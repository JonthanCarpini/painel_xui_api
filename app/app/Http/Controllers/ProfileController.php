<?php

namespace App\Http\Controllers;

use App\Models\PanelUser;
use App\Models\WhatsappSetting;
use App\Services\EvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        $xuiUser = Auth::user();
        
        // Busca ou cria o usuário local (caso não tenha logado ainda após a atualização)
        $panelUser = PanelUser::firstOrCreate(
            ['xui_id' => $xuiUser->id],
            [
                'username' => $xuiUser->username,
                'group_id' => $xuiUser->member_group_id
            ]
        );

        // Preferências padrão
        $preferences = [
            'panel_name' => $panelUser->getPreference('panel_name', 'Painel XUI'),
            'logo_url' => $panelUser->getPreference('logo_url', ''),
            'dark_mode' => $panelUser->getPreference('dark_mode', false),
            'show_dashboard_stats' => $panelUser->getPreference('show_dashboard_stats', true),
            'enable_notifications' => $panelUser->getPreference('enable_notifications', true),
        ];

        $whatsappSetting = WhatsappSetting::where('panel_user_id', $panelUser->id)->first();

        return view('profile.index', compact('xuiUser', 'panelUser', 'preferences', 'whatsappSetting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'panel_name' => 'nullable|string|max:255',
            'logo_url' => 'nullable|url|max:255',
            'dark_mode' => 'boolean',
            'show_dashboard_stats' => 'boolean',
            'enable_notifications' => 'boolean',
        ]);

        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->firstOrFail();

        // Atualizar dados do usuário
        $panelUser->phone = $request->input('phone');
        $panelUser->save();

        // Salvar preferências
        $panelUser->setPreference('panel_name', $request->input('panel_name', 'Painel XUI'));
        $panelUser->setPreference('logo_url', $request->input('logo_url', ''));
        $panelUser->setPreference('dark_mode', $request->has('dark_mode'));
        $panelUser->setPreference('show_dashboard_stats', $request->has('show_dashboard_stats'));
        $panelUser->setPreference('enable_notifications', $request->has('enable_notifications'));

        return redirect()->route('profile.index')->with('success', 'Preferências atualizadas com sucesso!');
    }

    public function createWhatsappInstance()
    {
        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->firstOrFail();
        $instanceName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $xuiUser->username));

        $existing = WhatsappSetting::where('panel_user_id', $panelUser->id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Você já possui uma instância WhatsApp.'], 422);
        }

        $evo = new EvolutionService();
        $result = $evo->createInstance($instanceName);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => 'Erro ao criar instância: ' . ($result['error'] ?? 'Desconhecido')], 500);
        }

        $setting = WhatsappSetting::create([
            'panel_user_id' => $panelUser->id,
            'instance_name' => $instanceName,
        ]);

        return response()->json(['success' => true, 'instance_name' => $instanceName]);
    }

    public function getWhatsappQrCode()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $result = $evo->getQrCode($setting->instance_name);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['error'] ?? 'Erro ao obter QR Code']);
        }

        $data = $result['data'] ?? [];
        $base64 = $data['base64'] ?? null;
        $code = $data['code'] ?? null;
        $pairingCode = $data['pairingCode'] ?? null;

        return response()->json([
            'success' => true,
            'qrcode' => $base64,
            'code' => $code,
            'pairingCode' => $pairingCode,
        ]);
    }

    public function getWhatsappStatus()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'status' => 'none']);
        }

        $evo = new EvolutionService();
        $result = $evo->getInstanceStatus($setting->instance_name);

        $state = $result['data']['instance']['state'] ?? 'close';

        if ($state === 'open' && $setting->connection_status !== 'connected') {
            $setting->update(['connection_status' => 'connected', 'connected_at' => now()]);
        } elseif ($state !== 'open' && $setting->connection_status === 'connected') {
            $setting->update(['connection_status' => 'disconnected', 'connected_at' => null]);
        }

        return response()->json([
            'success' => true,
            'status' => $state === 'open' ? 'connected' : $state,
            'instance_name' => $setting->instance_name,
        ]);
    }

    public function disconnectWhatsapp()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $evo->logout($setting->instance_name);
        $setting->update(['connection_status' => 'disconnected', 'connected_at' => null]);

        return response()->json(['success' => true]);
    }

    public function deleteWhatsappInstance()
    {
        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $evo = new EvolutionService();
        $evo->deleteInstance($setting->instance_name);
        $setting->delete();

        return response()->json(['success' => true]);
    }

    public function updateWhatsappSettings(Request $request)
    {
        $request->validate([
            'notifications_enabled' => 'boolean',
            'expiry_message_3d' => 'nullable|string|max:1000',
            'expiry_message_1d' => 'nullable|string|max:1000',
            'expiry_message_today' => 'nullable|string|max:1000',
        ]);

        $setting = $this->getWhatsappSetting();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Instância não encontrada.'], 404);
        }

        $setting->update([
            'notifications_enabled' => $request->boolean('notifications_enabled'),
            'expiry_message_3d' => $request->input('expiry_message_3d'),
            'expiry_message_1d' => $request->input('expiry_message_1d'),
            'expiry_message_today' => $request->input('expiry_message_today'),
        ]);

        return redirect()->route('profile.index', ['tab' => 'whatsapp'])->with('success', 'Configurações de WhatsApp atualizadas!');
    }

    private function getWhatsappSetting(): ?WhatsappSetting
    {
        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->first();
        if (!$panelUser) return null;

        return WhatsappSetting::where('panel_user_id', $panelUser->id)->first();
    }
}
