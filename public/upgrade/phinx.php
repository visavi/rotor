<?php
return [
    'paths' => [
        'migrations' => 'migrations',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'default',
        'default' => [
            'adapter'   => $_ENV['DB_DRIVER'],
            'charset'   => $_ENV['DB_CHARSET'],
            'collation' => $_ENV['DB_COLLATION'],
            'port'      => $_ENV['DB_PORT'],
            'host'      => $_ENV['DB_HOST'],
            'name'      => $_ENV['DB_DATABASE'],
            'user'      => $_ENV['DB_USERNAME'],
            'pass'      => $_ENV['DB_PASSWORD'],
        ]
    ]
];
