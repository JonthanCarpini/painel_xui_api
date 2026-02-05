<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\LineLive;
use App\Models\Server;
use App\Models\Stream;
use App\Models\XuiUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentBalance = $user->credits;

        if ($user->isAdmin()) {
            $stats = $this->getAdminStats();
        } else {
            $stats = $this->getResellerStats($user->id);
        }

        return view('dashboard.index', [
            'balance' => $currentBalance,
            'stats' => $stats,
            'user' => $user
        ]);
    }

    private function getAdminStats(): array
    {
        return Cache::remember('admin_dashboard_stats', 60, function () {
            $now = time();

            // Top Revendas com LEFT JOIN para incluir revendedores sem linhas
            $topResellers = DB::connection('xui')
                ->table('users')
                ->leftJoin('lines', function($join) {
                    $join->on('users.id', '=', 'lines.member_id')
                         ->where('lines.is_trial', 0);
                })
                ->where('users.member_group_id', 2)
                ->select('users.id', 'users.username', 'users.credits', DB::raw('COUNT(lines.id) as total_lines'))
                ->groupBy('users.id', 'users.username', 'users.credits')
                ->orderByDesc('total_lines')
                ->limit(5)
                ->get();

            $topResellersData = [];
            foreach ($topResellers as $top) {
                $topResellersData[] = [
                    'username' => $top->username,
                    'total_lines' => $top->total_lines,
                    'credits' => $top->credits ?? 0,
                ];
            }

            // Total de Canais (Live + Created Live: tipos 1 e 3)
            $liveChannels = Stream::whereIn('type', [1, 3])->count();
            
            // Canais Online (stream_status = 0 e PID > 0 significa Online)
            $onlineChannels = DB::connection('xui')
                ->table('streams_servers')
                ->join('streams', 'streams.id', '=', 'streams_servers.stream_id')
                ->whereIn('streams.type', [1, 3])
                ->where('streams_servers.stream_status', 0)
                ->whereNotNull('streams_servers.pid')
                ->where('streams_servers.pid', '>', 0)
                ->distinct('streams_servers.stream_id')
                ->count('streams_servers.stream_id');
            
            // Canais Offline
            $offlineChannels = $liveChannels - $onlineChannels;
            
            // Total de Filmes (tipo 2)
            $movies = Stream::where('type', 2)->count();
            
            // Total de Séries (tabela streams_series, não episódios)
            $series = DB::connection('xui')
                ->table('streams_series')
                ->count();
            
            $totalStreams = Stream::count();

            return [
                'total_clients' => Line::official()->count(),
                'active_clients' => Line::where('enabled', 1)
                    ->where('exp_date', '>', $now)
                    ->count(),
                'expired_clients' => Line::where('exp_date', '<', $now)->count(),
                'online_now' => LineLive::whereNull('date_end')->count(),
                'total_resellers' => XuiUser::where('member_group_id', 2)->count(),
                'load_balancers' => Server::loadBalancers()->get(),
                'total_streams' => $totalStreams,
                'live_channels' => $liveChannels,
                'movies' => $movies,
                'series' => $series,
                'online_streams' => $onlineChannels,
                'offline_streams' => $offlineChannels,
                'top_resellers' => $topResellersData,
                'main_server' => $this->getMainServerStats(),
            ];
        });
    }

    private function getResellerStats(int $userId): array
    {
        return Cache::remember("reseller_dashboard_stats_{$userId}", 60, function () use ($userId) {
            $now = time();
            $startOfDay = strtotime('today');
            $endOfDay = strtotime('tomorrow') - 1;

            $user = XuiUser::find($userId);
            $myTreeIds = $user->getAllSubResellerIds();

            return [
                'total_clients' => Line::whereIn('member_id', $myTreeIds)->count(),
                'active_clients' => Line::whereIn('member_id', $myTreeIds)
                    ->where('enabled', 1)
                    ->where('exp_date', '>', $now)
                    ->count(),
                'my_resellers' => XuiUser::where('owner_id', $userId)->count(),
                'expiring_today' => Line::whereIn('member_id', $myTreeIds)
                    ->whereBetween('exp_date', [$startOfDay, $endOfDay])
                    ->count(),
                'online_now' => LineLive::whereIn('user_id', function($query) use ($myTreeIds) {
                    $query->select('id')
                        ->from('lines')
                        ->whereIn('member_id', $myTreeIds);
                })->whereNull('date_end')->count(),
            ];
        });
    }

    private function getMainServerStats(): array
    {
        return Cache::remember('main_server_stats', 60, function () {
            $server = DB::connection('xui')
                ->table('servers')
                ->where('is_main', 1)
                ->select('id', 'server_name', 'status', 'total_clients', 'connections', 'users', 'requests_per_second', 'watchdog_data')
                ->first();

            if (!$server) {
                return [
                    'status' => 'offline',
                    'error' => 'Servidor não encontrado'
                ];
            }

            if ($server->status != 1) {
                return [
                    'status' => 'down',
                    'server_name' => $server->server_name ?? 'Main Server'
                ];
            }

            $data = json_decode($server->watchdog_data, true) ?? [];

            $totalDisk = $data['total_disk_space'] ?? 1;
            $freeDisk = $data['free_disk_space'] ?? 0;
            $usedDiskPerc = round(100 - (($freeDisk / $totalDisk) * 100), 2);

            $inputMbps = round(($data['bytes_received'] ?? 0) * 0.008, 2);
            $outputMbps = round(($data['bytes_sent'] ?? 0) * 0.008, 2);

            return [
                'status' => 'online',
                'server_name' => $server->server_name ?? 'Main Server',
                'connections' => $server->connections ?? 0,
                'users' => $server->users ?? 0,
                'cpu' => $data['cpu'] ?? 0,
                'streams_live' => $data['total_running_streams'] ?? 0,
                'mem' => $data['total_mem_used_percent'] ?? 0,
                'requests_sec' => $server->requests_per_second ?? 0,
                'uptime' => $data['uptime'] ?? 'N/A',
                'io_wait' => $data['iostat_info']['cpu']['iowait'] ?? 0,
                'input_mbps' => $inputMbps,
                'output_mbps' => $outputMbps,
                'disk_usage' => $usedDiskPerc,
            ];
        });
    }
}
