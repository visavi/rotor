<?php
return [
    'paths' => [
        'migrations' => [
            BASEDIR . '/database/migrations',
            BASEDIR . '/database/upgrades',
        ],
        'seeds' => BASEDIR.'/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database'        => 'default',
        'default' => [
            'adapter'   => env('DB_DRIVER'),
            'charset'   => env('DB_CHARSET'),
            'collation' => env('DB_COLLATION'),
            'port'      => env('DB_PORT'),
            'host'      => env('DB_HOST'),
            'name'      => env('DB_DATABASE'),
            'user'      => env('DB_USERNAME'),
            'pass'      => env('DB_PASSWORD'),
        ]
    ]
];
