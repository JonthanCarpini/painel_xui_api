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
                
                // Contagem de Tickets via API
                try {
                    $api = app(\App\Services\XuiApiService::class);
                    $tickets = $api->getTickets(['limit' => 1000]); // Buscar tickets recentes
                    
                    if (isset($tickets['data']) && is_array($tickets['data'])) {
                        foreach ($tickets['data'] as $ticket) {
                            $isAdmin = $xuiUser->isAdmin();
                            $isUnread = false;
                            
                            if ($isAdmin) {
                                // Admin vê tickets onde admin_read = 0
                                if ((int)($ticket['admin_read'] ?? 1) === 0) {
                                    $isUnread = true;
                                }
                            } else {
                                // Usuário vê SEUS tickets onde user_read = 0
                                if ((int)($ticket['member_id'] ?? 0) === (int)$xuiUser->xui_id && (int)($ticket['user_read'] ?? 1) === 0) {
                                    $isUnread = true;
                                }
                            }
                            
                            if ($isUnread) {
                                $unreadTicketsCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Falha silenciosa na contagem de tickets para não quebrar o site
                    \Illuminate\Support\Facades\Log::warning('Falha ao contar tickets via API: ' . $e->getMessage());
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

            $view->with('unreadNoticesCount', $unreadNoticesCount)
                 ->with('unreadTicketsCount', $unreadTicketsCount)
                 ->with('moduleShopEnabled', $moduleShopEnabled)
                 ->with('modulePaymentsEnabled', $modulePaymentsEnabled);
        });
    }
}
