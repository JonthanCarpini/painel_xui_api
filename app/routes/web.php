<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CreditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitorController;
use App\Http\Controllers\ResellerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/test-db', function () {
    try {
        $pdo = DB::connection('xui')->getPdo();
        $users = DB::connection('xui')->table('users')->count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Conexão XUI OK',
            'users_count' => $users,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/clear-cache', function () {
    try {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        return "Caches (App, Config, View, Route) limpos com sucesso! Tente acessar a página novamente.";
    } catch (\Exception $e) {
        return "Erro ao limpar cache: " . $e->getMessage();
    }
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

require __DIR__.'/fix_template.php';

Route::middleware(['auth', 'maintenance'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notices', [App\Http\Controllers\NoticeController::class, 'index'])->name('notices.index');
    Route::get('/updates', [App\Http\Controllers\UpdatesController::class, 'index'])->name('updates.index');

    // Pedidos de Filmes/Séries
    Route::prefix('vod-requests')->name('vod-requests.')->group(function () {
        Route::get('/', [App\Http\Controllers\VodRequestController::class, 'index'])->name('index');
        Route::get('/search', [App\Http\Controllers\VodRequestController::class, 'search'])->name('search');
        Route::get('/check', [App\Http\Controllers\VodRequestController::class, 'checkExists'])->name('check');
        Route::get('/check-seasons', [App\Http\Controllers\VodRequestController::class, 'checkSeasons'])->name('check-seasons');
        Route::post('/', [App\Http\Controllers\VodRequestController::class, 'store'])->name('store');
    });

    // Rotas de Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [App\Http\Controllers\TicketController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\TicketController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\TicketController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\TicketController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [App\Http\Controllers\TicketController::class, 'reply'])->name('reply');
        Route::put('/{id}/close', [App\Http\Controllers\TicketController::class, 'close'])->name('close');
    });

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::post('/sync', [ClientController::class, 'sync'])->name('sync');
        Route::get('/create', [ClientController::class, 'create'])->name('create');
        Route::post('/', [ClientController::class, 'store'])->name('store');
        Route::get('/create-trial', [ClientController::class, 'createTrial'])->name('create-trial');
        Route::post('/trial', [ClientController::class, 'storeTrial'])->name('store-trial');
        Route::get('/export', [ClientController::class, 'export'])->name('export');
        Route::get('/export/csv', [ClientController::class, 'exportCSV'])->name('export.csv');
        Route::get('/export/txt', [ClientController::class, 'exportTXT'])->name('export.txt');
        Route::get('/export/json', [ClientController::class, 'exportJSON'])->name('export.json');
        Route::get('/export/m3u', [ClientController::class, 'exportM3U'])->name('export.m3u');
        Route::get('/{id}/edit', [ClientController::class, 'edit'])->name('edit');
        Route::get('/{id}/edit-data', [ClientController::class, 'getEditData'])->name('edit-data');
        Route::put('/{id}', [ClientController::class, 'update'])->name('update');
        Route::post('/{id}/renew', [ClientController::class, 'renew'])->name('renew');
        Route::post('/{id}/renew-trust', [ClientController::class, 'renewTrust'])->name('renew-trust');
        Route::get('/{id}/m3u', [ClientController::class, 'generateM3u'])->name('m3u');
        Route::get('/{id}/m3u-data', [ClientController::class, 'getM3uData'])->name('m3u-data');
        Route::get('/{id}/message', [ClientController::class, 'getMessage'])->name('message');
        Route::post('/send-whatsapp', [ClientController::class, 'sendWhatsapp'])->name('send-whatsapp');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::post('/kill/{activityId}', [MonitorController::class, 'kill'])->name('kill');
    });

    Route::get('/reseller-stats', [\App\Http\Controllers\ResellerStatsController::class, 'index'])->name('reseller-stats.index');

    Route::prefix('resellers')->name('resellers.')->group(function () {
        Route::get('/', [ResellerController::class, 'index'])->name('index');
        Route::get('/create', [ResellerController::class, 'create'])->name('create');
        Route::post('/', [ResellerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ResellerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ResellerController::class, 'update'])->name('update');
        Route::post('/{id}/recharge', [ResellerController::class, 'recharge'])->name('recharge');
        Route::delete('/{id}', [ResellerController::class, 'destroy'])->name('destroy');
    });

    // Teste de Canais
    Route::prefix('channel-test')->name('channel-test.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChannelTestController::class, 'index'])->name('index');
        Route::get('/streams', [\App\Http\Controllers\ChannelTestController::class, 'getStreams'])->name('get-streams');
        Route::get('/details/{id}', [\App\Http\Controllers\ChannelTestController::class, 'getChannelDetails'])->name('details');
        Route::post('/restart/{id}', [\App\Http\Controllers\ChannelTestController::class, 'restartChannel'])->name('restart');
        Route::post('/report', [\App\Http\Controllers\ChannelTestController::class, 'report'])->name('report');
    });

    Route::get('/credit-logs', [CreditLogController::class, 'index'])->name('credit-logs.index');
    Route::get('/credit-logs/resellers', [CreditLogController::class, 'resellers'])->name('credit-logs.resellers');
    Route::get('/credit-logs/search-destinations', [CreditLogController::class, 'searchDestinations'])->name('credit-logs.search-destinations');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
    });

    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/connection', [\App\Http\Controllers\WhatsappController::class, 'connection'])->name('connection');
        Route::post('/create-instance', [\App\Http\Controllers\WhatsappController::class, 'createInstance'])->name('create-instance');
        Route::get('/qrcode', [\App\Http\Controllers\WhatsappController::class, 'qrcode'])->name('qrcode');
        Route::get('/status', [\App\Http\Controllers\WhatsappController::class, 'status'])->name('status');
        Route::post('/confirm-scan', [\App\Http\Controllers\WhatsappController::class, 'confirmScan'])->name('confirm-scan');
        Route::post('/disconnect', [\App\Http\Controllers\WhatsappController::class, 'disconnect'])->name('disconnect');
        Route::delete('/delete', [\App\Http\Controllers\WhatsappController::class, 'deleteInstance'])->name('delete');
        Route::get('/settings', [\App\Http\Controllers\WhatsappController::class, 'settings'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\WhatsappController::class, 'updateSettings'])->name('update-settings');
        Route::get('/notifications', [\App\Http\Controllers\WhatsappController::class, 'notifications'])->name('notifications');
    });

    Route::prefix('settings')->name('settings.')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
        
        // Rota auxiliar para rodar migrações via web (útil para atualizações sem SSH)
        Route::get('/migrate', function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                return redirect()->route('settings.index')->with('success', 'Banco de dados atualizado com sucesso! (Migrações executadas)');
            } catch (\Exception $e) {
                return redirect()->route('settings.index')->withErrors(['error' => 'Erro ao executar migrações: ' . $e->getMessage()]);
            }
        })->name('migrate');

        // Novas rotas para Apps, DNS e Mensagem
        Route::post('/apps', [\App\Http\Controllers\SettingsController::class, 'storeApp'])->name('apps.store');
        Route::put('/apps/{id}', [\App\Http\Controllers\SettingsController::class, 'updateApp'])->name('apps.update');
        Route::delete('/apps/{id}', [\App\Http\Controllers\SettingsController::class, 'destroyApp'])->name('apps.destroy');

        Route::post('/dns', [\App\Http\Controllers\SettingsController::class, 'storeDns'])->name('dns.store');
        Route::put('/dns/{id}', [\App\Http\Controllers\SettingsController::class, 'updateDns'])->name('dns.update');
        Route::delete('/dns/{id}', [\App\Http\Controllers\SettingsController::class, 'destroyDns'])->name('dns.destroy');

        // Rotas para Avisos (Notices)
        Route::post('/notices', [\App\Http\Controllers\SettingsController::class, 'storeNotice'])->name('notices.store');
        Route::put('/notices/{id}', [\App\Http\Controllers\SettingsController::class, 'updateNotice'])->name('notices.update');
        Route::delete('/notices/{id}', [\App\Http\Controllers\SettingsController::class, 'destroyNotice'])->name('notices.destroy');

        Route::put('/message', [\App\Http\Controllers\SettingsController::class, 'updateClientMessage'])->name('message.update');

        // Rotas para Categorias de Tickets
        Route::get('/ticket-categories', [\App\Http\Controllers\TicketCategoryController::class, 'index'])->name('ticket-categories.index');
        Route::post('/ticket-categories', [\App\Http\Controllers\TicketCategoryController::class, 'store'])->name('ticket-categories.store');
        Route::put('/ticket-categories/{id}', [\App\Http\Controllers\TicketCategoryController::class, 'update'])->name('ticket-categories.update');
        Route::delete('/ticket-categories/{id}', [\App\Http\Controllers\TicketCategoryController::class, 'destroy'])->name('ticket-categories.destroy');

        // Área de Manutenção
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('index');
            Route::put('/', [\App\Http\Controllers\MaintenanceController::class, 'update'])->name('update');
            Route::post('/ghost-rotate', [\App\Http\Controllers\MaintenanceController::class, 'runGhostRotation'])->name('ghost-rotate');
        });

        // Gestão Administrativa Avançada (Canais e Servidores)
        Route::prefix('admin')->name('admin.')->group(function () {
            // Canais
            Route::resource('channels', App\Http\Controllers\Admin\ChannelController::class)->only(['index', 'edit', 'update', 'destroy']);
            Route::post('channels/{id}/restart', [App\Http\Controllers\Admin\ChannelController::class, 'restart'])->name('channels.restart');

            // Servidores (Load Balancers)
            Route::get('servers', [App\Http\Controllers\Admin\ServerController::class, 'index'])->name('servers.index');
            Route::get('servers/{id}', [App\Http\Controllers\Admin\ServerController::class, 'show'])->name('servers.show');
            Route::post('servers/{id}/action', [App\Http\Controllers\Admin\ServerController::class, 'action'])->name('servers.action');

            // Pedidos de Filmes/Séries (Admin)
            Route::get('vod-requests', [App\Http\Controllers\Admin\VodRequestController::class, 'index'])->name('vod-requests.index');
            Route::get('vod-requests/check-xui', [App\Http\Controllers\Admin\VodRequestController::class, 'checkXui'])->name('vod-requests.check-xui');
            Route::get('vod-requests/{id}', [App\Http\Controllers\Admin\VodRequestController::class, 'show'])->name('vod-requests.show');
            Route::put('vod-requests/{id}/resolve', [App\Http\Controllers\Admin\VodRequestController::class, 'resolve'])->name('vod-requests.resolve');
        });
    });
});
