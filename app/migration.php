<?php

declare(strict_types = 1);

$database   = config('database.connections.' . config('database.default'));
$migrations = BASEDIR . '/database/' . (setting('app_installed') ? 'upgrades' : 'migrations');

return [
    'paths' => [
        'migrations' => $migrations,
        'seeds'      => BASEDIR . '/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database'        => 'default',
        'default' => [
            'adapter'      => $database['driver'],
            'charset'      => $database['charset'],
            'collation'    => $database['collation'],
            'port'         => $database['port'],
            'host'         => $database['host'],
            'name'         => $database['database'],
            'user'         => $database['username'],
            'pass'         => $database['password'],
            'table_prefix' => $database['prefix'],
        ]
    ]
];
