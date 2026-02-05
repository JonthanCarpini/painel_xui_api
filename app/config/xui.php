<?php

return [
    'base_url' => env('XUI_BASE_URL', 'http://192.168.100.210/XIkpMBHH/'),
    
    'api_key' => env('XUI_API_KEY', '6EA0E44987AD73B0804CB0D46D2A9159'),
    
    'timeout' => env('XUI_TIMEOUT', 30),
    
    'bouquet_blacklist' => [34, 35, 10],
    
    'streaming' => [
        'protocol' => env('XUI_STREAM_PROTOCOL', 'http'),
        'server' => env('XUI_STREAM_SERVER', '192.168.100.210'),
        'port' => env('XUI_STREAM_PORT', '80'),
    ],
];
