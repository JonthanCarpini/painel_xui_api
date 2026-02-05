<?php
// Script de diagnóstico web para conexão de banco de dados
// Acessar via http://SEU_IP/test_connection.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostico de Conexao - Painel XUI</h1>";
echo "<pre>";

echo "<h2>1. Ambiente PHP</h2>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "<h2>2. Leitura do arquivo .env</h2>";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "Arquivo .env encontrado em: $envFile\n";
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "Conteudo (filtrado):\n";
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            
            // Ocultar senhas
            if (strpos($key, 'PASSWORD') !== false || strpos($key, 'KEY') !== false) {
                $val = substr($val, 0, 3) . '...';
            }
            
            if (strpos($key, 'DB_') !== false) {
                echo "$key=$val\n";
            }
        }
    }
} else {
    echo "[ERRO] Arquivo .env NAO encontrado em: $envFile\n";
}

echo "<h2>3. Teste de Conexao MySQL Direta (PDO)</h2>";

function testPdo($host, $port, $db, $user, $pass, $name) {
    echo "Testando conexao $name ($host:$port)...\n";
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
        $start = microtime(true);
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        $end = microtime(true);
        echo "[OK] Conectado com sucesso em " . round(($end - $start) * 1000, 2) . "ms\n";
        
        // Info do servidor
        echo "Server Info: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n";
        
    } catch (PDOException $e) {
        echo "[ERRO] Falha na conexao: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Ler credenciais do .env (simulando o que o Laravel faz, mas de forma bruta para teste)
$envContents = file_get_contents($envFile);
preg_match('/DB_HOST=(.*)/', $envContents, $m_host);
preg_match('/DB_PORT=(.*)/', $envContents, $m_port);
preg_match('/DB_DATABASE=(.*)/', $envContents, $m_db);
preg_match('/DB_USERNAME=(.*)/', $envContents, $m_user);
preg_match('/DB_PASSWORD="(.*)"/', $envContents, $m_pass) ?: preg_match('/DB_PASSWORD=(.*)/', $envContents, $m_pass);

$local_host = trim($m_host[1] ?? '127.0.0.1');
$local_port = trim($m_port[1] ?? '3306');
$local_db = trim($m_db[1] ?? 'painel_plus');
$local_user = trim($m_user[1] ?? 'painel_user');
$local_pass = trim($m_pass[1] ?? '');

preg_match('/XUI_DB_HOST=(.*)/', $envContents, $mx_host);
preg_match('/XUI_DB_PORT=(.*)/', $envContents, $mx_port);
preg_match('/XUI_DB_DATABASE=(.*)/', $envContents, $mx_db);
preg_match('/XUI_DB_USERNAME=(.*)/', $envContents, $mx_user);
preg_match('/XUI_DB_PASSWORD="(.*)"/', $envContents, $mx_pass) ?: preg_match('/XUI_DB_PASSWORD=(.*)/', $envContents, $mx_pass);

$remote_host = trim($mx_host[1] ?? '109.205.178.143');
$remote_port = trim($mx_port[1] ?? '3306');
$remote_db = trim($mx_db[1] ?? 'xui');
$remote_user = trim($mx_user[1] ?? 'painel_office');
$remote_pass = trim($mx_pass[1] ?? 'Flamengo@2015');

// Teste Local
testPdo($local_host, $local_port, $local_db, $local_user, $local_pass, "LOCAL (Painel Plus)");

// Teste Remoto
testPdo($remote_host, $remote_port, $remote_db, $remote_user, $remote_pass, "REMOTO (XUI)");

echo "<h2>4. Verificacao de Cache Laravel</h2>";
$configCache = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($configCache)) {
    echo "Cache de config encontrado: $configCache\n";
    $config = include $configCache;
    
    echo "Configuracao 'database.connections.xui' no cache:\n";
    $xuiConfig = $config['database']['connections']['xui'] ?? null;
    if ($xuiConfig) {
        print_r([
            'host' => $xuiConfig['host'],
            'port' => $xuiConfig['port'],
            'database' => $xuiConfig['database'],
            'username' => $xuiConfig['username'],
            'password' => substr($xuiConfig['password'], 0, 3) . '...',
        ]);
    } else {
        echo "[ERRO] Configuraçao 'xui' nao encontrada no cache!\n";
    }
} else {
    echo "Cache de config NAO encontrado (Laravel lera do .env a cada requisicao).\n";
}

echo "</pre>";
