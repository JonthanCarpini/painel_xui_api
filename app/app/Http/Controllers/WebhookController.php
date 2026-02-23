<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected AsaasService $asaas;

    public function __construct(AsaasService $asaas)
    {
        $this->asaas = $asaas;
    }

    public function asaas(Request $request, string $webhookSecret)
    {
        $gateway = PaymentGateway::where('webhook_secret', $webhookSecret)
            ->where('provider', PaymentGateway::PROVIDER_ASAAS)
            ->where('active', true)
            ->first();

        if (!$gateway) {
            Log::warning('Webhook Asaas: gateway não encontrado', ['secret' => $webhookSecret]);
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $payload = $request->all();

        Log::info('Webhook Asaas recebido', [
            'reseller_id' => $gateway->reseller_id,
            'event' => $payload['event'] ?? 'unknown',
        ]);

        $result = $this->asaas->processWebhookPayment($payload);

        if (!$result['processed']) {
            Log::info('Webhook Asaas: evento não processado', ['reason' => $result['reason'] ?? '']);
            return response()->json(['status' => 'ignored']);
        }

        // TODO: Integrar com auto-renovação de linhas
        // $result contém: external_reference, value, payment_id, status
        Log::info('Webhook Asaas: pagamento confirmado', [
            'reseller_id' => $gateway->reseller_id,
            'external_reference' => $result['external_reference'],
            'value' => $result['value'],
            'payment_id' => $result['payment_id'],
        ]);

        return response()->json(['status' => 'ok']);
    }
}
