<?php

/*if (env('APP_ENV') === 'testing') {
    $migrations = BASEDIR . '/database/{migrations,upgrades}';
} else {*/
    $migrations = BASEDIR . '/database/' . (env('APP_NEW') ? 'migrations' : 'upgrades');
//}

return [
    'paths' => [
        'migrations' => $migrations,
        'seeds'      => BASEDIR . '/database/seeds',
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
