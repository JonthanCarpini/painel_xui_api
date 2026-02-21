<?php

namespace App\Http\Controllers;

use App\Services\XuiApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerStatsController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index(Request $request)
    {
        $user    = Auth::user();
        $sortBy  = $request->input('sort', 'username');
        $sortDir = $request->input('dir', 'asc');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $allowedSorts = ['username', 'credits', 'sub_resellers_count', 'clients_count', 'credits_sold'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'username';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'asc';

        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];

        $resellers = $this->getResellers($user, $allUsers);
        $stats     = $this->buildStats($resellers, $allUsers, $dateFrom, $dateTo);
        $stats     = $this->sortStats($stats, $sortBy, $sortDir);
        $chartData = $this->buildChartData($stats);

        return view('reseller-stats.index', [
            'stats'     => $stats,
            'chartData' => $chartData,
            'sortBy'    => $sortBy,
            'sortDir'   => $sortDir,
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
        ]);
    }

    private function getResellers($user, array $allUsers): array
    {
        if ($user->isAdmin()) {
            return array_values(array_filter($allUsers, fn($u) => (int)($u['member_group_id'] ?? 0) === 2));
        }

        return array_values(array_filter($allUsers, fn($u) =>
            (int)($u['member_group_id'] ?? 0) === 2 &&
            (int)($u['owner_id'] ?? 0) === (int)$user->xui_id
        ));
    }

    private function buildStats(array $resellers, array $allUsers, ?string $dateFrom, ?string $dateTo): array
    {
        $resellerIds = array_column($resellers, 'id');

        // Sub-revendedores por revenda
        $subCounts = [];
        foreach ($allUsers as $u) {
            if ((int)($u['member_group_id'] ?? 0) !== 2) continue;
            $oid = (int)($u['owner_id'] ?? 0);
            if (in_array($oid, $resellerIds)) {
                $subCounts[$oid] = ($subCounts[$oid] ?? 0) + 1;
            }
        }

        // Clientes por revenda via API
        $linesResp    = $this->api->getLines();
        $allLines     = $linesResp['data'] ?? [];
        $clientCounts = [];
        foreach ($allLines as $l) {
            $mid = (int)($l['member_id'] ?? 0);
            if (in_array($mid, $resellerIds)) {
                $clientCounts[$mid] = ($clientCounts[$mid] ?? 0) + 1;
            }
        }

        // Créditos vendidos via API de logs
        $logsResp    = $this->api->getUserLogs();
        $allLogs     = $logsResp['data'] ?? [];
        $creditsSold = [];

        $tsFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay()->timestamp : null;
        $tsTo   = $dateTo   ? Carbon::parse($dateTo)->endOfDay()->timestamp     : null;

        foreach ($allLogs as $log) {
            if (($log['type'] ?? '') !== 'credit_change') continue;
            if ((float)($log['cost'] ?? 0) <= 0) continue;
            $owner = (int)($log['owner'] ?? 0);
            if (!in_array($owner, $resellerIds)) continue;
            $logDate = (int)($log['date'] ?? 0);
            if ($tsFrom && $logDate < $tsFrom) continue;
            if ($tsTo   && $logDate > $tsTo)   continue;
            $creditsSold[$owner] = ($creditsSold[$owner] ?? 0) + (float)($log['cost'] ?? 0);
        }

        $stats = [];
        foreach ($resellers as $r) {
            $rid     = (int)($r['id'] ?? 0);
            $stats[] = [
                'id'                  => $rid,
                'username'            => $r['username'] ?? '',
                'credits'             => (float)($r['credits'] ?? 0),
                'status'              => (int)($r['status'] ?? 1),
                'sub_resellers_count' => $subCounts[$rid] ?? 0,
                'clients_count'       => $clientCounts[$rid] ?? 0,
                'credits_sold'        => (float)($creditsSold[$rid] ?? 0),
            ];
        }

        return $stats;
    }

    private function sortStats(array $stats, string $sortBy, string $sortDir): array
    {
        usort($stats, function ($a, $b) use ($sortBy, $sortDir) {
            $valA = $a[$sortBy];
            $valB = $b[$sortBy];
            $cmp  = is_string($valA) ? strcasecmp($valA, $valB) : ($valA <=> $valB);
            return $sortDir === 'desc' ? -$cmp : $cmp;
        });

        return $stats;
    }

    private function buildChartData(array $stats): array
    {
        $bySubResellers = collect($stats)->sortByDesc('sub_resellers_count')->take(20)->values();
        $byClients      = collect($stats)->sortByDesc('clients_count')->take(20)->values();
        $byCreditsSold  = collect($stats)->sortByDesc('credits_sold')->take(20)->values();

        return [
            'sub_resellers' => [
                'labels' => $bySubResellers->pluck('username')->toArray(),
                'data'   => $bySubResellers->pluck('sub_resellers_count')->toArray(),
            ],
            'clients' => [
                'labels' => $byClients->pluck('username')->toArray(),
                'data'   => $byClients->pluck('clients_count')->toArray(),
            ],
            'credits_sold' => [
                'labels' => $byCreditsSold->pluck('username')->toArray(),
                'data'   => $byCreditsSold->pluck('credits_sold')->toArray(),
            ],
        ];
    }
}
