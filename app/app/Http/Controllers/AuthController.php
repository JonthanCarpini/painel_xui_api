<?php

namespace App\Http\Controllers;

use App\Models\PanelUser;
use App\Services\XuiApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private XuiApiService $api) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Sincronizar usuário local (Painel Plus)
            PanelUser::updateOrCreate(
                ['xui_id' => $user->id],
                [
                    'username' => $user->username,
                    'group_id' => $user->member_group_id,
                ]
            );

            // Atualizar last_login e ip via API
            $this->api->editUser((int)$user->id, [
                'last_login' => time(),
                'ip'         => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Usuário ou senha incorretos.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
