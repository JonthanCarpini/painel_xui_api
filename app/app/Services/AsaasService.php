<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.base_url', 'https://api.asaas.com/v3');
    }

    public function generatePixQrCode(PaymentGateway $gateway, array $data): array
    {
        $accessToken = $gateway->getCredential('access_token');
        $addressKey = $gateway->getCredential('address_key');

        if (empty($accessToken) || empty($addressKey)) {
            return ['success' => false, 'error' => 'Credenciais Asaas incompletas.'];
        }

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'access_token' => $accessToken,
            ])->post("{$this->baseUrl}/pix/qrCodes/static", [
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
                        'expiration_date' => $body['expirationDate'] ?? null,
                    ],
                ];
            }

            Log::error('AsaasService: Erro ao gerar QR Code PIX', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'Erro na API Asaas: ' . ($response->json('errors.0.description') ?? $response->body()),
            ];
        } catch (\Exception $e) {
            Log::error('AsaasService: Exception ao gerar QR Code PIX', [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Erro de conexão com Asaas: ' . $e->getMessage()];
        }
    }

    public function validateWebhook(PaymentGateway $gateway, array $payload): bool
    {
        $webhookSecret = $gateway->webhook_secret;
        if (empty($webhookSecret)) {
            return true;
        }
        return true;
    }

    public function processWebhookPayment(array $payload): array
    {
        $event = $payload['event'] ?? '';
        $payment = $payload['payment'] ?? [];

        if ($event !== 'PAYMENT_RECEIVED' && $event !== 'PAYMENT_CONFIRMED') {
            return ['processed' => false, 'reason' => "Evento ignorado: {$event}"];
        }

        $billingType = $payment['billingType'] ?? '';
        if ($billingType !== 'PIX') {
            return ['processed' => false, 'reason' => "Tipo de pagamento ignorado: {$billingType}"];
        }

        return [
            'processed' => true,
            'external_reference' => $payment['externalReference'] ?? null,
            'value' => (float) ($payment['value'] ?? 0),
            'payment_id' => $payment['id'] ?? null,
            'status' => $payment['status'] ?? null,
        ];
    }

    public function testConnection(PaymentGateway $gateway): array
    {
        $accessToken = $gateway->getCredential('access_token');

        if (empty($accessToken)) {
            return ['success' => false, 'error' => 'Access token não configurado.'];
        }

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'access_token' => $accessToken,
            ])->get("{$this->baseUrl}/pix/addressKeys");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return [
                'success' => false,
                'error' => 'Erro na API: ' . ($response->json('errors.0.description') ?? $response->body()),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro de conexão: ' . $e->getMessage()];
        }
    }
}
