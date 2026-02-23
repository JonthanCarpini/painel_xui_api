<?php

namespace App\Http\Controllers;

use App\Models\DomainOrder;
use App\Models\PaymentGateway;
use App\Models\ResellerDomain;
use App\Models\ShopPaymentGateway;
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

        Log::info('Webhook Asaas: pagamento confirmado', [
            'reseller_id' => $gateway->reseller_id,
            'external_reference' => $result['external_reference'],
            'value' => $result['value'],
            'payment_id' => $result['payment_id'],
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function mercadopago(Request $request, string $webhookSecret)
    {
        $gateway = PaymentGateway::where('webhook_secret', $webhookSecret)
            ->where('provider', 'mercadopago')
            ->where('active', true)
            ->first();

        if (!$gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        Log::info('Webhook Mercado Pago recebido', [
            'reseller_id' => $gateway->reseller_id,
            'payload' => $request->all(),
        ]);

        // TODO: implementar processamento de pagamento Mercado Pago
        return response()->json(['status' => 'ok']);
    }

    public function fastdepix(Request $request, string $webhookSecret)
    {
        $gateway = PaymentGateway::where('webhook_secret', $webhookSecret)
            ->where('provider', 'fastdepix')
            ->where('active', true)
            ->first();

        if (!$gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        Log::info('Webhook FastDePix recebido', [
            'reseller_id' => $gateway->reseller_id,
            'payload' => $request->all(),
        ]);

        // TODO: implementar processamento de pagamento FastDePix
        return response()->json(['status' => 'ok']);
    }

    public function shopWebhook(Request $request, string $webhookSecret)
    {
        $gateway = ShopPaymentGateway::where('webhook_secret', $webhookSecret)
            ->where('active', true)
            ->first();

        if (!$gateway) {
            Log::warning('Webhook Shop: gateway não encontrado', ['secret' => $webhookSecret]);
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        Log::info('Webhook Shop recebido', [
            'provider' => $gateway->provider,
            'event' => $event,
        ]);

        if ($event !== 'PAYMENT_RECEIVED' && $event !== 'PAYMENT_CONFIRMED') {
            return response()->json(['status' => 'ignored']);
        }

        $payment = $payload['payment'] ?? [];
        $billingType = $payment['billingType'] ?? '';

        if ($billingType !== 'PIX') {
            return response()->json(['status' => 'ignored']);
        }

        $externalRef = $payment['externalReference'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if (!$externalRef || !str_starts_with($externalRef, 'DOM_')) {
            Log::info('Webhook Shop: referência não é de domínio', ['ref' => $externalRef]);
            return response()->json(['status' => 'ignored']);
        }

        $order = DomainOrder::where('order_ref', $externalRef)
            ->where('status', DomainOrder::STATUS_PENDING)
            ->first();

        if (!$order) {
            Log::warning('Webhook Shop: pedido não encontrado ou já processado', ['ref' => $externalRef]);
            return response()->json(['status' => 'not_found']);
        }

        $order->update([
            'status' => DomainOrder::STATUS_PAID,
            'payment_id' => $paymentId,
            'paid_at' => now(),
        ]);

        Log::info('Webhook Shop: pagamento confirmado, registrando domínio', [
            'order_ref' => $externalRef,
            'domain' => $order->domain,
        ]);

        $shopController = app(ShopController::class);
        $registerResult = $shopController->registerDomainAfterPayment($order);

        if ($registerResult['success']) {
            $order->update([
                'status' => DomainOrder::STATUS_REGISTERED,
                'namecheap_order_id' => $registerResult['namecheap_order_id'] ?? null,
                'registered_at' => now(),
            ]);

            ResellerDomain::create([
                'reseller_id' => $order->reseller_id,
                'domain' => $order->domain,
                'type' => ResellerDomain::TYPE_PURCHASED,
                'is_active' => false,
                'namecheap_order_id' => $registerResult['namecheap_order_id'] ?? null,
                'paid_amount_brl' => $order->price_brl,
                'years' => $order->years,
                'expires_at' => now()->addYears($order->years),
            ]);

            Log::info('Webhook Shop: domínio registrado com sucesso', [
                'domain' => $order->domain,
                'reseller_id' => $order->reseller_id,
            ]);
        } else {
            $order->update(['status' => DomainOrder::STATUS_FAILED]);

            Log::error('Webhook Shop: falha ao registrar domínio', [
                'domain' => $order->domain,
                'error' => $registerResult['error'] ?? 'unknown',
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
