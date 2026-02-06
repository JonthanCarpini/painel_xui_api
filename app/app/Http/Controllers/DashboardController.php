<?php

namespace App\Http\Controllers;

use App\Models\Bouquet;
use App\Models\CreditLog;
use App\Models\Line;
use App\Models\LineLive;
use App\Models\Package;
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

        // Dados para Modal de Teste Rápido
        $packages = Cache::remember('packages_all', 3600, function () {
            return Package::all();
        });
        
        $blacklist = config('xui.bouquet_blacklist', []);
        $bouquets = Cache::remember('bouquets_list', 3600, function () use ($blacklist) {
            return Bouquet::whereNotIn('id', $blacklist)
                ->orderBy('bouquet_order')
                ->get();
        });

        // Dados para Gráficos (7 e 30 dias)
        $charts7 = $this->getChartStats($user->id, $user->isAdmin(), 7);
        $charts30 = $this->getChartStats($user->id, $user->isAdmin(), 30);

        // Clientes vencendo nos próximos 7 dias
        $expiringClients = $this->getExpiringClients($user->id, $user->isAdmin());

        return view('dashboard.index', [
            'balance' => $currentBalance,
            'stats' => $stats,
            'user' => $user,
            'packages' => $packages,
            'bouquets' => $bouquets,
            'charts' => [
                'days_7' => $charts7,
                'days_30' => $charts30
            ],
            'expiringClients' => $expiringClients
        ]);
    }

    private function getExpiringClients(int $userId, bool $isAdmin)
    {
        // Cache curto de 5 minutos
        return Cache::remember("dashboard_expiring_{$userId}", 300, function () use ($userId, $isAdmin) {
            $query = Line::with(['package', 'member'])
                ->where('exp_date', '>', time()) // Não vencidos ainda
                ->where('exp_date', '<=', strtotime('+7 days')) // Vencem em até 7 dias
                ->where('is_trial', 0) // Excluir testes
                ->orderBy('exp_date', 'asc');

            if (!$isAdmin) {
                // Para revendedores, mostrar apenas seus clientes diretos e de sub-revendas
                $user = XuiUser::find($userId);
                $myTreeIds = $user->getAllSubResellerIds();
                $query->whereIn('member_id', $myTreeIds);
            }

            return $query->limit(50)->get(); // Limitamos a 50 na query, front vai exibir scroll
        });
    }

    private function getChartStats(int $userId, bool $isAdmin, int $daysCount = 7): array
    {
        $cacheKey = "dashboard_charts_{$userId}_{$daysCount}";
        
        return Cache::remember($cacheKey, 300, function () use ($userId, $isAdmin, $daysCount) {
            $days = [];
            $clientsData = [];
            $trialsData = [];
            $resellersData = [];
            $rechargesData = [];

            // Loop pelos dias
            for ($i = $daysCount - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $days[] = date('d/m', strtotime("-$i days"));
                $start = strtotime("$date 00:00:00");
                $end = strtotime("$date 23:59:59");

                // --- Clients & Trials ---
                $lineQuery = DB::connection('xui')->table('lines')
                    ->whereBetween('created_at', [$start, $end]);
                
                if (!$isAdmin) {
                    $user = XuiUser::find($userId);
                    $myTreeIds = $user->getAllSubResellerIds();
                    $lineQuery->whereIn('member_id', $myTreeIds);
                }

                $clientsData[] = (clone $lineQuery)->where('is_trial', 0)->count();
                $trialsData[] = (clone $lineQuery)->where('is_trial', 1)->count();

                // --- Resellers & Recharges ---
                $resellerQuery = DB::connection('xui')->table('users')
                    ->where('member_group_id', 2)
                    ->whereBetween('date_registered', [$start, $end]);
                
                if (!$isAdmin) {
                    $resellerQuery->where('owner_id', $userId);
                }
                $resellersData[] = $resellerQuery->count();

                $rechargeQuery = DB::connection('xui')->table('users_credits_logs')
                    ->where('admin_id', $userId)
                    ->where('amount', '>', 0)
                    ->where('target_id', '!=', $userId)
                    ->whereBetween('date', [$start, $end]);
                
                $rechargesData[] = $rechargeQuery->count();
            }

            return [
                'labels' => $days,
                'clients' => $clientsData,
                'trials' => $trialsData,
                'resellers' => $resellersData,
                'recharges' => $rechargesData
            ];
        });
    }

    private function getAdminStats(): array
    {
        return Cache::remember('admin_dashboard_stats', 60, function () {
            $now = time();
            $user = Auth::user();

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

            // Últimas Recargas (Logs onde admin é o atual, target é diferente, valor negativo para admin ou positivo para target?)
            // Na tabela users_credits_logs: target_id é quem sofreu a alteração. admin_id é quem fez.
            // Se admin recarregou revenda, log deve ser: target=revenda, admin=admin, amount > 0
            $lastRecharges = CreditLog::with('target:id,username')
                ->where('admin_id', $user->id)
                ->where('target_id', '!=', $user->id) // Não auto-alterações
                ->where('amount', '>', 0) // Crédito adicionado
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get();

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
                'last_recharges' => $lastRecharges,
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
            $startOfMonth = strtotime('first day of this month 00:00:00');
            $endOfMonth = strtotime('last day of this month 23:59:59');

            $user = XuiUser::find($userId);
            $myTreeIds = $user->getAllSubResellerIds();

            // Consultas de Linhas (Clientes)
            $linesQuery = DB::connection('xui')->table('lines')->whereIn('member_id', $myTreeIds);
            
            // Stats Básicos
            $totalClients = (clone $linesQuery)->where('is_trial', 0)->count();
            $activeClients = (clone $linesQuery)
                ->where('is_trial', 0)
                ->where('enabled', 1)
                ->where('admin_enabled', 1)
                ->where('exp_date', '>', $now)
                ->count();
            
            $expiredClients = (clone $linesQuery)
                ->where('is_trial', 0)
                ->where('exp_date', '<', $now)
                ->count();
                
            $expiringToday = (clone $linesQuery)
                ->where('is_trial', 0)
                ->whereBetween('exp_date', [$startOfDay, $endOfDay])
                ->count();

            // Estatísticas de Vendas (Criação de Linhas Oficiais)
            $salesToday = (clone $linesQuery)
                ->where('is_trial', 0)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();
                
            $salesMonth = (clone $linesQuery)
                ->where('is_trial', 0)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Estatísticas de Testes
            $trialsToday = (clone $linesQuery)
                ->where('is_trial', 1)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();
                
            $trialsMonth = (clone $linesQuery)
                ->where('is_trial', 1)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Online Now
            $onlineNow = LineLive::whereIn('user_id', function($query) use ($myTreeIds) {
                $query->select('id')
                    ->from('lines')
                    ->whereIn('member_id', $myTreeIds);
            })->whereNull('date_end')->count();
            
            // Revendas Stats
            $myResellersIds = XuiUser::where('owner_id', $userId)->pluck('id')->toArray();
            $totalResellers = count($myResellersIds);
            
            // Revendas Inativas (> 30 dias sem login)
            $inactiveResellers = 0;
            $resellersNoCredit = 0;
            $topResellers = [];
            
            if ($totalResellers > 0) {
                $inactiveResellers = XuiUser::whereIn('id', $myResellersIds)
                    ->where('last_login', '<', strtotime('-30 days'))
                    ->count();
                    
                $resellersNoCredit = XuiUser::whereIn('id', $myResellersIds)
                    ->where('credits', '<=', 0)
                    ->count();
                    
                // Top 5 Revendas Diretas por número de clientes
                $topResellers = XuiUser::whereIn('id', $myResellersIds)
                    ->select(['id', 'username', 'credits'])
                    ->get()
                    ->map(function($reseller) {
                        // Contar clientes deste revendedor (apenas diretos dele)
                        $reseller->total_lines = DB::connection('xui')->table('lines')
                            ->where('member_id', $reseller->id)
                            ->where('is_trial', 0)
                            ->count();
                        return $reseller;
                    })
                    ->sortByDesc('total_lines')
                    ->take(5)
                    ->values()
                    ->toArray();
            }

            // Últimas Recargas
            $lastRecharges = CreditLog::with('target:id,username')
                ->where('admin_id', $userId)
                ->where('target_id', '!=', $userId)
                ->where('amount', '>', 0)
                ->orderBy('date', 'desc')
                ->limit(5)
                ->get();

            return [
                'total_clients' => $totalClients,
                'active_clients' => $activeClients,
                'expired_clients' => $expiredClients,
                'expiring_today' => $expiringToday,
                'online_now' => $onlineNow,
                'sales_today' => $salesToday,
                'sales_month' => $salesMonth,
                'trials_today' => $trialsToday,
                'trials_month' => $trialsMonth,
                'my_resellers' => $totalResellers,
                'inactive_resellers' => $inactiveResellers,
                'resellers_no_credit' => $resellersNoCredit,
                'top_resellers' => $topResellers,
                'last_recharges' => $lastRecharges,
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
