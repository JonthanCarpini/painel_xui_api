<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\ClientApplication;
use App\Models\DnsServer;
use App\Models\Notice;
use App\Models\TestChannel;
use App\Services\ChannelService;
use App\Services\PackageService;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    protected $channelService;
    protected $api;

    protected $packages;

    public function __construct(ChannelService $channelService, XuiApiService $api, PackageService $packages)
    {
        $this->channelService = $channelService;
        $this->api = $api;
        $this->packages = $packages;
    }

    public function index()
    {
        $user = Auth::user();

        // Apenas Admin pode acessar configurações
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar as configurações.');
        }

        // Buscar configurações via API XUI
        $settingsResp = $this->api->getSettings();
        $settingsData = $settingsResp['data'] ?? [];
        $settings = (object) $settingsData;

        // tmdb_api_key vive no banco local
        $tmdbApiKey = AppSetting::get('tmdb_api_key', $settingsData['tmdb_api_key'] ?? null);

        // Buscar dados locais
        $apps = ClientApplication::all();
        $dnsServers = DnsServer::all();
        $notices = Notice::orderBy('priority', 'desc')->orderBy('created_at', 'desc')->get();
        $clientMessageTemplate = AppSetting::get('client_message_template', '');
        $trustPackageId = AppSetting::get('trust_renew_package_id');
        
        // Buscar pacotes oficiais para seleção
        $packages = $this->packages->where('is_official', true);
        
        // Dados da Revenda Fantasma (Teste de Canais)
        $ghostReseller = [
            'username' => AppSetting::get('ghost_reseller_username', ''),
            'password' => AppSetting::get('ghost_reseller_password', ''),
            'rotation_time' => AppSetting::get('ghost_rotation_time', '04:00'),
            'last_sync' => AppSetting::get('ghost_reseller_last_sync', 'Nunca'),
            'channels_count' => TestChannel::count()
        ];

        return view('settings.index', [
            'settings'              => $settings,
            'tmdbApiKey'            => $tmdbApiKey,
            'apps'                  => $apps,
            'dnsServers'            => $dnsServers,
            'notices'               => $notices,
            'clientMessageTemplate' => $clientMessageTemplate,
            'ghostReseller'         => $ghostReseller,
            'packages'              => $packages,
            'trustPackageId'        => $trustPackageId,
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
            'ghost_reseller_username' => 'nullable|string|max:255',
            'ghost_reseller_password' => 'nullable|string|max:255',
            'ghost_rotation_time' => 'nullable|date_format:H:i',
            'trust_renew_package_id' => 'nullable|integer',
        ]);

        try {
            // 0. Salvar Pacote de Confiança
            if ($request->has('trust_renew_package_id')) {
                AppSetting::set('trust_renew_package_id', $request->input('trust_renew_package_id'));
            }

            // 1. Processar Cliente Fantasma (Teste de Canais)
            if ($request->has('ghost_reseller_username')) {
                $username = $request->input('ghost_reseller_username');
                $password = $request->input('ghost_reseller_password');
                $rotationTime = $request->input('ghost_rotation_time', '04:00');
                
                $currentUsername = AppSetting::get('ghost_reseller_username');
                $currentPassword = AppSetting::get('ghost_reseller_password');
                
                AppSetting::set('ghost_rotation_time', $rotationTime);

                // Se houve mudança nas credenciais, salvar e sincronizar canais
                if ($username && ($username !== $currentUsername || $password !== $currentPassword)) {
                    AppSetting::set('ghost_reseller_username', $username);
                    AppSetting::set('ghost_reseller_password', $password);
                    
                    // Sincronizar canais
                    $syncResult = $this->channelService->syncChannels($username, $password);
                    
                    if (!$syncResult['success']) {
                        return back()->withErrors(['error' => 'Credenciais salvas, mas falha ao baixar lista de canais: ' . $syncResult['message']])->withInput();
                    }

                    // Notificar SaaS para atualizar proxy Nginx
                    $this->notifySaasGhostUpdate($username, $password);
                }
            }

            // 2. Salvar tmdb_api_key no banco local
            if ($request->has('tmdb_api_key')) {
                AppSetting::set('tmdb_api_key', $request->input('tmdb_api_key') ?? '');
            }

            // 3. Processar Configurações do XUI (exceto tmdb_api_key que agora é local)
            $xuiSettings = collect($validated)
                ->except(['ghost_reseller_username', 'ghost_reseller_password', 'ghost_rotation_time', 'trust_renew_package_id', 'tmdb_api_key'])
                ->toArray();

            if (!empty($xuiSettings)) {
                $this->api->updateSettings($xuiSettings);
            }

            return redirect()->route('settings.index')
                ->with('success', 'Configurações atualizadas e canais sincronizados com sucesso!');

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

    // --- AVISOS (NOTICES) ---

    public function storeNotice(Request $request)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,danger,success',
            'priority' => 'integer|min:0',
        ]);

        Notice::create($validated);

        return redirect()->route('settings.index', ['#notices'])
            ->with('success', 'Aviso criado com sucesso!');
    }

    public function updateNotice(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,danger,success',
            'priority' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        $notice = Notice::findOrFail($id);
        $notice->update($validated);

        return redirect()->route('settings.index', ['#notices'])
            ->with('success', 'Aviso atualizado com sucesso!');
    }

    public function destroyNotice($id)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $notice = Notice::findOrFail($id);
        $notice->delete();

        return redirect()->route('settings.index', ['#notices'])
            ->with('success', 'Aviso removido com sucesso!');
    }

    private function notifySaasGhostUpdate(string $username, string $password): void
    {
        $saasApiUrl = env('SAAS_API_URL');
        $instanceToken = env('INSTANCE_TOKEN');

        if (empty($saasApiUrl) || empty($instanceToken)) {
            return;
        }

        $url = rtrim($saasApiUrl, '/') . "/api/instance/{$instanceToken}/ghost-credentials";

        try {
            Http::timeout(15)->post($url, [
                'username' => $username,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Settings: falha ao notificar SaaS sobre ghost update", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
