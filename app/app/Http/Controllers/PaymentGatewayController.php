<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\PaymentGateway;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayController extends Controller
{
    protected AsaasService $asaas;

    public function __construct(AsaasService $asaas)
    {
        $this->asaas = $asaas;
    }

    public function index()
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $gateways = PaymentGateway::forReseller($user->id)->get();

        $gatewaysByProvider = [];
        foreach (PaymentGateway::PROVIDERS as $key => $label) {
            $gatewaysByProvider[$key] = $gateways->firstWhere('provider', $key);
        }

        return view('payments.gateways', [
            'gateways' => $gateways,
            'gatewaysByProvider' => $gatewaysByProvider,
            'providers' => PaymentGateway::PROVIDERS,
        ]);
    }

    public function store(Request $request)
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'provider' => 'required|string|in:' . implode(',', array_keys(PaymentGateway::PROVIDERS)),
        ]);

        $provider = $validated['provider'];
        $credentials = $this->extractCredentials($request, $provider);

        $existing = PaymentGateway::forReseller($user->id)->provider($provider)->first();

        if ($existing) {
            return redirect()->route('payments.gateways.index')
                ->withErrors(['error' => 'Gateway já cadastrado. Use a opção de editar.']);
        }

        PaymentGateway::create([
            'reseller_id' => $user->id,
            'provider' => $provider,
            'credentials' => $credentials,
            'active' => false,
            'webhook_secret' => Str::random(32),
        ]);

        return redirect()->route('payments.gateways.index')
            ->with('success', 'Gateway ' . PaymentGateway::getProviderLabel($provider) . ' cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $gateway = PaymentGateway::forReseller($user->id)->findOrFail($id);

        $credentials = $this->extractCredentials($request, $gateway->provider);
        $gateway->credentials = $credentials;
        $gateway->save();

        return redirect()->route('payments.gateways.index')
            ->with('success', 'Gateway ' . PaymentGateway::getProviderLabel($gateway->provider) . ' atualizado!');
    }

    public function toggleActive(Request $request, $id)
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $gateway = PaymentGateway::forReseller($user->id)->findOrFail($id);

        $creds = $gateway->credentials;
        if (empty($creds) || (empty($creds['access_token'] ?? null) && empty($creds['token'] ?? null))) {
            return redirect()->route('payments.gateways.index')
                ->withErrors(['error' => 'Configure as credenciais antes de ativar o gateway.']);
        }

        $gateway->active = !$gateway->active;
        $gateway->save();

        $status = $gateway->active ? 'ativado' : 'desativado';
        return redirect()->route('payments.gateways.index')
            ->with('success', PaymentGateway::getProviderLabel($gateway->provider) . " {$status}!");
    }

    public function testConnection($id)
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $gateway = PaymentGateway::forReseller($user->id)->findOrFail($id);

        $result = ['success' => false, 'error' => 'Provider não suportado para teste.'];

        if ($gateway->provider === PaymentGateway::PROVIDER_ASAAS) {
            $result = $this->asaas->testConnection($gateway);
        }

        return response()->json($result);
    }

    public function destroy($id)
    {
        if (AppSetting::get('module_payments_enabled', '0') !== '1') {
            abort(404);
        }

        $user = Auth::user();
        $gateway = PaymentGateway::forReseller($user->id)->findOrFail($id);
        $label = PaymentGateway::getProviderLabel($gateway->provider);
        $gateway->delete();

        return redirect()->route('payments.gateways.index')
            ->with('success', "Gateway {$label} removido!");
    }

    protected function extractCredentials(Request $request, string $provider): array
    {
        switch ($provider) {
            case PaymentGateway::PROVIDER_ASAAS:
                $request->validate([
                    'access_token' => 'required|string',
                    'address_key' => 'required|string',
                ]);
                return [
                    'access_token' => $request->input('access_token'),
                    'address_key' => $request->input('address_key'),
                ];

            case PaymentGateway::PROVIDER_MERCADOPAGO:
                $request->validate([
                    'access_token' => 'required|string',
                ]);
                return [
                    'access_token' => $request->input('access_token'),
                ];

            case PaymentGateway::PROVIDER_FASTDEPIX:
                $request->validate([
                    'token' => 'required|string',
                ]);
                return [
                    'token' => $request->input('token'),
                ];

            default:
                return [];
        }
    }
}
