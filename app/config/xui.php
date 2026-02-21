<?php

return [
    'base_url' => env('XUI_BASE_URL', 'http://192.168.100.210/fXvFkkfq/'),
    
    'api_key' => env('XUI_API_KEY', '5EE3138A43E3190ED00F031B1107EA30'),
    
    'timeout' => env('XUI_TIMEOUT', 30),
    
    'bouquet_blacklist' => [34, 35, 10],
    
    'streaming' => [
        'protocol' => env('XUI_STREAM_PROTOCOL', 'http'),
        'server' => env('XUI_STREAM_SERVER', '192.168.100.210'),
        'port' => env('XUI_STREAM_PORT', '80'),
    ],
];
