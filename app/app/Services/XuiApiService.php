<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class XuiApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('xui.base_url', 'http://192.168.100.210/XIkpMBHH/');
        $this->apiKey = config('xui.api_key', '6EA0E44987AD73B0804CB0D46D2A9159');
        $this->timeout = config('xui.timeout', 30);
    }

    private function makeRequest(string $action, array $params = []): array
    {
        try {
            $params['api_key'] = $this->apiKey;
            $params['action'] = $action;

            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->baseUrl, $params);

            if ($response->failed()) {
                Log::error('XUI API Request Failed', [
                    'action' => $action,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'result' => false,
                    'message' => 'Erro na comunicação com o servidor XUI'
                ];
            }

            $data = $response->json();

            return $data ?? [
                'result' => false,
                'message' => 'Resposta inválida do servidor'
            ];

        } catch (\Exception $e) {
            Log::error('XUI API Exception', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            return [
                'result' => false,
                'message' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    public function getUsers(array $params = []): array
    {
        return $this->makeRequest('get_users', $params);
    }

    public function getUser(int $userId): array
    {
        return $this->makeRequest('get_user', ['id' => $userId]);
    }

    public function createUser(array $data): array
    {
        return $this->makeRequest('create_user', $data);
    }

    public function editUser(int $userId, array $data): array
    {
        $data['id'] = $userId;
        return $this->makeRequest('edit_user', $data);
    }

    public function deleteUser(int $userId): array
    {
        return $this->makeRequest('delete_user', ['id' => $userId]);
    }

    public function getLines(array $params = []): array
    {
        return $this->makeRequest('get_lines', $params);
    }

    public function getLine(int $lineId): array
    {
        return $this->makeRequest('get_line', ['id' => $lineId]);
    }

    public function createLine(array $data): array
    {
        if (isset($data['bouquet_ids']) && is_array($data['bouquet_ids'])) {
            $data['bouquet_ids'] = json_encode(array_values($data['bouquet_ids']));
        }

        if (isset($data['exp_date']) && !is_string($data['exp_date'])) {
            $data['exp_date'] = date('Y-m-d H:i', strtotime($data['exp_date']));
        }

        return $this->makeRequest('create_line', $data);
    }

    public function editLine(int $lineId, array $data): array
    {
        $data['id'] = $lineId;

        if (isset($data['bouquet_ids']) && is_array($data['bouquet_ids'])) {
            $data['bouquet_ids'] = json_encode(array_values($data['bouquet_ids']));
        }

        if (isset($data['exp_date']) && !is_string($data['exp_date'])) {
            $data['exp_date'] = date('Y-m-d H:i', strtotime($data['exp_date']));
        }

        return $this->makeRequest('edit_line', $data);
    }

    public function deleteLine(int $lineId): array
    {
        return $this->makeRequest('delete_line', ['id' => $lineId]);
    }

    public function enableLine(int $lineId): array
    {
        return $this->makeRequest('enable_line', ['id' => $lineId]);
    }

    public function disableLine(int $lineId): array
    {
        return $this->makeRequest('disable_line', ['id' => $lineId]);
    }

    public function getPackages(): array
    {
        $cacheKey = 'xui_packages';
        
        return Cache::remember($cacheKey, 3600, function () {
            return $this->makeRequest('get_packages');
        });
    }

    public function getBouquets(): array
    {
        $cacheKey = 'xui_bouquets';
        
        return Cache::remember($cacheKey, 3600, function () {
            return $this->makeRequest('get_bouquets');
        });
    }

    public function getFilteredBouquets(): array
    {
        $blacklist = config('xui.bouquet_blacklist', [34, 35, 10]);
        
        $response = $this->getBouquets();
        
        if (!isset($response['data']) || !is_array($response['data'])) {
            return ['result' => false, 'data' => []];
        }

        $filtered = array_filter($response['data'], function ($bouquet) use ($blacklist) {
            return !in_array($bouquet['id'], $blacklist);
        });

        return [
            'result' => $response['result'] ?? true,
            'data' => array_values($filtered)
        ];
    }

    public function getLiveConnections(): array
    {
        return $this->makeRequest('live_connections');
    }

    public function killConnection(string $pid): array
    {
        return $this->makeRequest('kill_connection', ['pid' => $pid]);
    }

    public function authenticateUser(string $username, string $password): ?array
    {
        $response = $this->getUsers();

        if (!isset($response['data']) || !is_array($response['data'])) {
            Log::warning('XUI Auth - Invalid response structure');
            return null;
        }

        foreach ($response['data'] as $user) {
            if (isset($user['username']) && $user['username'] === $username) {
                if (isset($user['status']) && $user['status'] != 1) {
                    Log::warning('XUI Auth - User inactive', ['username' => $username]);
                    return null;
                }

                Log::info('XUI Auth - User found and active', ['username' => $username]);
                $user['password'] = $password;
                return $user;
            }
        }

        Log::warning('XUI Auth - User not found', ['username' => $username]);
        return null;
    }

    public function getUserStats(int $userId): array
    {
        $lines = $this->getLines();
        
        $stats = [
            'total_clients' => 0,
            'active_clients' => 0,
            'expired_clients' => 0,
            'online_now' => 0
        ];

        if (isset($lines['data']) && is_array($lines['data'])) {
            $now = time();
            
            foreach ($lines['data'] as $line) {
                if (isset($line['member_id']) && $line['member_id'] == $userId) {
                    $stats['total_clients']++;
                    
                    $expDate = is_numeric($line['exp_date']) ? $line['exp_date'] : strtotime($line['exp_date']);
                    
                    if ($expDate > $now) {
                        $stats['active_clients']++;
                    } else {
                        $stats['expired_clients']++;
                    }
                }
            }
        }

        $connections = $this->getLiveConnections();
        if (isset($connections['data']) && is_array($connections['data'])) {
            foreach ($connections['data'] as $conn) {
                if (isset($conn['member_id']) && $conn['member_id'] == $userId) {
                    $stats['online_now']++;
                }
            }
        }

        return $stats;
    }

    public function getCreditLogs(int $userId = null): array
    {
        $params = [];
        if ($userId !== null) {
            $params['user_id'] = $userId;
        }
        
        return $this->makeRequest('credit_logs', $params);
    }
}
