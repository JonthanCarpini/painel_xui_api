<?php

namespace App\Http\Controllers;

use App\Models\CreditLog;
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

                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $reseller->id,
                    'amount' => -$validated['credits'],
                    'date' => time(),
                    'reason' => "Criação de revenda: {$validated['username']}",
                ]);

                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $reseller->id,
                    'amount' => $validated['credits'],
                    'date' => time(),
                    'reason' => "Crédito inicial da revenda: {$validated['username']}",
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

                CreditLog::create([
                    'admin_id' => $user->id,
                    'target_id' => $user->id,
                    'amount' => -$amount,
                    'date' => time(),
                    'reason' => "Transferência para revenda: {$reseller->username}",
                ]);
            }

            CreditLog::create([
                'admin_id' => $user->id,
                'target_id' => $reseller->id,
                'amount' => $amount,
                'date' => time(),
                'reason' => $amount > 0 ? "Recarga de créditos" : "Remoção de créditos",
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
