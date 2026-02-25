#!/bin/bash
echo "=== Testar resolve-stream via artisan tinker ==="
docker exec painel_20 php artisan tinker --execute="
\$req = Request::create('/channel-test/resolve-stream', 'GET', ['channel_id' => 1, 'type' => 'live']);
\$controller = app(\App\Http\Controllers\ChannelTestController::class);
\$response = \$controller->resolveStreamUrl(\$req);
echo \$response->getContent();
"
