<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ClientApplication;
use App\Models\DnsServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Apenas Admin pode acessar configurações
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar as configurações.');
        }

        // Buscar configurações da tabela settings do XUI
        $settings = DB::connection('xui')->table('settings')->where('id', 1)->first();

        // Buscar dados locais
        $apps = ClientApplication::all();
        $dnsServers = DnsServer::all();
        $clientMessageTemplate = AppSetting::get('client_message_template', '');

        return view('settings.index', [
            'settings' => $settings,
            'apps' => $apps,
            'dnsServers' => $dnsServers,
            'clientMessageTemplate' => $clientMessageTemplate
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Apenas Admin pode atualizar configurações
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'server_name' => 'nullable|string|max:255',
            'default_timezone' => 'nullable|string|max:255',
            'client_prebuffer' => 'nullable|integer',
            'user_auto_kick_hours' => 'nullable|integer',
            'flood_limit' => 'nullable|integer',
            'flood_seconds' => 'nullable|integer',
            'playback_limit' => 'nullable|integer',
            'api_pass' => 'nullable|string|max:255',
            'message_of_day' => 'nullable|string',
            'tmdb_api_key' => 'nullable|string',
            'language' => 'nullable|string|max:16',
            'date_format' => 'nullable|string|max:16',
            'datetime_format' => 'nullable|string|max:16',
        ]);

        try {
            DB::connection('xui')->table('settings')
                ->where('id', 1)
                ->update($validated);

            return redirect()->route('settings.index')
                ->with('success', 'Configurações atualizadas com sucesso!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao atualizar configurações: ' . $e->getMessage()])
                ->withInput();
        }
    }

    // --- APLICATIVOS ---

    public function storeApp(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'downloader_id' => 'nullable|string|max:255',
            'direct_link' => 'nullable|string|max:255',
            'compatible_devices' => 'nullable|string|max:255',
            'activation_code' => 'nullable|string|max:255',
            'login_instructions' => 'nullable|string',
        ]);

        ClientApplication::create($validated);

        return redirect()->route('settings.index', ['#apps'])
            ->with('success', 'Aplicativo adicionado com sucesso!');
    }

    public function updateApp(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'downloader_id' => 'nullable|string|max:255',
            'direct_link' => 'nullable|string|max:255',
            'compatible_devices' => 'nullable|string|max:255',
            'activation_code' => 'nullable|string|max:255',
            'login_instructions' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $app = ClientApplication::findOrFail($id);
        $app->update($validated);

        return redirect()->route('settings.index', ['#apps'])
            ->with('success', 'Aplicativo atualizado com sucesso!');
    }

    public function destroyApp($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $app = ClientApplication::findOrFail($id);
        $app->delete();

        return redirect()->route('settings.index', ['#apps'])
            ->with('success', 'Aplicativo removido com sucesso!');
    }

    // --- DNS ---

    public function storeDns(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
        ]);

        DnsServer::create($validated);

        return redirect()->route('settings.index', ['#dns'])
            ->with('success', 'DNS adicionado com sucesso!');
    }

    public function updateDns(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $dns = DnsServer::findOrFail($id);
        $dns->update($validated);

        return redirect()->route('settings.index', ['#dns'])
            ->with('success', 'DNS atualizado com sucesso!');
    }

    public function destroyDns($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $dns = DnsServer::findOrFail($id);
        $dns->delete();

        return redirect()->route('settings.index', ['#dns'])
            ->with('success', 'DNS removido com sucesso!');
    }

    // --- MENSAGEM DO CLIENTE ---

    public function updateClientMessage(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'client_message_template' => 'nullable|string',
        ]);

        AppSetting::set('client_message_template', $validated['client_message_template'] ?? '');

        return redirect()->route('settings.index', ['#message'])
            ->with('success', 'Modelo de mensagem atualizado com sucesso!');
    }
}
