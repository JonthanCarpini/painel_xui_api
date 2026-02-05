<?php

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
            'connection' => [
                'host' => config('database.connections.xui.host'),
                'database' => config('database.connections.xui.database'),
                'username' => config('database.connections.xui.username'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'config' => [
                'host' => config('database.connections.xui.host'),
                'database' => config('database.connections.xui.database'),
                'username' => config('database.connections.xui.username'),
            ]
        ], 500);
    }
});
