<?php

return [
    'base_url' => env('XUI_BASE_URL', ''),
    
    'api_key' => env('XUI_API_KEY', ''),
    
    'timeout' => env('XUI_TIMEOUT', 30),
    
    'bouquet_blacklist' => array_map('intval', array_filter(explode(',', env('XUI_BOUQUET_BLACKLIST', '34,35,10')))),
    
    'streaming' => [
        'protocol' => env('XUI_STREAM_PROTOCOL', 'http'),
        'server' => env('XUI_STREAM_SERVER', ''),
        'port' => env('XUI_STREAM_PORT', '80'),
    ],
];
