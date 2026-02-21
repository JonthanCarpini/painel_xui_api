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

    /**
     * Busca logs de crédito via API credit_logs (tabela users_credits_logs).
     * Campos retornados: id, target_id, admin_id, target_username, owner_username,
     *                     amount (float), date (FROM_UNIXTIME string), reason (string).
     */
    private function fetchLogs(Request $request, ?int $resellerId = null, bool $allResellers = false): LengthAwarePaginator
    {
        $logsResp = $allResellers
            ? $this->api->getCreditLogs()
            : $this->api->getCreditLogs($resellerId);
        $items = $logsResp['data'] ?? [];

        // Filtros
        $dateStart   = $request->filled('date_start') ? strtotime($request->date_start . ' 00:00:00') : null;
        $dateEnd     = $request->filled('date_end')   ? strtotime($request->date_end   . ' 23:59:59') : null;
        $nature      = $request->input('nature');
        $type        = $request->input('type');
        $destination = $request->input('destination');
        $search      = $request->input('search');

        $filtered = collect($items)->filter(function ($log) use (
            $resellerId, $allResellers,
            $dateStart, $dateEnd, $nature, $type, $destination, $search
        ) {
            $logDate = strtotime($log['date'] ?? '');
            if ($dateStart && $logDate < $dateStart) return false;
            if ($dateEnd   && $logDate > $dateEnd)   return false;

            $amount = (float)($log['amount'] ?? 0);
            if ($nature === 'in'  && $amount <= 0) return false;
            if ($nature === 'out' && $amount >= 0) return false;

            $reason = strtolower($log['reason'] ?? '');
            $isClient   = str_contains($reason, 'line') || str_contains($reason, 'client')
                       || str_contains($reason, 'cria') || str_contains($reason, 'renov')
                       || str_contains($reason, 'teste') || str_contains($reason, 'trial');
            $isReseller = !$isClient;

            if ($type === 'client'   && !$isClient)   return false;
            if ($type === 'reseller' && !$isReseller)  return false;

            if ($destination) {
                $d = strtolower($destination);
                $target = strtolower($log['target_username'] ?? '');
                $owner  = strtolower($log['owner_username'] ?? '');
                if (!str_contains($target, $d) && !str_contains($owner, $d)) return false;
            }

            if ($search) {
                $s = strtolower($search);
                $haystack = strtolower(
                    ($log['reason'] ?? '') . ' ' .
                    ($log['target_username'] ?? '') . ' ' .
                    ($log['owner_username'] ?? '') . ' ' .
                    ($log['amount'] ?? '')
                );
                if (!str_contains($haystack, $s)) return false;
            }

            return true;
        });

        $sorted = $filtered->sortByDesc(fn($l) => strtotime($l['date'] ?? ''))->values();

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

        $paginator->getCollection()->transform(function ($log) {
            $amount = (float)($log['amount'] ?? 0);
            $dateTs = strtotime($log['date'] ?? '');

            $log['formatted_date']    = $dateTs ? date('d/m/Y H:i:s', $dateTs) : ($log['date'] ?? '-');
            $log['owner_name']        = $log['owner_username'] ?? 'N/A';
            $log['target_username']   = $log['target_username'] ?? 'N/A';

            if ($amount < 0) {
                $log['nature']       = 'out';
                $log['nature_label'] = 'Saída';
                $log['nature_class'] = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                $log['amount_formatted'] = number_format(abs($amount), 2);
            } elseif ($amount > 0) {
                $log['nature']       = 'in';
                $log['nature_label'] = 'Entrada';
                $log['nature_class'] = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                $log['amount_formatted'] = number_format($amount, 2);
            } else {
                $log['nature']       = 'neutral';
                $log['nature_label'] = '-';
                $log['nature_class'] = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
                $log['amount_formatted'] = '0.00';
            }

            $reason = strtolower($log['reason'] ?? '');
            $isClient = str_contains($reason, 'line') || str_contains($reason, 'client')
                     || str_contains($reason, 'cria') || str_contains($reason, 'renov')
                     || str_contains($reason, 'teste') || str_contains($reason, 'trial');

            if ($isClient) {
                $log['type_label'] = 'Cliente';
                $log['type_icon']  = 'bi-person';
                $log['type_class'] = 'text-blue-600 dark:text-blue-400';
            } else {
                $log['type_label'] = 'Revenda';
                $log['type_icon']  = 'bi-shop';
                $log['type_class'] = 'text-purple-600 dark:text-purple-400';
            }

            $log['destination']            = $log['target_username'];
            $log['description_formatted']  = $log['reason'] ?? '-';
            $log['credits_before']         = 0;
            $log['credits_after']          = 0;

            return $log;
        });

        return $paginator;
    }
}
