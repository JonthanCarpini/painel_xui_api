<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NamecheapController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $config = $this->getConfig();
        $data = [
            'configured' => !empty($config['apiUser']) && !empty($config['apiKey']),
            'apiUser' => $config['apiUser'],
            'sandbox' => $config['useSandbox'],
            'balance' => null,
            'domains' => [],
            'error' => null,
        ];

        if ($data['configured']) {
            $balanceResult = $this->getBalance($config);
            if ($balanceResult['success']) {
                $data['balance'] = $balanceResult['data'];
            } else {
                $data['error'] = $balanceResult['error'];
            }

            $domainsResult = $this->getDomainsList($config);
            if ($domainsResult['success']) {
                $data['domains'] = $domainsResult['data'];
            } elseif (!$data['error']) {
                $data['error'] = $domainsResult['error'];
            }
        }

        return view('admin.namecheap.index', $data);
    }

    protected function getConfig(): array
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

    protected function namecheapRequest(array $config, string $command, array $extra = []): array
    {
        $params = array_merge([
            'ApiUser' => $config['apiUser'],
            'ApiKey' => $config['apiKey'],
            'UserName' => $config['apiUser'],
            'ClientIp' => $config['clientIp'] ?: request()->ip(),
            'Command' => $command,
        ], $extra);

        try {
            $response = Http::timeout(30)->get($config['baseUrl'], $params);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'HTTP ' . $response->status()];
            }

            $xml = simplexml_load_string($response->body());
            if (!$xml) {
                return ['success' => false, 'error' => 'Resposta XML inválida'];
            }

            if ((string) $xml['Status'] !== 'OK') {
                $err = isset($xml->Errors->Error) ? (string) $xml->Errors->Error : 'Erro desconhecido';
                return ['success' => false, 'error' => $err];
            }

            return ['success' => true, 'xml' => $xml];
        } catch (\Exception $e) {
            Log::error('NamecheapController: erro', ['cmd' => $command, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Erro de conexão: ' . $e->getMessage()];
        }
    }

    protected function getBalance(array $config): array
    {
        $result = $this->namecheapRequest($config, 'namecheap.users.getBalances');
        if (!$result['success']) {
            return $result;
        }

        $xml = $result['xml'];
        $balances = $xml->CommandResponse->UserGetBalancesResult ?? null;

        if (!$balances) {
            return ['success' => false, 'error' => 'Sem dados de saldo'];
        }

        return [
            'success' => true,
            'data' => [
                'available' => (string) ($balances['AvailableBalance'] ?? '0.00'),
                'account' => (string) ($balances['AccountBalance'] ?? '0.00'),
                'earned' => (string) ($balances['EarnedAmount'] ?? '0.00'),
                'withdrawable' => (string) ($balances['WithdrawableAmount'] ?? '0.00'),
                'currency' => (string) ($balances['Currency'] ?? 'USD'),
            ],
        ];
    }

    protected function getDomainsList(array $config): array
    {
        $result = $this->namecheapRequest($config, 'namecheap.domains.getList', [
            'PageSize' => 100,
            'Page' => 1,
        ]);

        if (!$result['success']) {
            return $result;
        }

        $xml = $result['xml'];
        $domains = [];

        if (isset($xml->CommandResponse->DomainGetListResult->Domain)) {
            foreach ($xml->CommandResponse->DomainGetListResult->Domain as $d) {
                $domains[] = [
                    'id' => (string) ($d['ID'] ?? ''),
                    'name' => (string) ($d['Name'] ?? ''),
                    'user' => (string) ($d['User'] ?? ''),
                    'created' => (string) ($d['Created'] ?? ''),
                    'expires' => (string) ($d['Expires'] ?? ''),
                    'is_expired' => (string) ($d['IsExpired'] ?? 'false') === 'true',
                    'is_locked' => (string) ($d['IsLocked'] ?? 'false') === 'true',
                    'auto_renew' => (string) ($d['AutoRenew'] ?? 'false') === 'true',
                    'whois_guard' => (string) ($d['WhoisGuard'] ?? 'NOTPRESENT'),
                ];
            }
        }

        return ['success' => true, 'data' => $domains];
    }
}
