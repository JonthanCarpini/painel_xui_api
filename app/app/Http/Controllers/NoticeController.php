<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeRead;
use App\Models\PanelUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Marcar como lido para o usuário atual
        if (Auth::check()) {
            $xuiUser = Auth::user();
            $panelUser = PanelUser::where('xui_id', $xuiUser->id)->first();
            
            if ($panelUser) {
                foreach ($notices as $notice) {
                    NoticeRead::firstOrCreate([
                        'user_id' => $panelUser->id,
                        'notice_id' => $notice->id
                    ]);
                }
            }
        }

        return view('notices.index', compact('notices'));
    }
}
