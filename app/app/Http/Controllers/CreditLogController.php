<?php

namespace App\Http\Controllers;

use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CreditLogController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $logs = $this->fetchLogs($request, (int)$user->xui_id);

        return view('credit-logs.index', ['logs' => $logs]);
    }

    public function resellers(Request $request)
    {
        $user      = Auth::user();
        $usersResp = $this->api->getUsers();
        $allUsers  = $usersResp['data'] ?? [];

        if ($user->isAdmin()) {
            $resellers = array_values(array_filter($allUsers, fn($u) => (int)($u['member_group_id'] ?? 0) === 2));
        } else {
            $resellers = array_values(array_filter($allUsers, fn($u) => (int)($u['owner_id'] ?? 0) === (int)$user->xui_id));
        }

        usort($resellers, fn($a, $b) => strcmp($a['username'] ?? '', $b['username'] ?? ''));

        $targetResellerId = $request->input('reseller_id');
        $logs = null;

        if ($targetResellerId === 'all' && $user->isAdmin()) {
            $logs = $this->fetchLogs($request, null, true);
        } elseif ($targetResellerId) {
            if (!$user->isAdmin()) {
                $targetUser = collect($allUsers)->firstWhere('id', (int)$targetResellerId);
                if (!$targetUser || (int)($targetUser['owner_id'] ?? 0) !== (int)$user->xui_id) {
                    if ((int)$targetResellerId !== (int)$user->xui_id) {
                        abort(403, 'Acesso negado a esta revenda.');
                    }
                }
            }
            $logs = $this->fetchLogs($request, (int)$targetResellerId);
        }

        return view('credit-logs.resellers', [
            'logs'               => $logs,
            'resellers'          => $resellers,
            'selectedResellerId' => $targetResellerId,
        ]);
    }

    public function searchDestinations(Request $request)
    {
        $term = $request->input('term');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $usersResp = $this->api->getUsers();
        $linesResp = $this->api->getLines();

        $usernames = collect($usersResp['data'] ?? [])
            ->filter(fn($u) => str_contains(strtolower($u['username'] ?? ''), strtolower($term)))
            ->take(5)
            ->pluck('username');

        $lineNames = collect($linesResp['data'] ?? [])
            ->filter(fn($l) => str_contains(strtolower($l['username'] ?? ''), strtolower($term)))
            ->take(5)
            ->pluck('username');

        return response()->json($usernames->merge($lineNames)->unique()->values());
    }

    private function fetchLogs(Request $request, ?int $ownerId = null, bool $allResellers = false): LengthAwarePaginator
    {
        $logsResp = $this->api->getUserLogs($ownerId);
        $items    = $logsResp['data'] ?? [];

        // Enriquecer com mapas de usuários e linhas
        $usersResp = $this->api->getUsers();
        $linesResp = $this->api->getLines();
        $usersMap  = collect($usersResp['data'] ?? [])->keyBy('id');
        $linesMap  = collect($linesResp['data'] ?? [])->keyBy('id');

        // Filtros
        $dateStart   = $request->filled('date_start') ? strtotime($request->date_start . ' 00:00:00') : null;
        $dateEnd     = $request->filled('date_end')   ? strtotime($request->date_end   . ' 23:59:59') : null;
        $nature      = $request->input('nature');
        $type        = $request->input('type');
        $destination = $request->input('destination');
        $search      = $request->input('search');

        $filtered = collect($items)->filter(function ($log) use (
            $ownerId, $allResellers, $usersMap,
            $dateStart, $dateEnd, $nature, $type, $destination, $search
        ) {
            // Filtro por dono
            if ($ownerId && (int)($log['owner'] ?? 0) !== $ownerId) return false;
            if ($allResellers) {
                $owner = $usersMap->get((int)($log['owner'] ?? 0));
                if (!$owner || (int)($owner['member_group_id'] ?? 0) !== 2) return false;
            }

            // Filtro por data
            $logDate = (int)($log['date'] ?? 0);
            if ($dateStart && $logDate < $dateStart) return false;
            if ($dateEnd   && $logDate > $dateEnd)   return false;

            // Filtro por natureza
            $cost = (float)($log['cost'] ?? 0);
            if ($nature === 'in'  && $cost >= 0) return false;
            if ($nature === 'out' && $cost <= 0) return false;

            // Filtro por tipo
            $logType = $log['type'] ?? '';
            if ($type === 'reseller' && !in_array($logType, ['credit_change', 'reseller_create'])) return false;
            if ($type === 'client'   && !in_array($logType, ['line_create', 'line_extend']))       return false;
            if ($type && $type !== 'all' && $type !== 'reseller' && $type !== 'client' && $logType !== $type) return false;

            // Filtro por destino
            if ($destination) {
                $d       = strtolower($destination);
                $logId   = (int)($log['log_id'] ?? 0);
                $lineUsername  = '';
                $targetUsername = '';

                if (in_array($logType, ['line_create', 'line_extend'])) {
                    $lineUsername = strtolower($linesMap->get($logId)['username'] ?? '');
                } elseif (in_array($logType, ['credit_change', 'reseller_create'])) {
                    $targetUsername = strtolower($usersMap->get($logId)['username'] ?? '');
                }

                if (strpos($lineUsername, $d) === false && strpos($targetUsername, $d) === false) return false;
            }

            // Filtro por busca geral
            if ($search) {
                $s = strtolower($search);
                $action    = strtolower($log['action'] ?? '');
                $ownerName = strtolower($usersMap->get((int)($log['owner'] ?? 0))['username'] ?? '');
                if (strpos($action, $s) === false && strpos($ownerName, $s) === false) return false;
            }

            return true;
        });

        // Ordenar por data desc
        $sorted = $filtered->sortByDesc(fn($l) => (int)($l['date'] ?? 0))->values();

        // Paginação manual
        $perPage     = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems   = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Transformar cada item para a view
        $paginator->getCollection()->transform(function ($log) use ($usersMap, $linesMap) {
            $cost        = (float)($log['cost'] ?? 0);
            $logType     = $log['type'] ?? '';
            $ownerId     = (int)($log['owner'] ?? 0);
            $logId       = (int)($log['log_id'] ?? 0);
            $ownerUser   = $usersMap->get($ownerId);
            $lineUser    = in_array($logType, ['line_create', 'line_extend']) ? $linesMap->get($logId) : null;
            $targetUser  = in_array($logType, ['credit_change', 'reseller_create']) ? $usersMap->get($logId) : null;

            $log['formatted_date']  = date('d/m/Y H:i:s', (int)($log['date'] ?? 0));
            $log['owner_name']      = $ownerUser['username'] ?? 'N/A';
            $log['line_username']   = $lineUser['username'] ?? null;
            $log['target_username'] = $targetUser['username'] ?? null;
            $log['credits_before']  = (float)($log['credits_after'] ?? 0) + $cost;

            if ($cost > 0) {
                $log['nature'] = 'out'; $log['nature_label'] = 'Saída';
                $log['amount'] = $cost; $log['amount_formatted'] = '- ' . number_format($cost, 2);
                $log['nature_class'] = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            } elseif ($cost < 0) {
                $log['nature'] = 'in'; $log['nature_label'] = 'Entrada';
                $log['amount'] = abs($cost); $log['amount_formatted'] = '+ ' . number_format(abs($cost), 2);
                $log['nature_class'] = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            } else {
                $log['nature'] = 'neutral'; $log['nature_label'] = '-';
                $log['amount'] = 0; $log['amount_formatted'] = '0.00';
                $log['nature_class'] = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            }

            if (in_array($logType, ['line_create', 'line_extend'])) {
                $log['type_label'] = 'Cliente';
                $log['destination'] = $log['line_username'] ?? 'Desconhecido';
                $log['type_icon']  = 'bi-person';
                $log['type_class'] = 'text-blue-600 dark:text-blue-400';
            } else {
                $log['type_label'] = 'Revenda';
                $log['destination'] = $log['target_username'] ?? 'Sistema/Admin';
                $log['type_icon']  = 'bi-shop';
                $log['type_class'] = 'text-purple-600 dark:text-purple-400';
            }

            $details = '';
            if ($logType === 'line_create') {
                $details = 'Cliente criado';
                if (!empty($log['package_name'])) $details .= " - Pct: {$log['package_name']}";
            } elseif ($logType === 'line_extend') {
                $details = 'Cliente renovado';
                if (preg_match('/\(\+(.*?)\)/', $log['action'] ?? '', $m)) $details .= " ({$m[1]})";
            } elseif ($logType === 'credit_change') {
                $details = $cost > 0 ? 'Envio de créditos' : 'Recebimento de créditos';
                $details .= ' (' . abs($cost) . ')';
            } elseif ($logType === 'reseller_create') {
                $details = 'Criação de Revenda';
            } else {
                $details = $log['action'] ?? '';
            }
            $log['description_formatted'] = $details;

            return $log;
        });

        return $paginator;
    }
}
