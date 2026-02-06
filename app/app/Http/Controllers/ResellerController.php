<?php

namespace App\Http\Controllers;

use App\Models\CreditLog;
use App\Models\UserLog;
use App\Models\XuiUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResellerController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $resellers = XuiUser::where('member_group_id', 2)->get();
        } else {
            $resellers = XuiUser::where('owner_id', $user->id)
                ->where('member_group_id', 2)
                ->get();
        }

        return view('resellers.index', [
            'resellers' => $resellers
        ]);
    }

    public function create()
    {
        return view('resellers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email',
            'credits' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        if (!$user->isAdmin()) {
            if ($user->credits < $validated['credits']) {
                return back()->withErrors([
                    'error' => "Saldo insuficiente! Você tem {$user->credits} créditos e precisa de {$validated['credits']}."
                ])->withInput();
            }
        }

        $existingUser = XuiUser::where('username', $validated['username'])->first();
        if ($existingUser) {
            return back()->withErrors(['error' => 'Nome de usuário já existe'])->withInput();
        }

        DB::connection('xui')->beginTransaction();

        try {
            $reseller = XuiUser::create([
                'username' => $validated['username'],
                'password' => md5($validated['password']),
                'email' => $validated['email'] ?? '',
                'member_group_id' => 2,
                'owner_id' => $user->id,
                'credits' => $validated['credits'],
                'notes' => $validated['notes'] ?? '',
                'status' => 1,
                'date_registered' => time(),
            ]);

            if (!$user->isAdmin()) {
                $user->decrement('credits', $validated['credits']);

                // Log Legado (Admin pagando)
                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $reseller->id,
                    'amount' => -$validated['credits'],
                    'date' => time(),
                    'reason' => "Criação de revenda: {$validated['username']}",
                ]);

                // Log Novo (Admin pagando) - Type: credit_change
                // Na lógica do XUI, quando se transfere créditos, cria-se um log.
                UserLog::create([
                    'owner' => $user->id,
                    'type' => 'credit_change',
                    'action' => "Criou revenda {$validated['username']} com {$validated['credits']} créditos",
                    'log_id' => $reseller->id,
                    'package_id' => 0,
                    'cost' => $validated['credits'], // Custo positivo = Saída
                    'credits_after' => $user->fresh()->credits,
                    'date' => time(),
                    'deleted_info' => null
                ]);

                // Log Legado (Revenda recebendo)
                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $reseller->id,
                    'amount' => $validated['credits'],
                    'date' => time(),
                    'reason' => "Crédito inicial da revenda: {$validated['username']}",
                ]);
                
                 // Log Novo (Revenda Recebendo)
                 UserLog::create([
                    'owner' => $reseller->id,
                    'type' => 'credit_change',
                    'action' => "Créditos iniciais de {$user->username}",
                    'log_id' => $user->id, // De quem veio
                    'package_id' => 0,
                    'cost' => -$validated['credits'], // Custo negativo = Entrada
                    'credits_after' => $reseller->credits,
                    'date' => time(),
                    'deleted_info' => null
                ]);
            } else {
                // Se for admin criando, apenas logar a criação/entrada na revenda, sem débito no admin
                 UserLog::create([
                    'owner' => $reseller->id,
                    'type' => 'credit_change',
                    'action' => "Créditos iniciais do Admin",
                    'log_id' => $user->id,
                    'package_id' => 0,
                    'cost' => -$validated['credits'],
                    'credits_after' => $reseller->credits,
                    'date' => time(),
                    'deleted_info' => null
                ]);
            }

            DB::connection('xui')->commit();

            return redirect()->route('resellers.index')
                ->with('success', 'Revenda criada com sucesso!');

        } catch (\Exception $e) {
            DB::connection('xui')->rollBack();
            
            return back()->withErrors(['error' => 'Erro ao criar revenda: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(int $id)
    {
        $user = Auth::user();
        $reseller = XuiUser::find($id);

        if (!$reseller || $reseller->member_group_id != 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        if (!$user->isAdmin() && $reseller->owner_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        return view('resellers.edit', [
            'reseller' => $reseller
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'notes' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $user = Auth::user();
        $reseller = XuiUser::find($id);

        if (!$reseller || $reseller->member_group_id != 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        if (!$user->isAdmin() && $reseller->owner_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        $updateData = [
            'email' => $validated['email'] ?? '',
            'notes' => $validated['notes'] ?? '',
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = md5($validated['password']);
        }

        $reseller->update($updateData);

        return redirect()->route('resellers.index')
            ->with('success', 'Revenda atualizada com sucesso!');
    }

    public function recharge(Request $request, int $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
        ]);

        $user = Auth::user();
        $reseller = XuiUser::find($id);

        if (!$reseller || $reseller->member_group_id != 2) {
            return response()->json(['success' => false, 'message' => 'Revenda não encontrada'], 404);
        }

        if (!$user->isAdmin() && $reseller->owner_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Sem permissão'], 403);
        }

        $amount = (float) $validated['amount'];

        if ($amount < 0 && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Apenas admin pode remover créditos'], 403);
        }

        if (!$user->isAdmin() && $amount > 0) {
            if ($user->credits < $amount) {
                return response()->json([
                    'success' => false, 
                    'message' => "Saldo insuficiente! Você tem {$user->credits} créditos."
                ], 400);
            }
        }

        DB::connection('xui')->beginTransaction();

        try {
            $reseller->increment('credits', $amount);

            if (!$user->isAdmin() && $amount > 0) {
                $user->decrement('credits', $amount);

                // Log Legado (Admin/Revenda Pai enviando)
                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $user->id,
                    'amount' => -$amount,
                    'date' => time(),
                    'reason' => "Transferência para revenda: {$reseller->username}",
                ]);

                // Log Novo (Admin/Revenda Pai enviando)
                UserLog::create([
                    'owner' => $user->id,
                    'type' => 'credit_change',
                    'action' => "Transferência de créditos para {$reseller->username}",
                    'log_id' => $reseller->id,
                    'package_id' => 0,
                    'cost' => $amount, // Positivo = Gasto
                    'credits_after' => $user->fresh()->credits,
                    'date' => time(),
                    'deleted_info' => null
                ]);
            }

            // Log Legado (Revenda recebendo ou perdendo)
            CreditLog::create([
                'admin_id' => $user->id,
                'target_id' => $reseller->id,
                'amount' => $amount,
                'date' => time(),
                'reason' => $amount > 0 ? "Recarga de créditos" : "Remoção de créditos",
            ]);

            // Log Novo (Revenda recebendo ou perdendo)
            UserLog::create([
                'owner' => $reseller->id,
                'type' => 'credit_change',
                'action' => $amount > 0 ? "Créditos recebidos de {$user->username}" : "Créditos removidos pelo Admin",
                'log_id' => $user->id,
                'package_id' => 0,
                'cost' => -$amount, // Se amount=10 (ganhou), cost=-10. Se amount=-10 (perdeu), cost=10.
                'credits_after' => $reseller->fresh()->credits,
                'date' => time(),
                'deleted_info' => null
            ]);

            DB::connection('xui')->commit();

            return response()->json(['success' => true, 'message' => 'Créditos atualizados com sucesso!']);

        } catch (\Exception $e) {
            DB::connection('xui')->rollBack();
            
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        $reseller = XuiUser::find($id);

        if (!$reseller || $reseller->member_group_id != 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        if (!$user->isAdmin() && $reseller->owner_id != $user->id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        $reseller->delete();

        return redirect()->route('resellers.index')
            ->with('success', 'Revenda excluída com sucesso!');
    }
}
