<?php

namespace App\Http\Controllers;

use App\Models\CreditLog;
use Illuminate\Support\Facades\Auth;

class CreditLogController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $logs = CreditLog::with(['admin', 'target'])
                ->orderBy('date', 'desc')
                ->paginate(50);
        } else {
            $logs = CreditLog::with(['admin', 'target'])
                ->where(function($query) use ($user) {
                    $query->where('admin_id', $user->id)
                          ->orWhere('target_id', $user->id);
                })
                ->orderBy('date', 'desc')
                ->paginate(50);
        }

        // Calcular saldo antes e após para cada log
        foreach ($logs as $log) {
            if ($log->target) {
                // Saldo atual do usuário
                $currentBalance = $log->target->credits;
                
                // Saldo após = saldo atual
                $log->balance_after = $currentBalance;
                
                // Saldo antes = saldo atual - valor da transação
                // Se foi débito (negativo), antes tinha mais
                // Se foi crédito (positivo), antes tinha menos
                $log->balance_before = $currentBalance - $log->amount;
            } else {
                $log->balance_before = 0;
                $log->balance_after = 0;
            }
        }

        $summary = [
            'total_added' => 0,
            'total_removed' => 0,
            'total_transactions' => $logs->total(),
        ];

        if ($user->isAdmin()) {
            $summary['total_added'] = CreditLog::where('amount', '>', 0)->sum('amount');
            $summary['total_removed'] = abs(CreditLog::where('amount', '<', 0)->sum('amount'));
        } else {
            $summary['total_added'] = CreditLog::where('target_id', $user->id)
                ->where('amount', '>', 0)
                ->sum('amount');
            $summary['total_removed'] = abs(CreditLog::where('admin_id', $user->id)
                ->where('amount', '<', 0)
                ->sum('amount'));
        }

        return view('credit-logs.index', [
            'logs' => $logs,
            'summary' => $summary,
        ]);
    }
}
