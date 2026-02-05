<?php

namespace App\Providers;

use App\Auth\XuiDatabaseUserProvider;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('xui_db', function ($app, array $config) {
            return new XuiDatabaseUserProvider();
        });
    }
}
