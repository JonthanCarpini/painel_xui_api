#!/bin/bash
# Testar o endpoint resolve-stream dentro do container painel_20

echo "=== 1. Verificar credenciais ghost no DB ==="
echo "SELECT \`key\`, \`value\` FROM app_settings WHERE \`key\` LIKE 'ghost%';" | \
  docker exec -i mysql_central mysql -u user_20 -p28e460109527c46988b51f18 painel_20 2>/dev/null

echo ""
echo "=== 2. Verificar canal id=1 no DB ==="
echo "SELECT id, name, stream_id, stream_url FROM test_channels WHERE id=1 LIMIT 1;" | \
  docker exec -i mysql_central mysql -u user_20 -p28e460109527c46988b51f18 painel_20 2>/dev/null

echo ""
echo "=== 3. Testar resolve-stream via curl interno ==="
# Pegar cookie de sessão real fazendo login fake ou testar via artisan
docker exec painel_20 php artisan tinker --execute="
\$channel = \App\Models\TestChannel::find(1);
echo 'Channel: ' . (\$channel ? \$channel->name : 'NOT FOUND') . PHP_EOL;
echo 'Stream ID: ' . (\$channel->stream_id ?? 'null') . PHP_EOL;

\$creds = DB::table('app_settings')->where('key', 'ghost_reseller_username')->value('value');
echo 'Ghost user: ' . (\$creds ?: 'NOT FOUND') . PHP_EOL;

\$creds2 = DB::table('app_settings')->where('key', 'ghost_reseller_password')->value('value');
echo 'Ghost pass: ' . (\$creds2 ?: 'NOT FOUND') . PHP_EOL;

\$xuiIp = env('XUI_DB_HOST', '109.205.178.143');
echo 'XUI IP: ' . \$xuiIp . PHP_EOL;

// Simular o curl
\$url = 'http://' . \$xuiIp . '/' . \$creds . '/' . \$creds2 . '/' . \$channel->stream_id . '.m3u8';
echo 'XUI URL: ' . \$url . PHP_EOL;

\$ch = curl_init(\$url);
curl_setopt_array(\$ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_TIMEOUT => 10,
]);
\$response = curl_exec(\$ch);
\$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
curl_close(\$ch);

echo 'HTTP Code: ' . \$httpCode . PHP_EOL;

if (\$httpCode == 302 || \$httpCode == 301) {
    preg_match('/Location:\s*(.+)/i', \$response, \$m);
    \$location = trim(\$m[1] ?? '');
    echo 'Location: ' . \$location . PHP_EOL;
    \$path = parse_url(\$location, PHP_URL_PATH);
    echo 'Path: ' . \$path . PHP_EOL;
    echo 'Resolved URL: https://xui.p2player.vp1.officex.site' . \$path . PHP_EOL;
} else {
    echo 'Response headers: ' . substr(\$response, 0, 200) . PHP_EOL;
}
"
