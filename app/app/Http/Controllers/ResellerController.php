<?php

namespace App\Http\Controllers;

use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    // -------------------------------------------------------------------------
    // Listagem
    // -------------------------------------------------------------------------

    public function index()
    {
        $user     = Auth::user();
        $response = $this->api->getUsers();
        $all      = $response['data'] ?? [];

        $resellers = array_filter($all, function ($u) use ($user) {
            if ((int)($u['member_group_id'] ?? 0) !== 2) return false;
            if ($user->isAdmin()) return true;
            return (int)($u['owner_id'] ?? 0) === (int)$user->xui_id;
        });

        return view('resellers.index', ['resellers' => array_values($resellers)]);
    }

    public function create()
    {
        return view('resellers.create');
    }

    // -------------------------------------------------------------------------
    // Criar revenda
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
            'email'    => 'nullable|email',
            'credits'  => 'required|numeric|min:0',
            'notes'    => 'nullable|string',
        ]);

        $user    = Auth::user();
        $credits = (float)$validated['credits'];

        // Validar saldo do revendedor pai (via API)
        if (!$user->isAdmin() && $credits > 0) {
            $ownerResp = $this->api->getUser((int)$user->xui_id);
            $ownerData = $ownerResp['data'] ?? [];
            $ownerCredits = (float)($ownerData['credits'] ?? 0);

            if ($ownerCredits < $credits) {
                return back()->withErrors(['error' => "Saldo insuficiente! Você tem {$ownerCredits} créditos e precisa de {$credits}."])->withInput();
            }
        }

        // Criar revenda via API (owner_id = reseller logado)
        $result = $this->api->createUser([
            'username'        => $validated['username'],
            'password'        => $validated['password'],
            'email'           => $validated['email'] ?? '',
            'member_group_id' => 2,
            'owner_id'        => (int)$user->xui_id,
            'credits'         => $credits,
            'notes'           => $validated['notes'] ?? '',
            'status'          => 1,
        ]);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Erro ao criar revenda: ' . ($result['message'] ?? 'erro desconhecido')])->withInput();
        }

        $resellerId = (int)($result['id'] ?? 0);

        // Debitar créditos do pai (revendedor) via API
        if (!$user->isAdmin() && $credits > 0) {
            $parentId = (int)$user->xui_id;
            $reason   = "Criação de sub-revenda: {$validated['username']} ({$credits} créditos)";
            $debit    = $this->api->subtractCredits($parentId, $credits, $parentId, $reason);

            if (($debit['status'] ?? '') !== 'STATUS_SUCCESS') {
                // Compensação: deletar a revenda criada
                $this->api->deleteUser($resellerId);
                return back()->withErrors(['error' => 'Falha ao debitar créditos do pai. Revenda cancelada.'])->withInput();
            }

        }

        return redirect()->route('resellers.index')->with('success', 'Revenda criada com sucesso!');
    }

    // -------------------------------------------------------------------------
    // Editar revenda
    // -------------------------------------------------------------------------

    public function edit(int $id)
    {
        $user   = Auth::user();
        $result = $this->api->getUser($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS' || (int)($result['data']['member_group_id'] ?? 0) !== 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        $reseller = $result['data'];

        if (!$user->isAdmin() && (int)($reseller['owner_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        return view('resellers.edit', ['reseller' => $reseller]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'email'    => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'notes'    => 'nullable|string',
            'status'   => 'required|boolean',
        ]);

        $user   = Auth::user();
        $result = $this->api->getUser($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS' || (int)($result['data']['member_group_id'] ?? 0) !== 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        $reseller = $result['data'];

        if (!$user->isAdmin() && (int)($reseller['owner_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        $updateData = [
            'email'  => $validated['email'] ?? '',
            'notes'  => $validated['notes'] ?? '',
            'status' => (int)$validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $editResult = $this->api->editUser($id, $updateData);

        if (($editResult['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Erro ao atualizar revenda: ' . ($editResult['message'] ?? 'erro')]);
        }

        return redirect()->route('resellers.index')->with('success', 'Revenda atualizada com sucesso!');
    }

    // -------------------------------------------------------------------------
    // Recarga de créditos
    // -------------------------------------------------------------------------

    public function recharge(Request $request, int $id)
    {
        $validated = $request->validate(['amount' => 'required|numeric']);

        $user   = Auth::user();
        $amount = (float)$validated['amount'];

        $resellerResp = $this->api->getUser($id);
        if (($resellerResp['status'] ?? '') !== 'STATUS_SUCCESS' || (int)($resellerResp['data']['member_group_id'] ?? 0) !== 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        $reseller = $resellerResp['data'];

        if (!$user->isAdmin() && (int)($reseller['owner_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        if ($amount < 0 && !$user->isAdmin()) {
            return back()->withErrors(['error' => 'Apenas admin pode remover créditos']);
        }

        // Validar saldo do pai
        if (!$user->isAdmin() && $amount > 0) {
            $ownerResp    = $this->api->getUser((int)$user->xui_id);
            $ownerCredits = (float)($ownerResp['data']['credits'] ?? 0);

            if ($ownerCredits < $amount) {
                return back()->withErrors(['error' => "Saldo insuficiente! Você tem {$ownerCredits} créditos."]);
            }
        }

        // Aplicar créditos na revenda
        $adminId       = (int)$user->xui_id;
        $rechargeLabel = $amount >= 0 ? "Recarga de {$amount} créditos por {$user->username}" : "Remoção de " . abs($amount) . " créditos por {$user->username}";
        $rechargeResult = $amount >= 0
            ? $this->api->addCredits($id, $amount, $adminId, $rechargeLabel)
            : $this->api->subtractCredits($id, abs($amount), $adminId, $rechargeLabel);

        if (($rechargeResult['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Erro ao atualizar créditos da revenda: ' . ($rechargeResult['message'] ?? 'erro')]);
        }

        $resellerCreditsAfter = $rechargeResult['credits_after'] ?? 0;

        // Debitar do pai (se não for admin)
        if (!$user->isAdmin() && $amount > 0) {
            $parentId    = (int)$user->xui_id;
            $parentReason = "Transferência de {$amount} créditos para {$reseller['username']}";
            $debit = $this->api->subtractCredits($parentId, $amount, $parentId, $parentReason);

            if (($debit['status'] ?? '') !== 'STATUS_SUCCESS') {
                // Compensação: reverter créditos da revenda
                $this->api->subtractCredits($id, $amount, $adminId, "Estorno: falha na transferência para {$reseller['username']}");
                return back()->withErrors(['error' => 'Falha ao debitar créditos do pai. Operação revertida.']);
            }

        }

        return back()->with('success', 'Créditos atualizados com sucesso!');
    }

    // -------------------------------------------------------------------------
    // Excluir revenda
    // -------------------------------------------------------------------------

    public function destroy(int $id)
    {
        $user   = Auth::user();
        $result = $this->api->getUser($id);

        if (($result['status'] ?? '') !== 'STATUS_SUCCESS' || (int)($result['data']['member_group_id'] ?? 0) !== 2) {
            return back()->withErrors(['error' => 'Revenda não encontrada']);
        }

        $reseller = $result['data'];

        if (!$user->isAdmin() && (int)($reseller['owner_id'] ?? 0) !== (int)$user->xui_id) {
            return back()->withErrors(['error' => 'Sem permissão']);
        }

        $deleteResult = $this->api->deleteUser($id);

        if (($deleteResult['status'] ?? '') !== 'STATUS_SUCCESS') {
            return back()->withErrors(['error' => 'Erro ao excluir revenda: ' . ($deleteResult['message'] ?? 'erro')]);
        }

        return redirect()->route('resellers.index')->with('success', 'Revenda excluída com sucesso!');
    }

}
