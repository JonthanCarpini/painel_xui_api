<?php

namespace App\Providers;

use App\Auth\XuiApiUserProvider;
use App\Models\AppSetting;
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
                
                // Contagem de Tickets via banco local
                try {
                    if ($xuiUser->isAdmin()) {
                        $unreadTicketsCount = Ticket::where('admin_read', false)->count();
                    } else {
                        $unreadTicketsCount = Ticket::where('member_id', (int)$xuiUser->xui_id)
                            ->where('user_read', false)
                            ->count();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Falha ao contar tickets: ' . $e->getMessage());
                }
                
                // Créditos e Notificações (mantido)

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
            
            $moduleShopEnabled = AppSetting::get('module_shop_enabled', '0') === '1';
            $modulePaymentsEnabled = AppSetting::get('module_payments_enabled', '0') === '1';
            $moduleHelpEnabled = AppSetting::get('module_help_enabled', '0') === '1';

            $view->with('unreadNoticesCount', $unreadNoticesCount)
                 ->with('unreadTicketsCount', $unreadTicketsCount)
                 ->with('moduleShopEnabled', $moduleShopEnabled)
                 ->with('modulePaymentsEnabled', $modulePaymentsEnabled)
                 ->with('moduleHelpEnabled', $moduleHelpEnabled);
        });
    }
}
