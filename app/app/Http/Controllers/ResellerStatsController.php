<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\UserLog;
use App\Models\XuiUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sortBy = $request->input('sort', 'username');
        $sortDir = $request->input('dir', 'asc');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $allowedSorts = ['username', 'credits', 'sub_resellers_count', 'clients_count', 'credits_sold'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'username';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $resellers = $this->getResellers($user);
        $stats = $this->buildStats($resellers, $dateFrom, $dateTo);
        $stats = $this->sortStats($stats, $sortBy, $sortDir);

        $chartData = $this->buildChartData($stats);

        return view('reseller-stats.index', [
            'stats' => $stats,
            'chartData' => $chartData,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function getResellers(XuiUser $user): \Illuminate\Support\Collection
    {
        if ($user->isAdmin()) {
            return XuiUser::where('member_group_id', 2)->get();
        }

        return XuiUser::where('owner_id', $user->id)
            ->where('member_group_id', 2)
            ->get();
    }

    private function buildStats($resellers, ?string $dateFrom, ?string $dateTo): array
    {
        $resellerIds = $resellers->pluck('id')->toArray();

        // Sub-revendedores por revenda
        $subCounts = XuiUser::whereIn('owner_id', $resellerIds)
            ->where('member_group_id', 2)
            ->selectRaw('owner_id, COUNT(*) as cnt')
            ->groupBy('owner_id')
            ->pluck('cnt', 'owner_id')
            ->toArray();

        // Clientes (lines) por revenda
        $clientCounts = Line::whereIn('member_id', $resellerIds)
            ->selectRaw('member_id, COUNT(*) as cnt')
            ->groupBy('member_id')
            ->pluck('cnt', 'member_id')
            ->toArray();

        // Créditos vendidos (transferidos) por revenda
        $creditsSoldQuery = UserLog::whereIn('owner', $resellerIds)
            ->where('type', 'credit_change')
            ->where('cost', '>', 0);

        if ($dateFrom) {
            $creditsSoldQuery->where('date', '>=', Carbon::parse($dateFrom)->startOfDay()->timestamp);
        }
        if ($dateTo) {
            $creditsSoldQuery->where('date', '<=', Carbon::parse($dateTo)->endOfDay()->timestamp);
        }

        $creditsSold = $creditsSoldQuery
            ->selectRaw('owner, SUM(cost) as total')
            ->groupBy('owner')
            ->pluck('total', 'owner')
            ->toArray();

        $stats = [];
        foreach ($resellers as $reseller) {
            $stats[] = [
                'id' => $reseller->id,
                'username' => $reseller->username,
                'credits' => (float) $reseller->credits,
                'status' => $reseller->status,
                'sub_resellers_count' => $subCounts[$reseller->id] ?? 0,
                'clients_count' => $clientCounts[$reseller->id] ?? 0,
                'credits_sold' => (float) ($creditsSold[$reseller->id] ?? 0),
            ];
        }

        return $stats;
    }

    private function sortStats(array $stats, string $sortBy, string $sortDir): array
    {
        usort($stats, function ($a, $b) use ($sortBy, $sortDir) {
            $valA = $a[$sortBy];
            $valB = $b[$sortBy];

            if (is_string($valA)) {
                $cmp = strcasecmp($valA, $valB);
            } else {
                $cmp = $valA <=> $valB;
            }

            return $sortDir === 'desc' ? -$cmp : $cmp;
        });

        return $stats;
    }

    private function buildChartData(array $stats): array
    {
        $bySubResellers = collect($stats)->sortByDesc('sub_resellers_count')->take(20)->values();
        $byClients = collect($stats)->sortByDesc('clients_count')->take(20)->values();
        $byCreditsSold = collect($stats)->sortByDesc('credits_sold')->take(20)->values();

        return [
            'sub_resellers' => [
                'labels' => $bySubResellers->pluck('username')->toArray(),
                'data' => $bySubResellers->pluck('sub_resellers_count')->toArray(),
            ],
            'clients' => [
                'labels' => $byClients->pluck('username')->toArray(),
                'data' => $byClients->pluck('clients_count')->toArray(),
            ],
            'credits_sold' => [
                'labels' => $byCreditsSold->pluck('username')->toArray(),
                'data' => $byCreditsSold->pluck('credits_sold')->toArray(),
            ],
        ];
    }
}
