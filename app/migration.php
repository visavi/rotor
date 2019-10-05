<?php

/*if (config('APP_ENV') === 'testing') {
    $migrations = BASEDIR . '/database/{migrations,upgrades}';
} else {*/
    $migrations = BASEDIR . '/database/' . (config('APP_NEW') ? 'migrations' : 'upgrades');
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
            'adapter'   => config('DB_DRIVER'),
            'charset'   => config('DB_CHARSET'),
            'collation' => config('DB_COLLATION'),
            'port'      => config('DB_PORT'),
            'host'      => config('DB_HOST'),
            'name'      => config('DB_DATABASE'),
            'user'      => config('DB_USERNAME'),
            'pass'      => config('DB_PASSWORD'),
        ]
    ]
];
