<?php

return [
    'default' => 'monolog',
    'channels' => [
        'monolog' => [
            'path' => STORAGE . '/logs/rotor.log',
            'driver' => 'daily',
            'level' => 'debug',
        ],
    ],
];
