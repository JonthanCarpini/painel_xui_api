<?php
require '/var/www/vendor/autoload.php';
$app = require '/var/www/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "ghost_user: " . App\Models\AppSetting::get('ghost_reseller_username') . PHP_EOL;
echo "ghost_pass: " . App\Models\AppSetting::get('ghost_reseller_password') . PHP_EOL;
echo "tmdb_key: " . App\Models\AppSetting::get('tmdb_api_key') . PHP_EOL;

// Testar se player_api funciona
$baseUrl = rtrim(config('xui.base_url', env('XUI_BASE_URL', '')), '/');
$parsed = parse_url($baseUrl);
$playerBase = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? '') . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

$user = App\Models\AppSetting::get('ghost_reseller_username');
$pass = App\Models\AppSetting::get('ghost_reseller_password');

echo "player_base: " . $playerBase . PHP_EOL;

if ($user && $pass) {
    $url = $playerBase . "/player_api.php?username={$user}&password={$pass}&action=get_vod_streams";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "player_api HTTP: " . $code . PHP_EOL;
    if ($resp) {
        $data = json_decode($resp, true);
        echo "vod_streams count: " . (is_array($data) ? count($data) : 'not array') . PHP_EOL;
        if (is_array($data) && count($data) > 0) {
            $first = $data[0];
            echo "first tmdb_id: " . ($first['tmdb_id'] ?? 'null') . PHP_EOL;
            echo "first name: " . ($first['name'] ?? 'null') . PHP_EOL;
        }
    }
} else {
    echo "NO GHOST CREDENTIALS" . PHP_EOL;
}
