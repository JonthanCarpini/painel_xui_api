<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $serversRaw = $this->api->getServers();
        $servers = collect($serversRaw)->map(fn($s) => (object) $s);

        return view('admin.servers.index', compact('servers'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $serversRaw = $this->api->getServers();
        $server = collect($serversRaw)
            ->map(fn($s) => (object) $s)
            ->firstWhere('id', (string) $id);

        if (!$server) {
            return redirect()->route('settings.admin.servers.index')->with('error', 'Servidor não encontrado.');
        }

        // Contar streams deste servidor via API get_streams
        $streamsResp  = $this->api->getStreams();
        $allStreams    = collect($streamsResp['data'] ?? []);
        $streamsCount = $allStreams->where('server_id', (string) $id)->count();
        $onlineStreams = $allStreams->where('server_id', (string) $id)->where('stream_status', '1')->count();

        return view('admin.servers.show', compact('server', 'streamsCount', 'onlineStreams'));
    }

    public function action(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $action = $request->input('action');

        try {
            switch ($action) {
                case 'restart_streams':
                    // Buscar streams deste servidor e reiniciar via API stop + start
                    $streamsResp = $this->api->getStreams();
                    $streams = collect($streamsResp['data'] ?? [])
                        ->where('server_id', (string) $id);

                    foreach ($streams as $stream) {
                        $this->api->stopStream((int) $stream['stream_id']);
                    }
                    sleep(1);
                    foreach ($streams as $stream) {
                        $this->api->startStream((int) $stream['stream_id']);
                    }
                    break;

                case 'stop_all_streams':
                    $streamsResp = $this->api->getStreams();
                    $streams = collect($streamsResp['data'] ?? [])
                        ->where('server_id', (string) $id);

                    foreach ($streams as $stream) {
                        $this->api->stopStream((int) $stream['stream_id']);
                    }
                    break;

                case 'start_all_streams':
                    $streamsResp = $this->api->getStreams();
                    $streams = collect($streamsResp['data'] ?? [])
                        ->where('server_id', (string) $id);

                    foreach ($streams as $stream) {
                        $this->api->startStream((int) $stream['stream_id']);
                    }
                    break;

                case 'kill_connections':
                    // Matar todas as conexões ativas deste servidor via kill_connection
                    $connectionsResp = $this->api->getLiveConnections();
                    $connections = collect($connectionsResp['data'] ?? [])
                        ->where('server_id', (string) $id);

                    foreach ($connections as $conn) {
                        $this->api->killConnection((int) $conn['activity_id']);
                    }
                    break;

                case 'restart_services':
                    return back()->with('error', 'Reinício de serviços via painel não suportado sem acesso SSH configurado.');
            }

            return back()->with('success', "Ação '{$action}' enviada com sucesso para o servidor.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao executar ação: ' . $e->getMessage());
        }
    }
}
