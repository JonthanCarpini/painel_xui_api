<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AppSetting;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Se não estiver logado, segue o fluxo normal (provavelmente vai para login)
        if (!$user) {
            return $next($request);
        }

        // Admins nunca são bloqueados
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 1. Bloqueio Geral de Revendas
        if (AppSetting::get('maintenance_resellers', '0') == '1') {
            // Se for revendedor, bloqueia tudo exceto logout
            if (!$request->routeIs('logout')) {
                // Retorna a view de bloqueio específica com status 503 (Service Unavailable) ou 404 (Not Found)
                // O usuário pediu "tipo uma pagina de erro 404"
                return response()->view('errors.reseller_blocked', [], 404);
            }
        }

        // 2. Bloqueio de Testes de Canais
        if (AppSetting::get('maintenance_tests', '0') == '1') {
            // Verifica se a rota é de teste de canais
            if ($request->routeIs('channel-test.*')) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'O sistema de testes está em manutenção.'], 503);
                }
                return redirect()->route('dashboard')->with('error', 'O sistema de testes está em manutenção temporária.');
            }
        }

        return $next($request);
    }
}
