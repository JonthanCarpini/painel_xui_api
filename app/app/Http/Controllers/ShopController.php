<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    const EXTENSIONS = ['online', 'site', 'website', 'xyz'];

    public function dns()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        return view('shop.dns', [
            'extensions' => self::EXTENSIONS,
        ]);
    }

    public function dnsSearch(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $domain = strtolower(trim($request->input('domain')));
        $domain = preg_replace('/[^a-z0-9\-\.]/', '', $domain);

        $results = $this->checkDomainAvailability($domain);

        return response()->json($results);
    }

    public function dnsRegister(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $request->validate([
            'domain' => 'required|string|max:255',
            'years' => 'nullable|integer|min:1|max:10',
        ]);

        $domain = strtolower(trim($request->input('domain')));
        $years = (int) $request->input('years', 1);

        if (!str_contains($domain, '.')) {
            return response()->json(['success' => false, 'error' => 'Domínio inválido. Inclua a extensão.']);
        }

        $result = $this->registerDomain($domain, $years);

        return response()->json($result);
    }

    public function apps()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        return view('shop.apps');
    }

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
                return ['success' => false, 'error' => 'Erro ao consultar Namecheap (HTTP ' . $response->status() . ').'];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return ['success' => false, 'error' => 'Resposta inválida da Namecheap.'];
            }

            if ((string) $xml['Status'] !== 'OK') {
                $errorMsg = 'Erro desconhecido';
                if (isset($xml->Errors->Error)) {
                    $errorMsg = (string) $xml->Errors->Error;
                }
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

        $result = $this->namecheapRequest('namecheap.domains.check', [
            'DomainList' => $domainList,
        ]);

        if (!$result['success']) {
            return $result;
        }

        $xml = $result['xml'];
        $domains = [];

        foreach ($xml->CommandResponse->DomainCheckResult as $item) {
            $domains[] = [
                'domain' => (string) $item['Domain'],
                'available' => (string) $item['Available'] === 'true',
                'premium' => (string) ($item['IsPremiumName'] ?? 'false') === 'true',
            ];
        }

        return ['success' => true, 'data' => $domains];
    }

    protected function registerDomain(string $domain, int $years = 1): array
    {
        $checkResult = $this->checkDomainAvailability($domain);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $domainData = collect($checkResult['data'])->firstWhere('domain', $domain);
        if (!$domainData || !$domainData['available']) {
            return ['success' => false, 'error' => "O domínio {$domain} não está disponível para registro."];
        }

        $parts = explode('.', $domain, 2);
        $sld = $parts[0];
        $tld = $parts[1] ?? '';

        $config = $this->getNamecheapConfig();
        $registrantUser = $config['apiUser'];

        $result = $this->namecheapRequest('namecheap.domains.create', [
            'DomainName' => $domain,
            'Years' => $years,
            'RegistrantFirstName' => 'Admin',
            'RegistrantLastName' => 'Panel',
            'RegistrantAddress1' => 'Rua Principal 123',
            'RegistrantCity' => 'Sao Paulo',
            'RegistrantStateProvince' => 'SP',
            'RegistrantPostalCode' => '01000-000',
            'RegistrantCountry' => 'BR',
            'RegistrantPhone' => '+55.11999999999',
            'RegistrantEmailAddress' => $registrantUser . '@namecheap.com',
            'TechFirstName' => 'Admin',
            'TechLastName' => 'Panel',
            'TechAddress1' => 'Rua Principal 123',
            'TechCity' => 'Sao Paulo',
            'TechStateProvince' => 'SP',
            'TechPostalCode' => '01000-000',
            'TechCountry' => 'BR',
            'TechPhone' => '+55.11999999999',
            'TechEmailAddress' => $registrantUser . '@namecheap.com',
            'AdminFirstName' => 'Admin',
            'AdminLastName' => 'Panel',
            'AdminAddress1' => 'Rua Principal 123',
            'AdminCity' => 'Sao Paulo',
            'AdminStateProvince' => 'SP',
            'AdminPostalCode' => '01000-000',
            'AdminCountry' => 'BR',
            'AdminPhone' => '+55.11999999999',
            'AdminEmailAddress' => $registrantUser . '@namecheap.com',
            'AuxBillingFirstName' => 'Admin',
            'AuxBillingLastName' => 'Panel',
            'AuxBillingAddress1' => 'Rua Principal 123',
            'AuxBillingCity' => 'Sao Paulo',
            'AuxBillingStateProvince' => 'SP',
            'AuxBillingPostalCode' => '01000-000',
            'AuxBillingCountry' => 'BR',
            'AuxBillingPhone' => '+55.11999999999',
            'AuxBillingEmailAddress' => $registrantUser . '@namecheap.com',
            'AddFreeWhoisguard' => 'yes',
            'WGEnabled' => 'yes',
        ]);

        if (!$result['success']) {
            return $result;
        }

        $xml = $result['xml'];
        $domainResult = $xml->CommandResponse->DomainCreateResult ?? null;

        if (!$domainResult) {
            return ['success' => false, 'error' => 'Resposta inesperada da Namecheap ao registrar.'];
        }

        $registered = (string) ($domainResult['Registered'] ?? 'false') === 'true';

        if (!$registered) {
            return ['success' => false, 'error' => 'Falha ao registrar o domínio. Verifique saldo na Namecheap.'];
        }

        Log::info('ShopController: Domínio registrado', [
            'domain' => $domain,
            'years' => $years,
            'user' => Auth::user()->username ?? 'unknown',
            'order_id' => (string) ($domainResult['OrderID'] ?? ''),
            'transaction_id' => (string) ($domainResult['TransactionID'] ?? ''),
        ]);

        return [
            'success' => true,
            'message' => "Domínio {$domain} registrado com sucesso por {$years} ano(s)!",
            'data' => [
                'domain' => (string) ($domainResult['Domain'] ?? $domain),
                'order_id' => (string) ($domainResult['OrderID'] ?? ''),
                'transaction_id' => (string) ($domainResult['TransactionID'] ?? ''),
                'charged_amount' => (string) ($domainResult['ChargedAmount'] ?? ''),
            ],
        ];
    }
}
