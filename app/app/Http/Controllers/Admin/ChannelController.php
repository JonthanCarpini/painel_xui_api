<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $search = $request->input('search');
        
        $query = DB::connection('xui')->table('streams as s')
            ->leftJoin('streams_servers as ss', 's.id', '=', 'ss.stream_id')
            ->select(
                's.id', 
                's.stream_display_name', 
                's.stream_source', 
                's.category_id',
                'ss.stream_status', 
                'ss.pid', 
                'ss.server_id',
                'ss.on_demand'
            );

        if ($search) {
            $query->where('s.stream_display_name', 'like', "%{$search}%")
                  ->orWhere('s.stream_source', 'like', "%{$search}%");
        }

        // Agrupar por stream_id para evitar duplicatas na lista principal (opcional, dependendo de como quer visualizar)
        // Mas se tiver LB, pode querer ver cada instância. Vamos ordenar por ID por enquanto.
        $channels = $query->orderBy('s.id', 'desc')->paginate(20);

        return view('admin.channels.index', compact('channels'));
    }

    public function edit($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $channel = DB::connection('xui')->table('streams')->where('id', $id)->first();
        
        if (!$channel) {
            return redirect()->route('settings.admin.channels.index')->with('error', 'Canal não encontrado.');
        }

        // Buscar dados do servidor associado (streams_servers)
        $streamServer = DB::connection('xui')->table('streams_servers')->where('stream_id', $id)->first();

        // Buscar categorias (Assumindo streams_categories ou similar se existir, senão text input)
        $categories = DB::connection('xui')->table('streams_categories')->get();

        // Buscar servidores disponíveis
        $servers = DB::connection('xui')->table('servers')->where('status', 1)->get();

        return view('admin.channels.edit', compact('channel', 'streamServer', 'categories', 'servers'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'stream_display_name' => 'required|string|max:255',
            'stream_source' => 'required|string',
            'category_id' => 'nullable',
            // 'server_id' => 'required|integer' // Se formos permitir mover de servidor
        ]);

        try {
            DB::connection('xui')->table('streams')->where('id', $id)->update([
                'stream_display_name' => $request->stream_display_name,
                'stream_source' => $request->stream_source,
                'category_id' => $request->category_id,
                // Outros campos conforme necessário
            ]);

            // Atualizar servidor se necessário (logica mais complexa pois envolve streams_servers)
            if ($request->has('server_id')) {
                $currentServer = DB::connection('xui')->table('streams_servers')->where('stream_id', $id)->first();
                
                if ($currentServer) {
                    if ($currentServer->server_id != $request->server_id) {
                        // Se mudou de servidor, atualiza e reseta status para forçar reinício no novo
                        DB::connection('xui')->table('streams_servers')
                            ->where('server_stream_id', $currentServer->server_stream_id)
                            ->update([
                                'server_id' => $request->server_id,
                                'pid' => null,
                                'stream_status' => 0,
                                'to_analyze' => 1
                            ]);
                    }
                } else {
                    // Se não existia registro, cria um novo
                    DB::connection('xui')->table('streams_servers')->insert([
                        'stream_id' => $id,
                        'server_id' => $request->server_id,
                        'stream_status' => 0,
                        'to_analyze' => 1
                    ]);
                }
            }

            return redirect()->route('settings.admin.channels.index')->with('success', 'Canal atualizado com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar canal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        // Implementar exclusão lógica ou física? Geralmente física no XUI
        try {
            DB::connection('xui')->table('streams')->where('id', $id)->delete();
            DB::connection('xui')->table('streams_servers')->where('stream_id', $id)->delete();
            
            return redirect()->route('settings.admin.channels.index')->with('success', 'Canal removido com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover canal: ' . $e->getMessage());
        }
    }

    public function restart($id)
    {
        // Lógica de restart já implementada no ChannelTestController, podemos reutilizar ou reimplementar
        // Inserir na tabela signals
        $streamServer = DB::connection('xui')->table('streams_servers')->where('stream_id', $id)->first();
        
        if ($streamServer) {
            DB::connection('xui')->table('signals')->insert([
                'server_id' => $streamServer->server_id,
                'pid' => $streamServer->pid,
                'rtmp' => 0, // Verificar flag correta para restart
                'time' => time(),
                'custom_data' => json_encode(['action' => 'restart_stream', 'stream_id' => $id]) // Exemplo
            ]);
            
            // Também resetar status
            DB::connection('xui')->table('streams_servers')->where('stream_id', $id)->update(['stream_status' => 0]); // 0 = stopped/starting
        }

        return back()->with('success', 'Comando de reinício enviado.');
    }
}
