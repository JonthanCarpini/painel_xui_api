<?php

namespace App\Http\Controllers;

use App\Models\ShopPaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ShopGatewayController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'provider' => 'required|string|in:' . implode(',', array_keys(ShopPaymentGateway::PROVIDERS)),
            'credentials' => 'required|array',
        ]);

        $provider = $request->input('provider');

        $existing = ShopPaymentGateway::provider($provider)->first();
        if ($existing) {
            return response()->json(['success' => false, 'error' => 'Gateway já cadastrado. Use editar.']);
        }

        ShopPaymentGateway::create([
            'provider' => $provider,
            'credentials' => $request->input('credentials'),
            'active' => false,
            'webhook_secret' => 'shop_' . Str::random(32),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'credentials' => 'required|array',
        ]);

        $gateway = ShopPaymentGateway::findOrFail($id);
        $gateway->credentials = $request->input('credentials');
        $gateway->save();

        return response()->json(['success' => true]);
    }

    public function toggleActive($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Acesso negado.'], 403);
        }

        $gateway = ShopPaymentGateway::findOrFail($id);

        if (!$gateway->active) {
            ShopPaymentGateway::where('id', '!=', $gateway->id)->update(['active' => false]);
        }

        $gateway->active = !$gateway->active;
        $gateway->save();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'error' => 'Acesso negado.'], 403);
        }

        $gateway = ShopPaymentGateway::findOrFail($id);
        $gateway->delete();

        return response()->json(['success' => true]);
    }
}
