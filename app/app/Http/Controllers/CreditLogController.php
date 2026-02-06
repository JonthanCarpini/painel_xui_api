<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\XuiUser;

class CreditLogController extends Controller
{
    /**
     * Meus Logs (Logs do usuário logado)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $logs = $this->fetchLogs($request, $user->id);

        return view('credit-logs.index', [
            'logs' => $logs
        ]);
    }

    /**
     * Logs de Revendas (Visível apenas para Admin/Revendedor Mestre)
     */
    public function resellers(Request $request)
    {
        $user = Auth::user();

        // Se não for admin, limitar aos sub-revendedores
        $resellers = [];
        if ($user->isAdmin()) {
            $resellers = XuiUser::where('member_group_id', 2)->orderBy('username')->get();
        } else {
            // Revendedor vendo seus sub-revendedores
            $resellers = XuiUser::where('owner_id', $user->id)->orderBy('username')->get();
        }

        $targetResellerId = $request->input('reseller_id');
        $logs = null;

        if ($targetResellerId === 'all' && $user->isAdmin()) {
             $logs = $this->fetchLogs($request, null, true);
        } elseif ($targetResellerId) {
            // Verifica permissão (se é meu sub ou sou admin)
            if (!$user->isAdmin()) {
                $targetUser = XuiUser::find($targetResellerId);
                // Se não encontrar ou não for meu filho
                if (!$targetUser || $targetUser->owner_id != $user->id) {
                     // Se não for meu filho, mas for eu mesmo? (Pode acontecer se selecionar a si mesmo na lista, mas a lista filtra)
                     if ($targetResellerId != $user->id) {
                        abort(403, 'Acesso negado a esta revenda.');
                     }
                }
            }
            $logs = $this->fetchLogs($request, $targetResellerId);
        }

        return view('credit-logs.resellers', [
            'logs' => $logs,
            'resellers' => $resellers,
            'selectedResellerId' => $targetResellerId
        ]);
    }

    public function searchDestinations(Request $request)
    {
        $term = $request->input('term');
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        // Buscar em Revendedores/Admins
        $users = DB::connection('xui')->table('users')
            ->where('username', 'like', "%{$term}%")
            ->limit(5)
            ->pluck('username');

        // Buscar em Linhas (Clientes)
        $lines = DB::connection('xui')->table('lines')
            ->where('username', 'like', "%{$term}%")
            ->limit(5)
            ->pluck('username');

        $results = $users->merge($lines)->unique()->values();

        return response()->json($results);
    }

    /**
     * Lógica Central de Busca de Logs (Baseado na tabela users_logs)
     */
    private function fetchLogs(Request $request, $ownerId = null, $allResellers = false)
    {
        $query = DB::connection('xui')->table('users_logs as log')
            ->join('users as owner', 'log.owner', '=', 'owner.id')
            ->leftJoin('users_packages as pkg', 'log.package_id', '=', 'pkg.id')
            // Join for Line info
            ->leftJoin('lines', function($join) {
                $join->on('log.log_id', '=', 'lines.id')
                     ->whereIn('log.type', ['line_create', 'line_extend']);
            })
            // Join for Target User info (Reseller/Admin interaction)
            ->leftJoin('users as target_user', function($join) {
                $join->on('log.log_id', '=', 'target_user.id')
                     ->whereIn('log.type', ['credit_change', 'reseller_create']);
            })
            ->select([
                'log.id',
                'log.date',
                'log.type',
                'log.action',
                'log.cost',
                'log.credits_after',
                // Saldo Anterior = Saldo Atual + Custo (Invertido)
                DB::raw("(log.credits_after + log.cost) as credits_before"),
                'owner.username as owner_name',
                'pkg.package_name',
                'lines.username as line_username',
                'target_user.username as target_username'
            ]);

        // Filtro por Dono
        if ($ownerId) {
            $query->where('log.owner', $ownerId);
        } elseif ($allResellers) {
            $query->where('owner.member_group_id', 2);
        }

        // Filtro por Data
        if ($request->filled(['date_start', 'date_end'])) {
            $startDate = strtotime($request->date_start . ' 00:00:00');
            $endDate = strtotime($request->date_end . ' 23:59:59');
            $query->whereBetween('log.date', [$startDate, $endDate]);
        }

        // Filtro por Natureza (Entrada/Saída)
        if ($request->filled('nature')) {
            if ($request->nature == 'in') {
                $query->where('log.cost', '<', 0);
            } elseif ($request->nature == 'out') {
                $query->where('log.cost', '>', 0);
            }
        }

        // Filtro por Tipo (Cliente/Revenda)
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type == 'reseller') {
                // Revenda = Transferências e Criação de Revenda
                $query->whereIn('log.type', ['credit_change', 'reseller_create']);
            } elseif ($type == 'client') {
                // Cliente = Linhas
                $query->whereIn('log.type', ['line_create', 'line_extend']);
            } elseif ($type != 'all') {
                $query->where('log.type', $type);
            }
        }

        // Filtro por Destino (Username da linha ou da revenda alvo)
        if ($request->filled('destination')) {
            $dest = $request->destination;
            $query->where(function($q) use ($dest) {
                $q->where('lines.username', 'like', "%{$dest}%")
                  ->orWhere('target_user.username', 'like', "%{$dest}%");
            });
        }

        // Filtro de Busca Geral (Mantido para compatibilidade, mas focado em descrição)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('log.action', 'like', "%{$search}%")
                  ->orWhere('owner.username', 'like', "%{$search}%")
                  ->orWhere('lines.username', 'like', "%{$search}%")
                  ->orWhere('target_user.username', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $query->orderBy('log.date', 'desc');

        // Paginação
        $logs = $query->paginate(20)->withQueryString();

        // Transformação para View
        $logs->getCollection()->transform(function ($log) {
            $log->formatted_date = date('d/m/Y H:i:s', $log->date);

            // Natureza & Movimentação
            if ($log->cost > 0) {
                $log->nature = 'out';
                $log->nature_label = 'Saída';
                $log->amount = $log->cost;
                $log->amount_formatted = '- ' . number_format($log->cost, 2);
                $log->nature_class = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            } elseif ($log->cost < 0) {
                $log->nature = 'in';
                $log->nature_label = 'Entrada';
                $log->amount = abs($log->cost);
                $log->amount_formatted = '+ ' . number_format(abs($log->cost), 2);
                $log->nature_class = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            } else {
                $log->nature = 'neutral';
                $log->nature_label = '-';
                $log->amount = 0;
                $log->amount_formatted = '0.00';
                $log->nature_class = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            }

            // Tipo e Destino
            if (in_array($log->type, ['line_create', 'line_extend'])) {
                $log->type_label = 'Cliente';
                $log->destination = $log->line_username ?? 'Desconhecido';
                $log->type_icon = 'bi-person';
                $log->type_class = 'text-blue-600 dark:text-blue-400';
            } else {
                $log->type_label = 'Revenda';
                $log->destination = $log->target_username ?? 'Sistema/Admin';
                $log->type_icon = 'bi-shop';
                $log->type_class = 'text-purple-600 dark:text-purple-400';
            }

            // Descrição Detalhada
            $details = '';
            if ($log->type == 'line_create') {
                $details = "Cliente criado";
                if ($log->package_name) $details .= " - Pct: {$log->package_name}";
            } elseif ($log->type == 'line_extend') {
                $details = "Cliente renovado";
                // Extrair duração do action se possível (Ex: "Extended line x (+1 Month)")
                if (preg_match('/\(\+(.*?)\)/', $log->action, $matches)) {
                    $details .= " ({$matches[1]})";
                }
            } elseif ($log->type == 'credit_change') {
                if ($log->cost > 0) {
                    $details = "Envio de créditos";
                } else {
                    $details = "Recebimento de créditos";
                }
                $details .= " (" . abs($log->cost) . ")";
            } elseif ($log->type == 'reseller_create') {
                $details = "Criação de Revenda";
            } else {
                $details = $log->action;
            }
            
            $log->description_formatted = $details;

            return $log;
        });

        return $logs;
    }
}
