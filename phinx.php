<?php
return [
    'paths' => [
        'migrations' => APP.'/Database/Migrations',
        'seeds'      => APP.'/Database/Seeds',
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
