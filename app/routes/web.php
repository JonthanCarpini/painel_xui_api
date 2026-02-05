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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
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
        Route::get('/{id}/m3u', [ClientController::class, 'generateM3u'])->name('m3u');
        Route::get('/{id}/m3u-data', [ClientController::class, 'getM3uData'])->name('m3u-data');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('monitor')->name('monitor.')->group(function () {
        Route::get('/', [MonitorController::class, 'index'])->name('index');
        Route::post('/kill/{activityId}', [MonitorController::class, 'kill'])->name('kill');
    });

    Route::prefix('resellers')->name('resellers.')->group(function () {
        Route::get('/', [ResellerController::class, 'index'])->name('index');
        Route::get('/create', [ResellerController::class, 'create'])->name('create');
        Route::post('/', [ResellerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ResellerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ResellerController::class, 'update'])->name('update');
        Route::post('/{id}/recharge', [ResellerController::class, 'recharge'])->name('recharge');
        Route::delete('/{id}', [ResellerController::class, 'destroy'])->name('destroy');
    });

    Route::get('/credit-logs', [CreditLogController::class, 'index'])->name('credit-logs.index');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
    });

    Route::prefix('settings')->name('settings.')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\SettingsController::class, 'update'])->name('update');
    });
});
