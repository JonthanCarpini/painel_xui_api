<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class MaintenanceController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index()
    {
        // Apenas admin
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Buscar configurações via API XUI
        $settingsResp = $this->api->getSettings();
        $xuiSettings  = $settingsResp['data'] ?? [];

        // Buscar servidores via API XUI
        $serversRaw = $this->api->getServers();
        $servers = collect($serversRaw)->map(fn($s) => (object) $s);

        // Buscar streams via API XUI (últimos 50)
        $streamsResp = $this->api->getStreams();
        $streams = collect($streamsResp['data'] ?? [])
            ->sortByDesc('id')
            ->take(50)
            ->map(fn($s) => (object) $s)
            ->values();

        $settings = [
            'maintenance_resellers' => AppSetting::get('maintenance_resellers', '0'),
            'maintenance_tests'     => AppSetting::get('maintenance_tests', '0'),
            'disable_trial'         => $xuiSettings['disable_trial'] ?? 0,
        ];

        return view('maintenance.index', compact('settings', 'servers', 'streams'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'maintenance_resellers' => 'boolean',
            'maintenance_tests'     => 'boolean',
            'disable_trial'         => 'boolean',
        ]);

        AppSetting::set('maintenance_resellers', $request->has('maintenance_resellers') ? '1' : '0');
        AppSetting::set('maintenance_tests', $request->has('maintenance_tests') ? '1' : '0');

        // Atualizar disable_trial via API XUI
        $this->api->updateSettings([
            'disable_trial' => $request->has('disable_trial') ? 1 : 0,
        ]);

        return redirect()->route('settings.maintenance.index')->with('success', 'Configurações de manutenção atualizadas com sucesso!');
    }

    public function runGhostRotation()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            Artisan::call('ghost:rotate');
            $output = Artisan::output();
            
            return redirect()->route('settings.maintenance.index')->with('success', 'Rotação executada: ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('settings.maintenance.index')->withErrors(['error' => 'Erro ao executar rotação: ' . $e->getMessage()]);
        }
    }
}
