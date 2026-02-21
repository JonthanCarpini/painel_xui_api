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
     * Busca logs via API credit_logs (tabela users_credits_logs) — fonte principal.
     *
     * Campos retornados pela API credit_logs:
     *   id, target_id, admin_id, amount (SALDO FINAL — bug do XUI),
     *   date (FROM_UNIXTIME string), reason (texto rico quando adjust_credits é usado).
     *
     * Bug XUI: o campo `amount` armazena o saldo APÓS a operação, não a movimentação.
     * Movimentação real = amount[n] - amount[n-1] (diferença entre registros consecutivos).
     *
     * Padrões de reason (quando preenchido via adjust_credits):
     *   "Criação de linha: username (pacote: nome)" → Cliente
     *   "Renovação de linha: username (pacote: nome)" → Cliente
     *   "Transferência para revenda: username" → Revenda
     *   "Recarga de créditos" → Revenda
     *   "Estorno: falha ao criar linha username" → Cliente
     */
    private function fetchLogs(Request $request, ?int $resellerId = null, bool $allResellers = false): LengthAwarePaginator
    {
        $logsResp = $allResellers
            ? $this->api->getCreditLogs()
            : $this->api->getCreditLogs($resellerId);
        $items = $logsResp['data'] ?? [];

        // Filtrar registros sem reason (gerados pelo editUser antigo — dados inconsistentes)
        $items = array_values(array_filter($items, fn($l) => !empty($l['reason'])));

        // Calcular saldos e movimentação real (compensa bug XUI: amount = saldo final)
        $items = $this->calculateBalances($items);

        // Filtros do request
        $dateStart   = $request->filled('date_start') ? strtotime($request->date_start . ' 00:00:00') : null;
        $dateEnd     = $request->filled('date_end')   ? strtotime($request->date_end   . ' 23:59:59') : null;
        $nature      = $request->input('nature');
        $type        = $request->input('type');
        $destination = $request->input('destination');
        $search      = $request->input('search');

        $filtered = collect($items)->filter(function ($log) use (
            $dateStart, $dateEnd, $nature, $type, $destination, $search
        ) {
            $logDate = strtotime($log['date'] ?? '');
            if ($dateStart && $logDate && $logDate < $dateStart) return false;
            if ($dateEnd   && $logDate && $logDate > $dateEnd)   return false;

            $movement = $log['_movement'];
            if ($nature === 'out' && ($movement === null || $movement >= 0)) return false;
            if ($nature === 'in'  && ($movement === null || $movement <= 0)) return false;

            $isClient = $this->isClientOperation($log);
            if ($type === 'client'   && !$isClient) return false;
            if ($type === 'reseller' && $isClient)  return false;

            if ($destination) {
                $dest = $this->extractDestination($log);
                if (!str_contains(strtolower($dest), strtolower($destination))) return false;
            }

            if ($search) {
                $s = strtolower($search);
                $haystack = strtolower(
                    ($log['reason'] ?? '') . ' ' .
                    ($log['target_username'] ?? '') . ' ' .
                    ($log['owner_username'] ?? '')
                );
                if (!str_contains($haystack, $s)) return false;
            }

            return true;
        });

        $sorted = $filtered->sortByDesc(fn($l) => (int)($l['id'] ?? 0))->values();

        $perPage     = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pageItems   = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageItems, $sorted->count(), $perPage, $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $paginator->getCollection()->transform(fn($log) => $this->transformLog($log));

        return $paginator;
    }

    /**
     * Compensa o bug do XUI: o campo `amount` em users_credits_logs
     * armazena o SALDO FINAL após a operação, não a movimentação real.
     *
     * Estratégia: ordenar por id ASC, calcular movimentação como
     * diferença entre amount atual e amount anterior.
     *   saldo_depois  = amount do registro
     *   saldo_antes   = amount do registro anterior
     *   movimentação  = saldo_depois - saldo_antes
     */
    private function calculateBalances(array $items): array
    {
        if (empty($items)) return [];

        $byTarget = [];
        foreach ($items as $item) {
            $tid = (int)($item['target_id'] ?? 0);
            $byTarget[$tid][] = $item;
        }

        $result = [];
        foreach ($byTarget as $tid => $logs) {
            // Ordenar por id ASC (mais antigo primeiro)
            usort($logs, fn($a, $b) => (int)$a['id'] - (int)$b['id']);

            $prevAmount = null;
            foreach ($logs as &$log) {
                $currentAmount = (float)($log['amount'] ?? 0);
                $log['_credits_after'] = $currentAmount;

                if ($prevAmount !== null) {
                    $log['_movement']       = $currentAmount - $prevAmount;
                    $log['_credits_before'] = $prevAmount;
                } else {
                    // Primeiro registro: não temos o anterior
                    $log['_movement']       = null;
                    $log['_credits_before'] = null;
                }

                $prevAmount = $currentAmount;
            }
            unset($log);

            $result = array_merge($result, $logs);
        }

        return $result;
    }

    private function transformLog(array $log): array
    {
        $movement = $log['_movement'];
        $dateTs   = strtotime($log['date'] ?? '');
        $isClient = $this->isClientOperation($log);

        $log['formatted_date'] = $dateTs ? date('d/m/Y H:i:s', $dateTs) : ($log['date'] ?? '-');
        $log['credits_before'] = $log['_credits_before'];
        $log['credits_after']  = $log['_credits_after'];

        // Natureza baseada na movimentação calculada
        if ($movement !== null && $movement < 0) {
            $log['nature']       = 'out';
            $log['nature_label'] = 'Saída';
            $log['nature_class'] = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            $log['amount_formatted'] = number_format(abs($movement), 2);
        } elseif ($movement !== null && $movement > 0) {
            $log['nature']       = 'in';
            $log['nature_label'] = 'Entrada';
            $log['nature_class'] = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            $log['amount_formatted'] = number_format($movement, 2);
        } else {
            $log['nature']       = 'neutral';
            $log['nature_label'] = '-';
            $log['nature_class'] = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            $log['amount_formatted'] = '0.00';
        }

        // Tipo: Cliente ou Revenda (baseado no reason)
        if ($isClient) {
            $log['type_label'] = 'Cliente';
            $log['type_icon']  = 'bi-person';
            $log['type_class'] = 'text-blue-600 dark:text-blue-400';
        } else {
            $log['type_label'] = 'Revenda';
            $log['type_icon']  = 'bi-shop';
            $log['type_class'] = 'text-purple-600 dark:text-purple-400';
        }

        $log['destination']           = $this->extractDestination($log);
        $log['description_formatted'] = $this->formatDescription($log);

        return $log;
    }

    /**
     * Determina se a operação é de cliente (linha) baseado no reason.
     */
    private function isClientOperation(array $log): bool
    {
        $reason = strtolower($log['reason'] ?? '');
        $clientPatterns = ['linha', 'line', 'cliente', 'client', 'estorno'];
        foreach ($clientPatterns as $p) {
            if (str_contains($reason, $p)) return true;
        }
        return false;
    }

    /**
     * Extrai o username de destino do campo reason.
     * Padrões: "Criação de linha: USERNAME (Pacote: ...)"
     *          "Renovação de Linha: USERNAME (+...)"
     *          "Transferência para revenda: USERNAME"
     *          "Criação de revenda: USERNAME"
     *          "Crédito inicial da revenda: USERNAME"
     */
    private function extractDestination(array $log): string
    {
        $reason = $log['reason'] ?? '';

        // Padrão: "Transferência de X créditos para USERNAME" / "Recarga de X créditos por USERNAME"
        if (preg_match('/(?:para|por)\s+(\S+)/i', $reason, $m)) {
            return $m[1];
        }

        // Padrão: "Criação de linha: USERNAME (Pacote: ...)" / "Renovação de linha: USERNAME"
        if (preg_match('/:\s*([^\s(]+)/', $reason, $m)) {
            return $m[1];
        }

        // Fallback: target_username ou owner_username
        return $log['target_username'] ?? $log['owner_username'] ?? '-';
    }

    /**
     * Formata a descrição a partir do reason.
     * O reason já é descritivo, mas padronizamos para PT-BR.
     */
    private function formatDescription(array $log): string
    {
        $reason = $log['reason'] ?? '';
        if (empty($reason)) return '-';

        // Padronizar termos em inglês para PT-BR
        $replacements = [
            '/^Creation of line/i'    => 'Criação de linha',
            '/^Created line/i'        => 'Criou linha',
            '/^Line renewal/i'        => 'Renovação de linha',
            '/^Extended line/i'       => 'Renovou linha',
            '/^Renewal of line/i'     => 'Renovação de linha',
            '/^Transfer to reseller/i' => 'Transferência para revenda',
            '/^Credits transfer/i'    => 'Transferência de créditos',
            '/^Credits received/i'    => 'Créditos recebidos',
            '/^Reseller creation/i'   => 'Criação de revenda',
            '/^Initial credit/i'      => 'Crédito inicial',
            '/^Refund/i'              => 'Estorno',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $reason = preg_replace($pattern, $replacement, $reason);
        }

        return $reason;
    }
}
