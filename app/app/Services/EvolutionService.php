<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.url', ''), '/');
        $this->apiKey = config('services.evolution.api_key', '');
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders(['apikey' => $this->apiKey])
                ->$method("{$this->baseUrl}{$endpoint}", $data);

            if ($response->failed()) {
                Log::warning('Evolution API request failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $errorMsg = $response->json('response.message') ?? $response->body();
                return ['success' => false, 'error' => is_array($errorMsg) ? json_encode($errorMsg) : (string) $errorMsg];
            }

            return ['success' => true, 'data' => $response->json()];
        } catch (\Exception $e) {
            Log::error('Evolution API exception', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createInstance(string $instanceName): array
    {
        return $this->request('post', '/instance/create', [
            'instanceName' => $instanceName,
            'integration' => 'WHATSAPP-BAILEYS',
            'token' => $this->apiKey,
        ]);
    }

    public function deleteInstance(string $instanceName): array
    {
        return $this->request('delete', "/instance/delete/{$instanceName}");
    }

    public function getInstanceStatus(string $instanceName): array
    {
        return $this->request('get', "/instance/connectionState/{$instanceName}");
    }

    public function getQrCode(string $instanceName): array
    {
        return $this->request('get', "/instance/connect/{$instanceName}");
    }

    public function logout(string $instanceName): array
    {
        return $this->request('delete', "/instance/logout/{$instanceName}");
    }

    public function sendText(string $instanceName, string $phone, string $message): array
    {
        $chatId = preg_replace('/\D/', '', $phone) . '@s.whatsapp.net';

        return $this->request('post', "/message/sendText/{$instanceName}", [
            'number' => $chatId,
            'text' => $message,
        ]);
    }

    public function restartInstance(string $instanceName): array
    {
        return $this->request('put', "/instance/restart/{$instanceName}");
    }

    public function fetchInstances(): array
    {
        return $this->request('get', '/instance/fetchInstances');
    }
}
