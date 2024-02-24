<?php

return [
    'enabled' => env('FFMPEG_ENABLED'),
    'path'    => env('FFMPEG_PATH'),
    'timeout' => env('FFMPEG_TIMEOUT'),
    'threads' => env('FFMPEG_THREADS'),
    'ffprobe' => [
        'path' => env('FFPROBE_PATH'),
    ],
];
