<?php

return [
    'default' => env('CACHE_DRIVER'),
    'prefix' => null,

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path'   => STORAGE . '/caches',
        ],

        'redis' => [
            'driver'     => 'redis',
            'connection' => 'default',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host'   => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port'   => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
    ],
];
