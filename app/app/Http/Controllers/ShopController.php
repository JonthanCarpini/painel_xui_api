<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\DomainOrder;
use App\Models\ResellerDomain;
use App\Models\ShopPaymentGateway;
use App\Services\AsaasService;
use App\Services\CurrencyService;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    const EXTENSIONS = ['online', 'site', 'website', 'xyz'];
    const DEFAULT_PRICES_USD = ['online' => 1.98, 'site' => 1.98, 'website' => 1.98, 'xyz' => 1.00];

    protected CurrencyService $currency;
    protected AsaasService $asaas;
    protected XuiApiService $xuiApi;

    public function __construct(CurrencyService $currency, AsaasService $asaas, XuiApiService $xuiApi)
    {
        $this->currency = $currency;
        $this->asaas = $asaas;
        $this->xuiApi = $xuiApi;
    }

    public function dns()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $markup = (int) AppSetting::get('shop_markup_percent', '30');
        $rate = $this->currency->getUsdToBrl();
        $shopGateway = ShopPaymentGateway::getActiveGateway();

        return view('shop.dns', [
            'extensions' => self::EXTENSIONS,
            'isAdmin' => $user->isAdmin(),
            'markupPercent' => $markup,
            'exchangeRate' => $rate,
            'hasShopGateway' => $shopGateway !== null,
        ]);
    }

    public function dnsSearch(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $request->validate(['domain' => 'required|string|max:255']);

        $domain = strtolower(trim($request->input('domain')));
        $domain = preg_replace('/[^a-z0-9\-\.]/', '', $domain);

        $results = $this->checkDomainAvailability($domain);

        if ($results['success'] && !Auth::user()->isAdmin()) {
            $markup = (int) AppSetting::get('shop_markup_percent', '30');
            $results = $this->addPricing($results, $markup);
        }

        return response()->json($results);
    }

    public function dnsPurchase(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        if ($user->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Admin não compra domínios pelo shop.']);
        }

        $request->validate([
            'domain' => 'required|string|max:255',
            'years' => 'nullable|integer|min:1|max:10',
        ]);

        $domain = strtolower(trim($request->input('domain')));
        $years = (int) $request->input('years', 1);

        if (!str_contains($domain, '.')) {
            return response()->json(['success' => false, 'error' => 'Domínio inválido.']);
        }

        $shopGateway = ShopPaymentGateway::getActiveGateway();
        if (!$shopGateway) {
            return response()->json(['success' => false, 'error' => 'Nenhum gateway de pagamento configurado pelo admin.']);
        }

        $pending = DomainOrder::forReseller($user->id)->pending()
            ->where('domain', $domain)->first();
        if ($pending) {
            return response()->json([
                'success' => true,
                'order_ref' => $pending->order_ref,
                'pix_payload' => $pending->pix_payload,
                'pix_encoded_image' => $pending->pix_encoded_image,
                'price_brl' => $pending->price_brl,
                'message' => 'Pedido já existe. Escaneie o QR Code para pagar.',
            ]);
        }

        $parts = explode('.', $domain, 2);
        $tld = $parts[1] ?? '';
        $priceUsd = self::DEFAULT_PRICES_USD[$tld] ?? 9.98;
        $markup = (int) AppSetting::get('shop_markup_percent', '30');
        $conversion = $this->currency->convertUsdToBrl($priceUsd * $years, $markup);

        $orderRef = 'DOM_' . strtoupper(Str::random(12));

        $pixResult = $this->generateShopPix($shopGateway, [
            'description' => "Domínio: {$domain} ({$years} ano(s))",
            'value' => $conversion['final_brl'],
            'external_reference' => $orderRef,
        ]);

        if (!$pixResult['success']) {
            return response()->json($pixResult);
        }

        $order = DomainOrder::create([
            'order_ref' => $orderRef,
            'reseller_id' => $user->id,
            'domain' => $domain,
            'years' => $years,
            'price_usd' => $priceUsd * $years,
            'price_brl' => $conversion['final_brl'],
            'exchange_rate' => $conversion['rate'],
            'status' => DomainOrder::STATUS_PENDING,
            'pix_qr_code_id' => $pixResult['data']['qr_code_id'] ?? null,
            'pix_payload' => $pixResult['data']['payload'] ?? null,
            'pix_encoded_image' => $pixResult['data']['encoded_image'] ?? null,
            'expires_at' => now()->addMinutes(30),
        ]);

        Log::info('ShopController: Pedido de domínio criado', [
            'order_ref' => $orderRef,
            'domain' => $domain,
            'reseller_id' => $user->id,
            'price_brl' => $conversion['final_brl'],
        ]);

        return response()->json([
            'success' => true,
            'order_ref' => $orderRef,
            'pix_payload' => $order->pix_payload,
            'pix_encoded_image' => $order->pix_encoded_image,
            'price_brl' => $order->price_brl,
            'message' => 'QR Code PIX gerado! Escaneie para pagar.',
        ]);
    }

    public function dnsOrderStatus(string $orderRef)
    {
        $user = Auth::user();
        $order = DomainOrder::forReseller($user->id)->where('order_ref', $orderRef)->firstOrFail();

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'domain' => $order->domain,
        ]);
    }

    // === Meus Domínios ===

    public function myDomains()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $domains = ResellerDomain::forReseller($user->id)->orderByDesc('is_active')->orderByDesc('created_at')->get();
        $pendingOrders = DomainOrder::forReseller($user->id)->pending()->get();

        return view('shop.my-domains', compact('domains', 'pendingOrders'));
    }

    public function addCustomDomain(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $request->validate(['domain' => 'required|string|max:255|regex:/^[a-z0-9\-]+\.[a-z]{2,}$/i']);

        $user = Auth::user();
        $domain = strtolower(trim($request->input('domain')));

        $exists = ResellerDomain::forReseller($user->id)->where('domain', $domain)->exists();
        if ($exists) {
            return redirect()->route('shop.my-domains')->withErrors(['error' => 'Domínio já cadastrado.']);
        }

        ResellerDomain::create([
            'reseller_id' => $user->id,
            'domain' => $domain,
            'type' => ResellerDomain::TYPE_CUSTOM,
            'is_active' => false,
        ]);

        return redirect()->route('shop.my-domains')->with('success', "Domínio {$domain} adicionado!");
    }

    public function activateDomain($id)
    {
        $user = Auth::user();
        $domain = ResellerDomain::forReseller($user->id)->findOrFail($id);

        ResellerDomain::forReseller($user->id)->where('id', '!=', $id)->update(['is_active' => false]);
        $domain->update(['is_active' => true]);

        return redirect()->route('shop.my-domains')->with('success', "Domínio {$domain->domain} ativado para as listas!");
    }

    public function removeDomain($id)
    {
        $user = Auth::user();
        $domain = ResellerDomain::forReseller($user->id)->findOrFail($id);

        if ($domain->type === ResellerDomain::TYPE_PURCHASED) {
            return redirect()->route('shop.my-domains')->withErrors(['error' => 'Domínios comprados não podem ser removidos.']);
        }

        $domain->delete();
        return redirect()->route('shop.my-domains')->with('success', 'Domínio removido.');
    }

    public function cancelOrder($id)
    {
        $user = Auth::user();
        $order = DomainOrder::forReseller($user->id)->findOrFail($id);

        if ($order->status !== DomainOrder::STATUS_PENDING) {
            return redirect()->route('shop.my-domains')
                ->withErrors(['error' => 'Apenas pedidos pendentes podem ser cancelados.']);
        }

        $order->delete();

        return redirect()->route('shop.my-domains')
            ->with('success', "Pedido do domínio {$order->domain} removido.");
    }

    public function configureDns($id)
    {
        $user = Auth::user();
        $domain = ResellerDomain::forReseller($user->id)->findOrFail($id);
        $domainName = $domain->domain;

        $errors = [];
        $successes = [];

        // 1. Obter IP do servidor Main do XUI
        $mainIp = $this->getMainServerIp();
        if (!$mainIp) {
            return redirect()->route('shop.my-domains')
                ->withErrors(['error' => 'Servidor Main do XUI não encontrado.']);
        }

        // 2. Alterar registro A via Namecheap (apenas para domínios comprados)
        if ($domain->type === ResellerDomain::TYPE_PURCHASED) {
            $dnsResult = $this->setNamecheapDnsRecord($domainName, $mainIp);
            if ($dnsResult['success']) {
                $successes[] = "Registro A de {$domainName} apontado para {$mainIp}.";
            } else {
                $errors[] = "Erro DNS Namecheap: " . ($dnsResult['error'] ?? 'desconhecido');
            }
        }

        // 3. Autorizar domínio no XUI (allowed_user_cps)
        $authResult = $this->authorizedomainInXui($domainName);
        if ($authResult['success']) {
            $successes[] = "Domínio autorizado no XUI.";
        } else {
            $errors[] = "Erro XUI: " . ($authResult['error'] ?? 'desconhecido');
        }

        // 4. Atualizar reseller_dns do revendedor no XUI
        $xuiId = $user->xui_id ?? $user->id;
        $dnsUpdateResult = $this->xuiApi->editUser((int) $xuiId, [
            'reseller_dns' => $domainName,
        ]);
        if (($dnsUpdateResult['status'] ?? '') === 'STATUS_SUCCESS') {
            $successes[] = "DNS do revendedor atualizado para {$domainName}.";
        } else {
            $errors[] = "Erro ao atualizar DNS do revendedor no XUI.";
        }

        // 5. Marcar domínio como configurado
        $domain->update(['dns_configured' => true]);

        if (!empty($errors)) {
            return redirect()->route('shop.my-domains')
                ->with('success', implode(' ', $successes))
                ->withErrors(['error' => implode(' | ', $errors)]);
        }

        return redirect()->route('shop.my-domains')
            ->with('success', implode(' ', $successes));
    }

    protected function getMainServerIp(): ?string
    {
        $servers = $this->xuiApi->getServers();
        foreach ($servers as $server) {
            if (!empty($server['is_main'])) {
                return $server['server_ip'] ?? $server['domain_name'] ?? null;
            }
        }
        return null;
    }

    protected function setNamecheapDnsRecord(string $domainName, string $ip): array
    {
        $parts = explode('.', $domainName, 2);
        $sld = $parts[0] ?? '';
        $tld = $parts[1] ?? '';

        $result = $this->namecheapRequest('namecheap.domains.dns.setHosts', [
            'SLD' => $sld,
            'TLD' => $tld,
            'HostName1' => '@',
            'RecordType1' => 'A',
            'Address1' => $ip,
            'TTL1' => '1800',
            'HostName2' => 'www',
            'RecordType2' => 'CNAME',
            'Address2' => $domainName,
            'TTL2' => '1800',
        ]);

        return $result;
    }

    protected function authorizedomainInXui(string $domainName): array
    {
        $settingsResp = $this->xuiApi->getSettings();
        $currentCps = $settingsResp['data']['allowed_user_cps'] ?? '';

        $cpsList = array_filter(array_map('trim', explode(',', $currentCps)));

        if (!in_array($domainName, $cpsList)) {
            $cpsList[] = $domainName;
        }

        $newCps = implode(',', $cpsList);

        $updateResult = $this->xuiApi->updateSettings([
            'allowed_user_cps' => $newCps,
        ]);

        if (($updateResult['status'] ?? '') === 'STATUS_SUCCESS') {
            return ['success' => true];
        }

        return ['success' => false, 'error' => $updateResult['message'] ?? 'Falha ao atualizar settings XUI'];
    }

    public function apps()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }
        return view('shop.apps');
    }

    // === Namecheap Helpers ===

    protected function getNamecheapConfig(): array
    {
        $apiUser = AppSetting::get('namecheap_api_user', '');
        $apiKey = AppSetting::get('namecheap_api_key', '');
        $clientIp = AppSetting::get('namecheap_client_ip', '');
        $useSandbox = AppSetting::get('namecheap_sandbox', '0') === '1';

        $baseUrl = $useSandbox
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';

        return compact('apiUser', 'apiKey', 'clientIp', 'useSandbox', 'baseUrl');
    }

    protected function namecheapRequest(string $command, array $extraParams = []): array
    {
        $config = $this->getNamecheapConfig();

        if (empty($config['apiUser']) || empty($config['apiKey'])) {
            return ['success' => false, 'error' => 'API Namecheap não configurada. Contate o administrador.'];
        }

        $params = array_merge([
            'ApiUser' => $config['apiUser'],
            'ApiKey' => $config['apiKey'],
            'UserName' => $config['apiUser'],
            'ClientIp' => $config['clientIp'] ?: request()->ip(),
            'Command' => $command,
        ], $extraParams);

        try {
            $response = Http::timeout(30)->get($config['baseUrl'], $params);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Erro Namecheap (HTTP ' . $response->status() . ').'];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return ['success' => false, 'error' => 'Resposta inválida da Namecheap.'];
            }

            if ((string) $xml['Status'] !== 'OK') {
                $errorMsg = isset($xml->Errors->Error) ? (string) $xml->Errors->Error : 'Erro desconhecido';
                return ['success' => false, 'error' => $errorMsg];
            }

            return ['success' => true, 'xml' => $xml];
        } catch (\Exception $e) {
            Log::error('ShopController: Erro Namecheap', ['command' => $command, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erro de conexão com Namecheap.'];
        }
    }

    protected function checkDomainAvailability(string $domain): array
    {
        if (!str_contains($domain, '.')) {
            $domainList = implode(',', array_map(fn($ext) => "{$domain}.{$ext}", self::EXTENSIONS));
        } else {
            $domainList = $domain;
        }

        $result = $this->namecheapRequest('namecheap.domains.check', ['DomainList' => $domainList]);

        if (!$result['success']) {
            return $result;
        }

        $domains = [];
        foreach ($result['xml']->CommandResponse->DomainCheckResult as $item) {
            $domains[] = [
                'domain' => (string) $item['Domain'],
                'available' => (string) $item['Available'] === 'true',
                'premium' => (string) ($item['IsPremiumName'] ?? 'false') === 'true',
            ];
        }

        return ['success' => true, 'data' => $domains];
    }

    protected function getDomainPrice(string $domain, int $years = 1): array
    {
        $parts = explode('.', $domain, 2);
        $tld = $parts[1] ?? '';

        $result = $this->namecheapRequest('namecheap.users.getPricing', [
            'ProductType' => 'DOMAIN',
            'ProductCategory' => 'REGISTER',
            'ActionName' => 'REGISTER',
        ]);

        if (!$result['success']) {
            $price = self::DEFAULT_PRICES_USD[$tld] ?? 9.98;
            return ['success' => true, 'price_usd' => $price];
        }

        $xml = $result['xml'];
        $price = 9.98;

        try {
            foreach ($xml->CommandResponse->UserGetPricingResult->ProductType->ProductCategory->Product as $product) {
                if (strtolower((string) $product['Name']) === strtolower($tld)) {
                    foreach ($product->Price as $priceNode) {
                        if ((int) $priceNode['Duration'] === $years) {
                            $price = (float) $priceNode['YourPrice'];
                            break 2;
                        }
                    }
                    $price = (float) ($product->Price[0]['YourPrice'] ?? 9.98);
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::warning('ShopController: Erro ao parsear preços', ['error' => $e->getMessage()]);
        }

        return ['success' => true, 'price_usd' => $price];
    }

    protected function addPricing(array $results, int $markup): array
    {
        if (!isset($results['data'])) return $results;

        $rate = $this->currency->getUsdToBrl();

        foreach ($results['data'] as &$item) {
            if (!$item['available']) {
                $item['price_brl'] = null;
                continue;
            }

            $parts = explode('.', $item['domain'], 2);
            $tld = $parts[1] ?? '';
            $priceUsd = self::DEFAULT_PRICES_USD[$tld] ?? 9.98;

            $conversion = $this->currency->convertUsdToBrl($priceUsd, $markup);
            $item['price_usd'] = $priceUsd;
            $item['price_brl'] = $conversion['final_brl'];
            $item['exchange_rate'] = $rate;
        }

        $results['exchange_rate'] = $rate;
        $results['markup_percent'] = $markup;

        return $results;
    }

    public function registerDomainAfterPayment(DomainOrder $order): array
    {
        $domain = $order->domain;
        $years = $order->years;
        $config = $this->getNamecheapConfig();
        $registrantUser = $config['apiUser'];

        $contactFields = [
            'FirstName' => 'Admin', 'LastName' => 'Panel',
            'Address1' => 'Rua Principal 123', 'City' => 'Sao Paulo',
            'StateProvince' => 'SP', 'PostalCode' => '01000-000',
            'Country' => 'BR', 'Phone' => '+55.11999999999',
            'EmailAddress' => $registrantUser . '@namecheap.com',
        ];

        $params = ['DomainName' => $domain, 'Years' => $years];
        foreach (['Registrant', 'Tech', 'Admin', 'AuxBilling'] as $prefix) {
            foreach ($contactFields as $field => $value) {
                $params["{$prefix}{$field}"] = $value;
            }
        }
        $params['AddFreeWhoisguard'] = 'yes';
        $params['WGEnabled'] = 'yes';

        $result = $this->namecheapRequest('namecheap.domains.create', $params);

        if (!$result['success']) {
            return $result;
        }

        $domainResult = $result['xml']->CommandResponse->DomainCreateResult ?? null;
        if (!$domainResult || (string) ($domainResult['Registered'] ?? 'false') !== 'true') {
            return ['success' => false, 'error' => 'Falha ao registrar. Verifique saldo Namecheap.'];
        }

        $orderId = (string) ($domainResult['OrderID'] ?? '');

        Log::info('ShopController: Domínio registrado após pagamento', [
            'domain' => $domain, 'years' => $years,
            'reseller_id' => $order->reseller_id,
            'order_ref' => $order->order_ref,
            'namecheap_order_id' => $orderId,
        ]);

        return [
            'success' => true,
            'namecheap_order_id' => $orderId,
            'charged_amount' => (string) ($domainResult['ChargedAmount'] ?? ''),
        ];
    }

    protected function generateShopPix(ShopPaymentGateway $gateway, array $data): array
    {
        if ($gateway->provider !== 'asaas') {
            return ['success' => false, 'error' => 'Apenas Asaas suportado no momento.'];
        }

        $accessToken = $gateway->getCredential('access_token');
        $addressKey = $gateway->getCredential('address_key');

        if (empty($accessToken) || empty($addressKey)) {
            return ['success' => false, 'error' => 'Gateway do shop sem credenciais.'];
        }

        try {
            $baseUrl = config('services.asaas.base_url', 'https://api.asaas.com/v3');
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $accessToken,
            ])->post("{$baseUrl}/pix/qrCodes/static", [
                'addressKey' => $addressKey,
                'description' => $data['description'] ?? 'Pagamento PIX',
                'value' => (float) ($data['value'] ?? 0),
                'allowsMultiplePayments' => false,
                'externalReference' => $data['external_reference'] ?? null,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data' => [
                        'qr_code_id' => $body['id'] ?? null,
                        'encoded_image' => $body['encodedImage'] ?? null,
                        'payload' => $body['payload'] ?? null,
                    ],
                ];
            }

            return ['success' => false, 'error' => 'Erro Asaas: ' . ($response->json('errors.0.description') ?? $response->body())];
        } catch (\Exception $e) {
            Log::error('ShopController: Erro PIX', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erro de conexão com gateway.'];
        }
    }
}
