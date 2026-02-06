<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $servers = DB::connection('xui')->table('servers')->get();

        return view('admin.servers.index', compact('servers'));
    }

    public function show($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $server = DB::connection('xui')->table('servers')->where('id', $id)->first();
        
        if (!$server) {
            return redirect()->route('settings.admin.servers.index')->with('error', 'Servidor não encontrado.');
        }

        // Métricas e Streams associados
        $streamsCount = DB::connection('xui')->table('streams_servers')->where('server_id', $id)->count();
        $onlineStreams = DB::connection('xui')->table('streams_servers')->where('server_id', $id)->where('stream_status', 0)->count(); // 0 pode ser ON dependendo da versão, vou verificar

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
                    // Buscar todos os streams ativos ou configurados neste servidor
                    $streams = DB::connection('xui')->table('streams_servers')
                        ->where('server_id', $id)
                        ->get();

                    $signals = [];
                    $now = time();
                    foreach ($streams as $stream) {
                        // Resetar status
                        DB::connection('xui')->table('streams_servers')
                            ->where('server_stream_id', $stream->server_stream_id)
                            ->update(['stream_status' => 0, 'to_analyze' => 1]);

                        // Enviar sinal de restart (se tiver PID)
                        if ($stream->pid) {
                            $signals[] = [
                                'server_id' => $id,
                                'pid' => $stream->pid,
                                'rtmp' => 0,
                                'time' => $now,
                                'custom_data' => json_encode(['action' => 'restart_stream', 'stream_id' => $stream->stream_id])
                            ];
                        }
                    }
                    
                    if (!empty($signals)) {
                        DB::connection('xui')->table('signals')->insert($signals);
                    }
                    break;

                case 'stop_all_streams':
                    // Parar todos os streams (Set status 0 e talvez matar PIDs via signal)
                    $streams = DB::connection('xui')->table('streams_servers')
                        ->where('server_id', $id)
                        ->where('stream_status', '!=', 0)
                        ->get();

                    $signals = [];
                    $now = time();
                    foreach ($streams as $stream) {
                        DB::connection('xui')->table('streams_servers')
                            ->where('server_stream_id', $stream->server_stream_id)
                            ->update(['stream_status' => 0]); // Apenas marcar como parado

                        if ($stream->pid) {
                            $signals[] = [
                                'server_id' => $id,
                                'pid' => $stream->pid,
                                'rtmp' => 0,
                                'time' => $now,
                                'custom_data' => json_encode(['action' => 'stop_stream', 'stream_id' => $stream->stream_id])
                            ];
                        }
                    }
                    if (!empty($signals)) {
                        DB::connection('xui')->table('signals')->insert($signals);
                    }
                    break;

                case 'start_all_streams':
                    // Tenta iniciar streams que deveriam estar rodando
                    // Forçar 'to_analyze' = 1 faz o watchdog verificar e iniciar
                    DB::connection('xui')->table('streams_servers')
                        ->where('server_id', $id)
                        ->update(['to_analyze' => 1]);
                    break;

                case 'kill_connections':
                    // Remover conexões da tabela lines_live forçando desconexão lógica
                    // O XUI deve derrubar conexões órfãs ou o player vai reconectar e ser validado novamente
                    DB::connection('xui')->table('lines_live')
                        ->where('server_id', $id)
                        ->delete();
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
