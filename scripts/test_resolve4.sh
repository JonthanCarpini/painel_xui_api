#!/bin/bash
echo "=== HEAD vs GET - comparar redirect behavior ==="
docker exec painel_20 php artisan tinker --execute="
\$url = 'http://109.205.178.143/testeVIDEO/TESTEcanal2/2.m3u8';

// HEAD com FOLLOWLOCATION
\$ch = curl_init(\$url);
curl_setopt_array(\$ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_NOBODY => true,
    CURLOPT_TIMEOUT => 10,
]);
curl_exec(\$ch);
echo 'HEAD - Effective: ' . curl_getinfo(\$ch, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
echo 'HEAD - Redirects: ' . curl_getinfo(\$ch, CURLINFO_REDIRECT_COUNT) . PHP_EOL;
echo 'HEAD - HTTP: ' . curl_getinfo(\$ch, CURLINFO_HTTP_CODE) . PHP_EOL;
curl_close(\$ch);

// GET com FOLLOWLOCATION
\$ch = curl_init(\$url);
curl_setopt_array(\$ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
]);
curl_exec(\$ch);
echo 'GET  - Effective: ' . curl_getinfo(\$ch, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
echo 'GET  - Redirects: ' . curl_getinfo(\$ch, CURLINFO_REDIRECT_COUNT) . PHP_EOL;
echo 'GET  - HTTP: ' . curl_getinfo(\$ch, CURLINFO_HTTP_CODE) . PHP_EOL;
curl_close(\$ch);
"
