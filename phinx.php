<?php
return [
    'paths' => [
        'migrations' => APP.'/database/migrations',
        'seeds' => APP.'/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'default',
        'default' => [
            'adapter' => 'mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'host' => env('DB_HOST'),
            'name' => env('DB_DATABASE'),
            'user' => env('DB_USERNAME'),
            'pass' => env('DB_PASSWORD'),
        ]
    ]
];
