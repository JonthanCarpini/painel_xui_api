#!/bin/bash
# Testar curl com FOLLOWLOCATION para ver effective_url
docker exec painel_20 php artisan tinker --execute="
\$url = 'http://109.205.178.143/testeVIDEO/TESTEcanal2/2.m3u8';

\$ch = curl_init(\$url);
curl_setopt_array(\$ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => false,
    CURLOPT_TIMEOUT => 10,
]);
\$body = curl_exec(\$ch);
\$httpCode = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
\$effectiveUrl = curl_getinfo(\$ch, CURLINFO_EFFECTIVE_URL);
\$redirectCount = curl_getinfo(\$ch, CURLINFO_REDIRECT_COUNT);
curl_close(\$ch);

echo 'HTTP Code: ' . \$httpCode . PHP_EOL;
echo 'Effective URL: ' . \$effectiveUrl . PHP_EOL;
echo 'Redirect Count: ' . \$redirectCount . PHP_EOL;
echo 'Body length: ' . strlen(\$body) . PHP_EOL;
echo 'Body (first 300): ' . substr(\$body, 0, 300) . PHP_EOL;
"
