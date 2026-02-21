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
     * Busca logs via API user_logs (tabela users_logs) — fonte principal.
     *
     * Campos retornados pela API user_logs:
     *   id, owner (quem fez), username (owner_name), type (line/user/mag/enigma),
     *   action (new/extend/edit/enable/disable/delete/adjust_credits/send_event/convert),
     *   log_id (ID do destino), package_id, cost (créditos gastos),
     *   credits_after (saldo após), date (unix timestamp int), deleted_info (JSON).
     *
     * Enriquecido com get_lines, get_users e get_packages para resolver nomes.
     */
    private function fetchLogs(Request $request, ?int $resellerId = null, bool $allResellers = false): LengthAwarePaginator
    {
        $logsResp = $allResellers
            ? $this->api->getUserLogs()
            : $this->api->getUserLogs($resellerId);
        $items = $logsResp['data'] ?? [];

        // Filtrar apenas ações que envolvem créditos (cost != 0)
        $items = array_values(array_filter($items, fn($l) => (float)($l['cost'] ?? 0) != 0));

        // Mapas de referência para enriquecer dados
        $linesMap   = $this->buildLinesMap();
        $usersMap   = $this->buildUsersMap();
        $packagesMap = $this->buildPackagesMap();

        // Filtros do request
        $dateStart   = $request->filled('date_start') ? strtotime($request->date_start . ' 00:00:00') : null;
        $dateEnd     = $request->filled('date_end')   ? strtotime($request->date_end   . ' 23:59:59') : null;
        $nature      = $request->input('nature');
        $type        = $request->input('type');
        $destination = $request->input('destination');
        $search      = $request->input('search');

        $filtered = collect($items)->filter(function ($log) use (
            $dateStart, $dateEnd, $nature, $type, $destination, $search,
            $linesMap, $usersMap
        ) {
            $logDate = (int)($log['date'] ?? 0);
            if ($dateStart && $logDate < $dateStart) return false;
            if ($dateEnd   && $logDate > $dateEnd)   return false;

            $cost = (float)($log['cost'] ?? 0);
            if ($nature === 'out' && $cost <= 0) return false;
            if ($nature === 'in'  && $cost >= 0) return false;

            $logType = $log['type'] ?? '';
            $isClient = in_array($logType, ['line', 'mag', 'enigma']);
            if ($type === 'client'   && !$isClient) return false;
            if ($type === 'reseller' && $isClient)  return false;

            if ($destination) {
                $destName = $this->resolveDestinationName($log, $linesMap, $usersMap);
                if (!str_contains(strtolower($destName), strtolower($destination))) return false;
            }

            if ($search) {
                $s = strtolower($search);
                $desc = $this->buildDescription($log, $linesMap, $usersMap, $packagesMap);
                $destName = $this->resolveDestinationName($log, $linesMap, $usersMap);
                $haystack = strtolower($desc . ' ' . $destName . ' ' . ($log['username'] ?? ''));
                if (!str_contains($haystack, $s)) return false;
            }

            return true;
        });

        $sorted = $filtered->sortByDesc(fn($l) => (int)($l['date'] ?? 0))->values();

        $perPage     = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems   = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageItems, $sorted->count(), $perPage, $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $paginator->getCollection()->transform(
            fn($log) => $this->transformLog($log, $linesMap, $usersMap, $packagesMap)
        );

        return $paginator;
    }

    private function transformLog(array $log, $linesMap, $usersMap, $packagesMap): array
    {
        $cost         = (float)($log['cost'] ?? 0);
        $creditsAfter = (float)($log['credits_after'] ?? 0);
        $creditsBefore = $creditsAfter + $cost;
        $logDate      = (int)($log['date'] ?? 0);
        $logType      = $log['type'] ?? '';
        $action       = $log['action'] ?? '';
        $isClient     = in_array($logType, ['line', 'mag', 'enigma']);

        $log['formatted_date'] = $logDate ? date('d/m/Y H:i:s', $logDate) : '-';
        $log['credits_before'] = $creditsBefore;
        $log['credits_after']  = $creditsAfter;

        // Natureza: cost > 0 = saída de créditos, cost < 0 = entrada
        if ($cost > 0) {
            $log['nature']       = 'out';
            $log['nature_label'] = 'Saída';
            $log['nature_class'] = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            $log['amount_formatted'] = number_format($cost, 2);
        } elseif ($cost < 0) {
            $log['nature']       = 'in';
            $log['nature_label'] = 'Entrada';
            $log['nature_class'] = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            $log['amount_formatted'] = number_format(abs($cost), 2);
        } else {
            $log['nature']       = 'neutral';
            $log['nature_label'] = '-';
            $log['nature_class'] = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            $log['amount_formatted'] = '0.00';
        }

        // Tipo: Cliente ou Revenda
        if ($isClient) {
            $log['type_label'] = 'Cliente';
            $log['type_icon']  = 'bi-person';
            $log['type_class'] = 'text-blue-600 dark:text-blue-400';
        } else {
            $log['type_label'] = 'Revenda';
            $log['type_icon']  = 'bi-shop';
            $log['type_class'] = 'text-purple-600 dark:text-purple-400';
        }

        $log['destination']           = $this->resolveDestinationName($log, $linesMap, $usersMap);
        $log['description_formatted'] = $this->buildDescription($log, $linesMap, $usersMap, $packagesMap);

        return $log;
    }

    private function resolveDestinationName(array $log, $linesMap, $usersMap): string
    {
        $logId   = (int)($log['log_id'] ?? 0);
        $logType = $log['type'] ?? '';

        if (in_array($logType, ['line', 'mag', 'enigma'])) {
            return $linesMap->get($logId)['username'] ?? $this->getDeletedName($log);
        }
        if ($logType === 'user') {
            return $usersMap->get($logId)['username'] ?? $this->getDeletedName($log);
        }
        return 'N/A';
    }

    private function getDeletedName(array $log): string
    {
        $info = json_decode($log['deleted_info'] ?? '', true);
        if (is_array($info)) {
            return $info['username'] ?? $info['mac'] ?? 'Removido';
        }
        return 'Removido';
    }

    private function buildDescription(array $log, $linesMap, $usersMap, $packagesMap): string
    {
        $action    = $log['action'] ?? '';
        $logType   = $log['type'] ?? '';
        $logId     = (int)($log['log_id'] ?? 0);
        $packageId = (int)($log['package_id'] ?? 0);
        $cost      = (float)($log['cost'] ?? 0);

        $deviceLabel = match ($logType) {
            'line'    => 'Cliente',
            'user'    => 'Revenda',
            'mag'     => 'MAG',
            'enigma'  => 'Enigma2',
            default   => 'Item',
        };

        $destName = $this->resolveDestinationName($log, $linesMap, $usersMap);
        $pkgName  = $packagesMap->get($packageId)['package_name'] ?? '';

        return match ($action) {
            'new'            => "Criou {$deviceLabel}: {$destName}" . ($pkgName ? " — Pacote: {$pkgName}" : ''),
            'extend'         => "Renovou {$deviceLabel}: {$destName}" . ($pkgName ? " — Pacote: {$pkgName}" : ''),
            'edit'           => "Editou {$deviceLabel}: {$destName}",
            'enable'         => "Habilitou {$deviceLabel}: {$destName}",
            'disable'        => "Desabilitou {$deviceLabel}: {$destName}",
            'delete'         => "Excluiu {$deviceLabel}: {$destName}",
            'adjust_credits' => "Ajuste de créditos: " . number_format(abs($cost), 2),
            'convert'        => "Converteu dispositivo para {$deviceLabel}: {$destName}",
            'send_event'     => "Enviou evento para {$deviceLabel}: {$destName}",
            default          => $action ?: '-',
        };
    }

    private function buildLinesMap(): \Illuminate\Support\Collection
    {
        return collect(($this->api->getLines())['data'] ?? [])->keyBy('id');
    }

    private function buildUsersMap(): \Illuminate\Support\Collection
    {
        return collect(($this->api->getUsers())['data'] ?? [])->keyBy('id');
    }

    private function buildPackagesMap(): \Illuminate\Support\Collection
    {
        $packages = $this->api->getPackages();
        return collect(is_array($packages) ? $packages : [])->keyBy('id');
    }
}
