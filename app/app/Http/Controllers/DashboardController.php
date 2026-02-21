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
        $user = Auth::user();

        // Balance via API XUI (não do DB local)
        $currentBalance = $this->getBalanceFromApi((int)$user->xui_id);

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
                $obj->member = (object)['username' => $line['owner_name'] ?? $line['member_username'] ?? 'N/A'];
                
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
            $usersResp  = $this->api->getUsers();
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

                // Vendas e trials via credit_logs (date é FROM_UNIXTIME string)
                $clients = $trials = 0;
                foreach ($logItems as $log) {
                    $ld = strtotime($log['date'] ?? '');
                    if (!$ld || $ld < $start || $ld > $end) continue;
                    if ((float)($log['amount'] ?? 0) >= 0) continue;
                    $adminId = (int)($log['admin_id'] ?? 0);
                    if ($allowedIds !== null && !in_array($adminId, $allowedIds)) continue;
                    $reason = strtolower($log['reason'] ?? '');
                    if (str_contains($reason, 'cria') && !str_contains($reason, 'teste')) {
                        $clients++;
                    } elseif (str_contains($reason, 'teste') || str_contains($reason, 'trial')) {
                        $trials++;
                    }
                }
                $clientsData[] = $clients;
                $trialsData[]  = $trials;

                // Novos revendedores por dia (date_registered existe em get_users)
                $resellers = 0;
                foreach ($allUsers as $u) {
                    if ((int)($u['member_group_id'] ?? 0) !== 2) continue;
                    $dr = (int)($u['date_registered'] ?? 0);
                    if ($dr < $start || $dr > $end) continue;
                    if (!$isAdmin && (int)($u['owner_id'] ?? 0) !== $userId) continue;
                    $resellers++;
                }
                $resellersData[] = $resellers;

                // Recargas feitas por mim para outros
                $recharges = 0;
                foreach ($logItems as $log) {
                    $ld = strtotime($log['date'] ?? '');
                    if (!$ld || $ld < $start || $ld > $end) continue;
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

            $creditLogsResp = $this->api->getCreditLogs((int)$user->xui_id);
            $lastRecharges = collect($creditLogsResp['data'] ?? [])
                ->filter(fn($l) => (int)($l['admin_id'] ?? 0) === (int)$user->xui_id
                    && (int)($l['target_id'] ?? 0) !== (int)$user->xui_id
                    && (float)($l['amount'] ?? 0) > 0)
                ->sortByDesc(fn($l) => strtotime($l['date'] ?? ''))
                ->take(5)
                ->map(function($l) use ($allUsers) {
                    $obj = (object) $l;
                    $obj->date = strtotime($l['date'] ?? '') ?: 0;
                    $targetId = (int)($l['target_id'] ?? 0);
                    $targetUser = collect($allUsers)->firstWhere('id', $targetId);
                    $obj->target = (object)['username' => $targetUser['username'] ?? 'N/A'];
                    return $obj;
                })
                ->values();

            // Streams: tipo 1=live, 2=movie, 3=created_live
            $streamsResp   = $this->api->getStreams();
            $allStreams    = $streamsResp['data'] ?? [];
            $liveChannels  = count(array_filter($allStreams, fn($s) => in_array((int)($s['type'] ?? 0), [1, 3])));
            $movies        = count(array_filter($allStreams, fn($s) => (int)($s['type'] ?? 0) === 2));

            // Séries via API
            $seriesResp = $this->api->getSeriesList();
            $seriesData = $seriesResp['data'] ?? [];
            $seriesCount = count($seriesData);

            // Streams online/offline via stats do servidor
            $mainServer    = $this->getMainServerStats();
            $onlineStreams = (int)($mainServer['streams_live'] ?? 0);
            $offlineStreams = max(0, $liveChannels - $onlineStreams);

            // Load Balancers via API
            $servers = $this->api->getServers();
            $loadBalancers = collect($servers)
                ->map(fn($s) => (object) $s)
                ->values()
                ->all();

            // Conexões ativas via API
            $liveResp  = $this->api->getLiveConnections();
            $onlineNow = count($liveResp['data'] ?? []);

            return [
                'total_clients'   => $totalClients,
                'active_clients'  => $activeClients,
                'expired_clients' => $expiredClients,
                'online_now'      => $onlineNow,
                'total_resellers' => $totalResellers,
                'load_balancers'  => $loadBalancers,
                'total_streams'   => count($allStreams),
                'live_channels'   => $liveChannels,
                'movies'          => $movies,
                'series'          => $seriesCount,
                'online_streams'  => $onlineStreams,
                'offline_streams' => $offlineStreams,
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

            $myLines = array_filter($allLines, fn($l) => in_array((int)($l['member_id'] ?? 0), $myTreeIds));

            $totalClients   = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0));
            $activeClients  = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['enabled'] ?? 1) && (int)($l['admin_enabled'] ?? 1) && (int)($l['exp_date'] ?? 0) > $now));
            $expiredClients = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['exp_date'] ?? 0) < $now));
            $expiringToday  = count(array_filter($myLines, fn($l) => (int)($l['is_trial'] ?? 0) === 0 && (int)($l['exp_date'] ?? 0) >= $startOfDay && (int)($l['exp_date'] ?? 0) <= $endOfDay));

            // Vendas e testes via credit_logs (get_lines NÃO retorna created_at)
            $creditLogsResp = $this->api->getCreditLogs($userId);
            $allLogs = $creditLogsResp['data'] ?? [];
            [$salesToday, $salesMonth, $trialsToday, $trialsMonth] = $this->countSalesFromLogs(
                $allLogs, $myTreeIds, $startOfDay, $endOfDay, $startOfMonth, $endOfMonth
            );

            // Conexões ativas dos meus clientes
            $liveResp  = $this->api->getLiveConnections();
            $liveConns = $liveResp['data'] ?? [];
            $myLineIds = array_column(array_values($myLines), 'id');
            $onlineNow = count(array_filter($liveConns, fn($c) => in_array((int)($c['user_id'] ?? 0), $myLineIds)));

            // Sub-revendedores diretos
            $myDirectResellers = array_filter($allUsers, fn($u) => (int)($u['owner_id'] ?? 0) === $userId && (int)($u['member_group_id'] ?? 0) === 2);
            $totalResellers    = count($myDirectResellers);

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

            // Últimas recargas feitas por mim para sub-revendedores
            $lastRecharges = collect($allLogs)
                ->filter(fn($l) => (int)($l['admin_id'] ?? 0) === $userId
                    && (int)($l['target_id'] ?? 0) !== $userId
                    && (float)($l['amount'] ?? 0) > 0)
                ->sortByDesc(fn($l) => strtotime($l['date'] ?? ''))
                ->take(5)
                ->map(function($l) use ($allUsers) {
                    $obj = (object) $l;
                    $obj->date = strtotime($l['date'] ?? '') ?: 0;
                    $targetId = (int)($l['target_id'] ?? 0);
                    $targetUser = collect($allUsers)->firstWhere('id', $targetId);
                    $obj->target = (object)['username' => $targetUser['username'] ?? 'N/A'];
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
                return ['status' => 'offline', 'server_name' => 'Main Server'];
            }

            $data = $statsResp['data'] ?? [];
            if (empty($data)) {
                return ['status' => 'offline', 'server_name' => 'Main Server'];
            }

            $totalDisk    = max((float)($data['total_disk_space'] ?? 1), 1);
            $freeDisk     = (float)($data['free_disk_space'] ?? 0);
            $usedDiskPerc = round(100 - (($freeDisk / $totalDisk) * 100), 2);
            $inputMbps    = round(((float)($data['bytes_received'] ?? 0)) * 0.008, 2);
            $outputMbps   = round(((float)($data['bytes_sent'] ?? 0)) * 0.008, 2);

            return [
                'status'       => 'online',
                'server_name'  => $data['server_name'] ?? 'Main Server',
                'connections'  => (int)($data['open_connections'] ?? $data['total_connections'] ?? 0),
                'users'        => (int)($data['online_users'] ?? $data['total_users'] ?? 0),
                'cpu'          => (float)($data['cpu'] ?? 0),
                'streams_live' => (int)($data['total_running_streams'] ?? 0),
                'mem'          => (float)($data['total_mem_used_percent'] ?? 0),
                'requests_sec' => (int)($data['requests_per_second'] ?? 0),
                'uptime'       => $data['uptime'] ?? 'N/A',
                'io_wait'      => (float)($data['iostat_info']['cpu']['iowait'] ?? 0),
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

    private function getBalanceFromApi(int $xuiId): float
    {
        $resp = $this->api->getUser($xuiId);
        if (($resp['status'] ?? '') === 'STATUS_SUCCESS') {
            return (float)($resp['data']['credits'] ?? 0);
        }
        return 0.0;
    }

    /**
     * Conta vendas e trials a partir dos credit_logs.
     * Vendas oficiais = débitos de crédito (amount < 0) com reason contendo "Criação de linha"
     * Trials = linhas com is_trial=1 e custo 0 (sem log de crédito), estimados por exp_date
     *
     * Abordagem: credit_logs registram cada débito com timestamp.
     * - Débito com reason "Criação de linha" = venda oficial
     * - Débito com reason "Teste" ou amount=0 = trial (se existir log)
     */
    private function countSalesFromLogs(
        array $allLogs,
        array $myTreeIds,
        int $startOfDay,
        int $endOfDay,
        int $startOfMonth,
        int $endOfMonth
    ): array {
        $salesToday = $salesMonth = $trialsToday = $trialsMonth = 0;

        foreach ($allLogs as $log) {
            $date     = strtotime($log['date'] ?? '');
            $amount   = (float)($log['amount'] ?? 0);
            $adminId  = (int)($log['admin_id'] ?? 0);
            $reason   = strtolower($log['reason'] ?? '');

            if (!$date) continue;
            if (!in_array($adminId, $myTreeIds)) continue;
            if ($amount >= 0) continue;

            $isSale  = str_contains($reason, 'cria') && !str_contains($reason, 'teste');
            $isTrial = str_contains($reason, 'teste') || str_contains($reason, 'trial');

            if ($isSale) {
                if ($date >= $startOfDay && $date <= $endOfDay) $salesToday++;
                if ($date >= $startOfMonth && $date <= $endOfMonth) $salesMonth++;
            }
            if ($isTrial) {
                if ($date >= $startOfDay && $date <= $endOfDay) $trialsToday++;
                if ($date >= $startOfMonth && $date <= $endOfMonth) $trialsMonth++;
            }
        }

        return [$salesToday, $salesMonth, $trialsToday, $trialsMonth];
    }
}
