<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    public function index()
    {
        // Apenas admin
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Buscar configuração do XUI
        $xuiSettings = DB::connection('xui')->table('settings')->where('id', 1)->first();

        // Buscar Load Balancers
        $servers = DB::connection('xui')->table('servers')
            ->select('id', 'server_name', 'server_ip', 'status', 'total_clients', 'is_main', 'last_check_ago')
            ->get();

        // Buscar Streams (Apenas uma amostra ou os que estão com problema? Vamos pegar os últimos adicionados ou offline)
        // Para uma gestão completa, seria melhor uma rota separada com DataTables, mas vamos colocar uma lista básica aqui.
        // Vamos pegar streams e seus status
        $streams = DB::connection('xui')->table('streams as s')
            ->leftJoin('streams_servers as ss', 's.id', '=', 'ss.stream_id')
            ->select('s.id', 's.stream_display_name', 's.stream_source', 'ss.stream_status', 'ss.pid', 'ss.server_id')
            ->orderBy('s.id', 'desc')
            ->limit(50)
            ->get();

        $settings = [
            'maintenance_resellers' => AppSetting::get('maintenance_resellers', '0'),
            'maintenance_tests' => AppSetting::get('maintenance_tests', '0'),
            'disable_trial' => $xuiSettings->disable_trial ?? 0,
        ];

        return view('maintenance.index', compact('settings', 'servers', 'streams'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'maintenance_resellers' => 'boolean',
            'maintenance_tests' => 'boolean',
            'disable_trial' => 'boolean',
        ]);

        AppSetting::set('maintenance_resellers', $request->has('maintenance_resellers') ? '1' : '0');
        AppSetting::set('maintenance_tests', $request->has('maintenance_tests') ? '1' : '0');
        
        // Atualizar no XUI
        DB::connection('xui')->table('settings')->where('id', 1)->update([
            'disable_trial' => $request->has('disable_trial') ? 1 : 0
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
