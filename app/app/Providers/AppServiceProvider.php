<?php

namespace App\Providers;

use App\Auth\XuiApiUserProvider;
use App\Models\Notice;
use App\Models\PanelUser;
use App\Models\Ticket;
use App\Services\XuiApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Auth::provider('xui_db', function ($app, array $config) {
            return new XuiApiUserProvider($app->make(XuiApiService::class));
        });

        // Compartilhar contadores com todas as views
        View::composer('*', function ($view) {
            $unreadNoticesCount = 0;
            $unreadTicketsCount = 0;
            
            if (Auth::check()) {
                $xuiUser = Auth::user();
                
                // Contagem de Tickets
                if ($xuiUser->isAdmin()) {
                    $unreadTicketsCount = Ticket::where('admin_read', 0)->count();
                } else {
                    $unreadTicketsCount = Ticket::where('member_id', $xuiUser->id)
                        ->where('user_read', 0)
                        ->count();
                }

                // Contagem de Avisos (Requer PanelUser)
                $panelUser = PanelUser::where('xui_id', $xuiUser->id)->first();
                if ($panelUser) {
                    $unreadNoticesCount = Notice::active()
                        ->whereDoesntHave('reads', function ($query) use ($panelUser) {
                            $query->where('user_id', $panelUser->id);
                        })
                        ->count();
                }
            }
            
            $view->with('unreadNoticesCount', $unreadNoticesCount)
                 ->with('unreadTicketsCount', $unreadTicketsCount);
        });
    }
}
