<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Apenas Admin pode acessar configurações
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem acessar as configurações.');
        }

        // Buscar configurações da tabela settings
        $settings = DB::connection('xui')->table('settings')->where('id', 1)->first();

        return view('settings.index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Apenas Admin pode atualizar configurações
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado.');
        }

        $validated = $request->validate([
            'server_name' => 'nullable|string|max:255',
            'default_timezone' => 'nullable|string|max:255',
            'client_prebuffer' => 'nullable|integer',
            'user_auto_kick_hours' => 'nullable|integer',
            'flood_limit' => 'nullable|integer',
            'flood_seconds' => 'nullable|integer',
            'playback_limit' => 'nullable|integer',
            'api_pass' => 'nullable|string|max:255',
            'message_of_day' => 'nullable|string',
            'tmdb_api_key' => 'nullable|string',
            'language' => 'nullable|string|max:16',
            'date_format' => 'nullable|string|max:16',
            'datetime_format' => 'nullable|string|max:16',
        ]);

        try {
            DB::connection('xui')->table('settings')
                ->where('id', 1)
                ->update($validated);

            return redirect()->route('settings.index')
                ->with('success', 'Configurações atualizadas com sucesso!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao atualizar configurações: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
