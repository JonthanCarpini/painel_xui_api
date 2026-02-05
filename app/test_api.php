<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\XuiApiService;

$xuiApi = new XuiApiService();

echo "=== TESTE DE CONEXÃO COM API XUI ===\n\n";

echo "1. Testando get_users...\n";
$response = $xuiApi->getUsers();

if (isset($response['result']) && $response['result'] === false) {
    echo "ERRO: " . ($response['message'] ?? 'Erro desconhecido') . "\n";
    exit(1);
}

if (!isset($response['data']) || !is_array($response['data'])) {
    echo "ERRO: Resposta inválida da API\n";
    echo "Resposta: " . json_encode($response, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "✓ API respondeu com sucesso!\n";
echo "Total de usuários: " . count($response['data']) . "\n\n";

echo "2. Procurando usuário 'carpiniadm'...\n";
$found = false;
foreach ($response['data'] as $user) {
    if (isset($user['username']) && $user['username'] === 'carpiniadm') {
        $found = true;
        echo "✓ Usuário encontrado!\n";
        echo "Dados do usuário:\n";
        echo "  - ID: " . ($user['id'] ?? 'N/A') . "\n";
        echo "  - Username: " . ($user['username'] ?? 'N/A') . "\n";
        echo "  - Password: " . ($user['password'] ?? 'N/A') . "\n";
        echo "  - Status: " . ($user['status'] ?? 'N/A') . "\n";
        echo "  - Group ID: " . ($user['member_group_id'] ?? 'N/A') . "\n";
        echo "  - Credits: " . ($user['credits'] ?? 'N/A') . "\n";
        break;
    }
}

if (!$found) {
    echo "✗ Usuário 'carpiniadm' NÃO encontrado!\n";
    echo "\nUsuários disponíveis:\n";
    foreach ($response['data'] as $user) {
        echo "  - " . ($user['username'] ?? 'N/A') . "\n";
    }
}

echo "\n=== FIM DO TESTE ===\n";
