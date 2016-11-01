<?php
return [
    'paths' => [
        'migrations' => 'app/database/migrations',
        'seeds' => 'app/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'default',
        'default' => [
            'adapter' => 'mysql',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'host' => $_ENV['DB_HOST'],
            'name' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'pass' => $_ENV['DB_PASSWORD'],
        ]
    ]
];
