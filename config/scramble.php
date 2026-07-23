<?php

return [
    'cache' => [
        'key' => 'scramble.openapi',
        'store' => 'file',
    ],
    'info' => [
        'version' => env('API_VERSION', '1.0'),
    ],
];
