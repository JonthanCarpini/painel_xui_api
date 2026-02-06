<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Estrutura da Tabela 'users' (XUI) ===\n";
try {
    $columns = DB::connection('xui')->select('DESCRIBE users');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Erro ao ler users: " . $e->getMessage() . "\n";
}

echo "\n=== Estrutura da Tabela 'users_logs' (XUI) ===\n";
try {
    $columns = DB::connection('xui')->select('DESCRIBE users_logs');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Erro ao ler users_logs: " . $e->getMessage() . "\n";
}

echo "\n=== Amostra de Dados 'users_logs' (Últimos 5) ===\n";
try {
    $logs = DB::connection('xui')->table('users_logs')->orderBy('id', 'desc')->limit(5)->get();
    print_r($logs->toArray());
} catch (\Exception $e) {
    echo "Erro ao ler dados de users_logs: " . $e->getMessage() . "\n";
}
