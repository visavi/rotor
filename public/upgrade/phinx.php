<?php
return [
    'paths' => [
        'migrations' => 'migrations',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'default',
        'default' => [
            'adapter' => 'mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'pass' => $_ENV['DB_PASSWORD'],
        ]
    ]
];
