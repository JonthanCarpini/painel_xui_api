<?php

namespace App\Http\Controllers;

use App\Services\PackageService;
use App\Services\XuiApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private XuiApiService $api,
        private PackageService $packages,
    ) {}

    public function index()
    {
        $user           = Auth::user();
        $currentBalance = $user->credits;

        if ($user->isAdmin()) {
            $stats = $this->getAdminStats();
        } else {
            $stats = $this->getResellerStats((int)$user->xui_id);
        }

        $packages = $this->packages->all();
        $bouquets = $this->packages->bouquets();

        $charts7  = $this->getChartStats((int)$user->xui_id, $user->isAdmin(), 7);
        $charts30 = $this->getChartStats((int)$user->xui_id, $user->isAdmin(), 30);

        $expiringClients = $this->getExpiringClients((int)$user->xui_id, $user->isAdmin());

        return view('dashboard.index', [
            'balance'         => $currentBalance,
            'stats'           => $stats,
            'user'            => $user,
            'packages'        => $packages,
            'bouquets'        => $bouquets,
            'charts'          => ['days_7' => $charts7, 'days_30' => $charts30],
            'expiringClients' => $expiringClients,
        ]);
    }

    private function getExpiringClients(int $userId, bool $isAdmin): array
    {
        return Cache::remember("dashboard_expiring_{$userId}", 300, function () use ($userId, $isAdmin) {
            $apiResponse = $this->api->getLines();
            $allLines    = $apiResponse['data'] ?? [];

            $allowedIds = null;
            if (!$isAdmin) {
                $allowedIds = $this->getSubResellerIds($userId);
            }

            $now    = time();
            $in7d   = strtotime('+7 days');
            $result = [];

            foreach ($allLines as $line) {
                if ((int)($line['is_trial'] ?? 0) !== 0) continue;
                $exp = (int)($line['exp_date'] ?? 0);
                if ($exp <= $now || $exp > $in7d) continue;
                if ($allowedIds !== null && !in_array((int)($line['member_id'] ?? 0), $allowedIds)) continue;
                
                // Converter para objeto para compatibilidade com a view e cast explícito
                $obj = (object)$line;
                $obj->exp_date = (int)($line['exp_date'] ?? 0);
                $obj->member = (object)['username' => $line['member_username'] ?? 'Unknown']; // Simular relacionamento member
                
                $result[] = $obj;
            }

            usort($result, fn($a, $b) => $a->exp_date <=> $b->exp_date);
            return array_slice($result, 0, 50);
        });
    }

    private function getChartStats(int $userId, bool $isAdmin, int $daysCount = 7): array
    {
        $cacheKey = "dashboard_charts_{$userId}_{$daysCount}";

        return Cache::remember($cacheKey, 300, function () use ($userId, $isAdmin, $daysCount) {
            $linesResp  = $this->api->getLines();
            $usersResp  = $this->api->getUsers();
            $allLines   = $linesResp['data'] ?? [];
            $allUsers   = $usersResp['data'] ?? [];
            $creditLogs = $this->api->getCreditLogs($isAdmin ? null : $userId);
            $logItems   = $creditLogs['data'] ?? [];

            $allowedIds = null;
            if (!$isAdmin) {
                $allowedIds = $this->getSubResellerIds($userId);
            }

            $days = $clientsData = $trialsData = $resellersData = $rechargesData = [];

            for ($i = $daysCount - 1; $i >= 0; $i--) {
                $days[]  = date('d/m', strtotime("-$i days"));
                $start   = strtotime(date('Y-m-d', strtotime("-$i days")) . ' 00:00:00');
                $end     = strtotime(date('Y-m-d', strtotime("-$i days")) . ' 23:59:59');

                $clients = $trials = 0;
                foreach ($allLines as $l) {
                    $ca = (int)($l['created_at'] ?? 0);
                    if ($ca < $start || $ca > $end) continue;
                    if ($allowedIds !== null && !in_array((int)($l['member_id'] ?? 0), $allowedIds)) continue;
                    (int)($l['is_trial'] ?? 0) ? $trials++ : $clients++;
                }
                $clientsData[]  = $clients;
                $trialsData[]   = $trials;

                $resellers = 0;
                foreach ($allUsers as $u) {
                    if ((int)($u['member_group_id'] ?? 0) !== 2) continue;
                    $dr = (int)($u['date_registered'] ?? 0);
                    if ($dr < $start || $dr > $end) continue;
                    if (!$isAdmin && (int)($u['owner_id'] ?? 0) !== $userId) continue;
                    $resellers++;
                }
                $resellersData[] = $resellers;

                $recharges = 0;
                foreach ($logItems as $log) {
                    $ld = (int)($log['date'] ?? 0);
                    if ($ld < $start || $ld > $end) continue;
                    if ((float)($log['amount'] ?? 0) <= 0) continue;
                    if ((int)($log['admin_id'] ?? 0) !== $userId) continue;
                    if ((int)($log['target_id'] ?? 0) === $userId) continue;
                    $recharges++;
                }
                $rechargesData[] = $recharges;
            }

            return [
                'labels'    => $days,
                'clients'   => $clientsData,
                'trials'    => $trialsData,
                'resellers' => $resellersData,
                'recharges' => $rechargesData,
            ];
        });
    }

    private function getAdminStats(): array
    {
        return Cache::remember('admin_dashboard_stats', 60, function () {
            $now       = time();
            $user      = Auth::user();
            $linesResp = $this->api->getLines();
            $usersResp = $this->api->getUsers();
            $allLines  = $linesResp['data'] ?? [];
            $allUsers  = $usersResp['data'] ?? [];

            $totalClients   = count(array_filter($allLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0));
            $activeClients  = count(array_filter($allLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['enabled'] ?? 1) && (int)($l['exp_date'] ?? 0) > $now));
            $expiredClients = count(array_filter($allLines, fn($l) => (int)($l['exp_date'] ?? 0) < $now));
            $totalResellers = count(array_filter($allUsers, fn($u) => (int)($u['member_group_id'] ?? 0) === 2));

            // Top 5 revendedores por clientes oficiais
            $linesByMember = [];
            foreach ($allLines as $l) {
                if ((int)($l['is_trial'] ?? 0) !== 0) continue;
                $mid = (int)($l['member_id'] ?? 0);
                $linesByMember[$mid] = ($linesByMember[$mid] ?? 0) + 1;
            }
            $topResellersData = [];
            foreach ($allUsers as $u) {
                if ((int)($u['member_group_id'] ?? 0) !== 2) continue;
                $uid = (int)($u['id'] ?? 0);
                $topResellersData[] = [
                    'username'    => $u['username'] ?? '',
                    'total_lines' => $linesByMember[$uid] ?? 0,
                    'credits'     => (float)($u['credits'] ?? 0),
                ];
            }
            usort($topResellersData, fn($a, $b) => $b['total_lines'] <=> $a['total_lines']);
            $topResellersData = array_slice($topResellersData, 0, 5);

            // Últimas recargas via API credit_logs
            $creditLogsResp = $this->api->getCreditLogs((int)$user->xui_id);
            $lastRecharges = collect($creditLogsResp['data'] ?? [])
                ->filter(fn($l) => (int)($l['admin_id'] ?? 0) === (int)$user->xui_id
                    && (int)($l['target_id'] ?? 0) !== (int)$user->xui_id
                    && (float)($l['amount'] ?? 0) > 0)
                ->sortByDesc('date')
                ->take(5)
                ->map(function($l) {
                    $obj = (object) $l;
                    $obj->date = (int)($l['date'] ?? 0);
                    return $obj;
                })
                ->values();

            // Stats de streams via API
            $streamsResp  = $this->api->getStreams();
            $allStreams    = $streamsResp['data'] ?? [];
            $liveChannels = count(array_filter($allStreams, fn($s) => in_array((int)($s['type'] ?? 0), [1, 3])));
            $movies       = count(array_filter($allStreams, fn($s) => (int)($s['type'] ?? 0) === 2));
            $totalStreams  = count($allStreams);

            // Conexões ativas via API
            $liveResp   = $this->api->getLiveConnections();
            $onlineNow  = count($liveResp['data'] ?? []);

            // Stats do servidor principal
            $mainServer = $this->getMainServerStats();

            return [
                'total_clients'   => $totalClients,
                'active_clients'  => $activeClients,
                'expired_clients' => $expiredClients,
                'online_now'      => $onlineNow,
                'total_resellers' => $totalResellers,
                'load_balancers'  => [],
                'total_streams'   => $totalStreams,
                'live_channels'   => $liveChannels,
                'movies'          => $movies,
                'series'          => 0,
                'online_streams'  => 0,
                'offline_streams' => 0,
                'top_resellers'   => $topResellersData,
                'last_recharges'  => $lastRecharges,
                'main_server'     => $mainServer,
            ];
        });
    }

    private function getResellerStats(int $userId): array
    {
        return Cache::remember("reseller_dashboard_stats_{$userId}", 60, function () use ($userId) {
            $now          = time();
            $startOfDay   = strtotime('today');
            $endOfDay     = strtotime('tomorrow') - 1;
            $startOfMonth = strtotime('first day of this month 00:00:00');
            $endOfMonth   = strtotime('last day of this month 23:59:59');

            $linesResp = $this->api->getLines();
            $usersResp = $this->api->getUsers();
            $allLines  = $linesResp['data'] ?? [];
            $allUsers  = $usersResp['data'] ?? [];

            $myTreeIds = $this->getSubResellerIds($userId);

            // Filtrar linhas da árvore
            $myLines = array_filter($allLines, fn($l) => in_array((int)($l['member_id'] ?? 0), $myTreeIds));

            $totalClients   = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0));
            $activeClients  = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['enabled'] ?? 1) && (int)($l['admin_enabled'] ?? 1) && (int)($l['exp_date'] ?? 0) > $now));
            $expiredClients = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['exp_date'] ?? 0) < $now));
            $expiringToday  = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['exp_date'] ?? 0) >= $startOfDay && (int)($l['exp_date'] ?? 0) <= $endOfDay));
            $salesToday     = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['created_at'] ?? 0) >= $startOfDay && (int)($l['created_at'] ?? 0) <= $endOfDay));
            $salesMonth     = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['created_at'] ?? 0) >= $startOfMonth && (int)($l['created_at'] ?? 0) <= $endOfMonth));
            $trialsToday    = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 1 && (int)($l['created_at'] ?? 0) >= $startOfDay && (int)($l['created_at'] ?? 0) <= $endOfDay));
            $trialsMonth    = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 1 && (int)($l['created_at'] ?? 0) >= $startOfMonth && (int)($l['created_at'] ?? 0) <= $endOfMonth));

            // Conexões ativas dos meus clientes
            $liveResp  = $this->api->getLiveConnections();
            $liveConns = $liveResp['data'] ?? [];
            $myLineIds = array_column(array_values($myLines), 'id');
            $onlineNow = count(array_filter($liveConns, fn($c) => in_array((int)($c['user_id'] ?? 0), $myLineIds)));

            // Sub-revendedores diretos
            $myDirectResellers = array_filter($allUsers, fn($u) => (int)($u['owner_id'] ?? 0) === $userId && (int)($u['member_group_id'] ?? 0) === 2);
            $myResellersIds    = array_column(array_values($myDirectResellers), 'id');
            $totalResellers    = count($myResellersIds);

            $inactiveResellers  = 0;
            $resellersNoCredit  = 0;
            $topResellers       = [];
            $linesByMember      = [];

            foreach ($myLines as $l) {
                if ((int)($l['is_trial'] ?? 0) !== 0) continue;
                $mid = (int)($l['member_id'] ?? 0);
                $linesByMember[$mid] = ($linesByMember[$mid] ?? 0) + 1;
            }

            if ($totalResellers > 0) {
                $thirtyDaysAgo = strtotime('-30 days');
                foreach ($myDirectResellers as $r) {
                    if ((int)($r['last_login'] ?? 0) < $thirtyDaysAgo) $inactiveResellers++;
                    if ((float)($r['credits'] ?? 0) <= 0) $resellersNoCredit++;
                    $rid = (int)($r['id'] ?? 0);
                    $topResellers[] = [
                        'id'          => $rid,
                        'username'    => $r['username'] ?? '',
                        'credits'     => (float)($r['credits'] ?? 0),
                        'total_lines' => $linesByMember[$rid] ?? 0,
                    ];
                }
                usort($topResellers, fn($a, $b) => $b['total_lines'] <=> $a['total_lines']);
                $topResellers = array_slice($topResellers, 0, 5);
            }

            // Últimas recargas via API credit_logs
            $creditLogsResp = $this->api->getCreditLogs($userId);
            $lastRecharges = collect($creditLogsResp['data'] ?? [])
                ->filter(fn($l) => (int)($l['admin_id'] ?? 0) === $userId
                    && (int)($l['target_id'] ?? 0) !== $userId
                    && (float)($l['amount'] ?? 0) > 0)
                ->sortByDesc('date')
                ->take(5)
                ->map(function($l) {
                    $obj = (object) $l;
                    $obj->date = (int)($l['date'] ?? 0);
                    return $obj;
                })
                ->values();

            return [
                'total_clients'       => $totalClients,
                'active_clients'      => $activeClients,
                'expired_clients'     => $expiredClients,
                'expiring_today'      => $expiringToday,
                'online_now'          => $onlineNow,
                'sales_today'         => $salesToday,
                'sales_month'         => $salesMonth,
                'trials_today'        => $trialsToday,
                'trials_month'        => $trialsMonth,
                'my_resellers'        => $totalResellers,
                'inactive_resellers'  => $inactiveResellers,
                'resellers_no_credit' => $resellersNoCredit,
                'top_resellers'       => $topResellers,
                'last_recharges'      => $lastRecharges,
            ];
        });
    }

    private function getMainServerStats(): array
    {
        return Cache::remember('main_server_stats', 60, function () {
            $statsResp = $this->api->getServerStats();

            if (($statsResp['status'] ?? '') !== 'STATUS_SUCCESS') {
                return ['status' => 'offline', 'error' => 'Servidor não encontrado'];
            }

            $data = $statsResp['data'] ?? [];

            if ((int)($data['status'] ?? 0) !== 1) {
                return ['status' => 'down', 'server_name' => $data['server_name'] ?? 'Main Server'];
            }

            $totalDisk    = $data['total_disk_space'] ?? 1;
            $freeDisk     = $data['free_disk_space'] ?? 0;
            $usedDiskPerc = round(100 - (($freeDisk / max($totalDisk, 1)) * 100), 2);
            $inputMbps    = round(($data['bytes_received'] ?? 0) * 0.008, 2);
            $outputMbps   = round(($data['bytes_sent'] ?? 0) * 0.008, 2);

            return [
                'status'       => 'online',
                'server_name'  => $data['server_name'] ?? 'Main Server',
                'connections'  => $data['connections'] ?? 0,
                'users'        => $data['users'] ?? 0,
                'cpu'          => $data['cpu'] ?? 0,
                'streams_live' => $data['total_running_streams'] ?? 0,
                'mem'          => $data['total_mem_used_percent'] ?? 0,
                'requests_sec' => $data['requests_per_second'] ?? 0,
                'uptime'       => $data['uptime'] ?? 'N/A',
                'io_wait'      => $data['iostat_info']['cpu']['iowait'] ?? 0,
                'input_mbps'   => $inputMbps,
                'output_mbps'  => $outputMbps,
                'disk_usage'   => $usedDiskPerc,
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Helper privado
    // -------------------------------------------------------------------------

    private function getSubResellerIds(int $userId): array
    {
        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];
        $ids       = [$userId];
        $changed   = true;

        while ($changed) {
            $changed = false;
            foreach ($allUsers as $u) {
                $uid = (int)($u['id'] ?? 0);
                $oid = (int)($u['owner_id'] ?? 0);
                if ($oid && in_array($oid, $ids) && !in_array($uid, $ids)) {
                    $ids[]   = $uid;
                    $changed = true;
                }
            }
        }

        return array_unique($ids);
    }
}
