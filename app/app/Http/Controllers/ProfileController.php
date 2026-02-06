<?php

namespace App\Http\Controllers;

use App\Models\PanelUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $xuiUser = Auth::user();
        
        // Busca ou cria o usuário local (caso não tenha logado ainda após a atualização)
        $panelUser = PanelUser::firstOrCreate(
            ['xui_id' => $xuiUser->id],
            [
                'username' => $xuiUser->username,
                'group_id' => $xuiUser->member_group_id
            ]
        );

        // Preferências padrão
        $preferences = [
            'panel_name' => $panelUser->getPreference('panel_name', 'Painel XUI'),
            'logo_url' => $panelUser->getPreference('logo_url', ''),
            'dark_mode' => $panelUser->getPreference('dark_mode', false),
            'show_dashboard_stats' => $panelUser->getPreference('show_dashboard_stats', true),
            'enable_notifications' => $panelUser->getPreference('enable_notifications', true),
        ];

        return view('profile.index', compact('xuiUser', 'panelUser', 'preferences'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'panel_name' => 'nullable|string|max:255',
            'logo_url' => 'nullable|url|max:255',
            'dark_mode' => 'boolean',
            'show_dashboard_stats' => 'boolean',
            'enable_notifications' => 'boolean',
        ]);

        $xuiUser = Auth::user();
        $panelUser = PanelUser::where('xui_id', $xuiUser->id)->firstOrFail();

        // Atualizar dados do usuário
        $panelUser->phone = $request->input('phone');
        $panelUser->save();

        // Salvar preferências
        $panelUser->setPreference('panel_name', $request->input('panel_name', 'Painel XUI'));
        $panelUser->setPreference('logo_url', $request->input('logo_url', ''));
        $panelUser->setPreference('dark_mode', $request->has('dark_mode'));
        $panelUser->setPreference('show_dashboard_stats', $request->has('show_dashboard_stats'));
        $panelUser->setPreference('enable_notifications', $request->has('enable_notifications'));

        return redirect()->route('profile.index')->with('success', 'Preferências atualizadas com sucesso!');
    }
}
