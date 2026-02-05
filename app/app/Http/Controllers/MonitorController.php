<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\LineLive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitorController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $connectionsQuery = DB::connection('xui')
                ->table('lines_live')
                ->join('lines', 'lines_live.user_id', '=', 'lines.id')
                ->leftJoin('streams', 'lines_live.stream_id', '=', 'streams.id')
                ->whereNull('lines_live.date_end')
                ->select(
                    'lines_live.activity_id',
                    'lines_live.user_id',
                    'lines_live.stream_id',
                    'lines_live.server_id',
                    'lines_live.user_ip as ip',
                    'lines_live.user_agent',
                    'lines_live.date_start',
                    'lines_live.container',
                    'lines_live.geoip_country_code',
                    'lines_live.isp',
                    'lines.username',
                    'streams.stream_display_name as stream_name',
                    DB::raw('UNIX_TIMESTAMP() - lines_live.date_start as duration')
                )
                ->orderBy('lines_live.date_start', 'desc');
        } else {
            $myTreeIds = $user->getAllSubResellerIds();
            
            $connectionsQuery = DB::connection('xui')
                ->table('lines_live')
                ->join('lines', 'lines_live.user_id', '=', 'lines.id')
                ->leftJoin('streams', 'lines_live.stream_id', '=', 'streams.id')
                ->whereNull('lines_live.date_end')
                ->whereIn('lines.member_id', $myTreeIds)
                ->select(
                    'lines_live.activity_id',
                    'lines_live.user_id',
                    'lines_live.stream_id',
                    'lines_live.server_id',
                    'lines_live.user_ip as ip',
                    'lines_live.user_agent',
                    'lines_live.date_start',
                    'lines_live.container',
                    'lines_live.geoip_country_code',
                    'lines_live.isp',
                    'lines.username',
                    'streams.stream_display_name as stream_name',
                    DB::raw('UNIX_TIMESTAMP() - lines_live.date_start as duration')
                )
                ->orderBy('lines_live.date_start', 'desc');
        }

        $connections = $connectionsQuery->get();

        return view('monitor.index', [
            'connections' => $connections
        ]);
    }

    public function kill(int $activityId)
    {
        try {
            // 1. Buscar a conexão ativa
            $connection = DB::connection('xui')->table('lines_live')
                ->where('activity_id', $activityId)
                ->select('pid', 'server_id', 'user_id')
                ->first();

            if (!$connection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conexão não encontrada ou já encerrada.'
                ], 404);
            }

            // 2. Verificar permissão (se não for admin, verificar se é dono)
            $user = Auth::user();
            if (!$user->isAdmin()) {
                $line = DB::connection('xui')->table('lines')
                    ->where('id', $connection->user_id)
                    ->first();
                
                $myTreeIds = $user->getAllSubResellerIds();
                if (!in_array($line->member_id, $myTreeIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não tem permissão para derrubar esta conexão.'
                    ], 403);
                }
            }

            // 3. Agendar a queda na tabela 'signals'
            // O XUI lê esta tabela e mata o processo PID no servidor correspondente
            DB::connection('xui')->table('signals')->insert([
                'pid'         => $connection->pid,
                'server_id'   => $connection->server_id,
                'time'        => time(),
                'rtmp'        => 0, // 0 = Padrão (MPEGTS/HLS), 1 = RTMP
            ]);

            // 4. Limpeza visual imediata (opcional)
            // O XUI deletaria automaticamente, mas fazemos agora para feedback rápido
            DB::connection('xui')->table('lines_live')
                ->where('activity_id', $activityId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conexão derrubada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao derrubar conexão: ' . $e->getMessage()
            ], 500);
        }
    }
}
