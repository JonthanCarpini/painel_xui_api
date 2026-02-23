<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function dns()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        return view('shop.dns');
    }

    public function dnsSearch(Request $request)
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $domain = $request->input('domain');
        $results = $this->checkDomainAvailability($domain);

        return response()->json($results);
    }

    public function apps()
    {
        if (AppSetting::get('module_shop_enabled', '0') !== '1') {
            abort(404);
        }

        return view('shop.apps');
    }

    protected function checkDomainAvailability(string $domain): array
    {
        $apiUser = AppSetting::get('namecheap_api_user', '');
        $apiKey = AppSetting::get('namecheap_api_key', '');
        $clientIp = AppSetting::get('namecheap_client_ip', '');

        if (empty($apiUser) || empty($apiKey)) {
            return ['success' => false, 'error' => 'API Namecheap não configurada. Contate o administrador.'];
        }

        $useSandbox = AppSetting::get('namecheap_sandbox', '0') === '1';
        $baseUrl = $useSandbox
            ? 'https://api.sandbox.namecheap.com/xml.response'
            : 'https://api.namecheap.com/xml.response';

        $domain = strtolower(trim($domain));
        if (!str_contains($domain, '.')) {
            $extensions = ['com', 'net', 'org', 'com.br', 'tv', 'info'];
            $domainList = implode(',', array_map(fn($ext) => "{$domain}.{$ext}", $extensions));
        } else {
            $domainList = $domain;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get($baseUrl, [
                'ApiUser' => $apiUser,
                'ApiKey' => $apiKey,
                'UserName' => $apiUser,
                'ClientIp' => $clientIp ?: request()->ip(),
                'Command' => 'namecheap.domains.check',
                'DomainList' => $domainList,
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Erro ao consultar Namecheap.'];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml || (string) $xml['Status'] !== 'OK') {
                $errorMsg = (string) ($xml->Errors->Error ?? 'Erro desconhecido');
                return ['success' => false, 'error' => $errorMsg];
            }

            $results = [];
            foreach ($xml->CommandResponse->DomainCheckResult as $result) {
                $results[] = [
                    'domain' => (string) $result['Domain'],
                    'available' => (string) $result['Available'] === 'true',
                    'premium' => (string) ($result['IsPremiumName'] ?? 'false') === 'true',
                ];
            }

            return ['success' => true, 'data' => $results];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ShopController: Erro Namecheap', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erro de conexão com Namecheap.'];
        }
    }
}
