<?php

/*
|--------------------------------------------------------------------------
| Запуск планировщика задач
|--------------------------------------------------------------------------
|
| В cron, раз в минуту: /path-to-site/cron.php
| Подходит и хостингам, где в cron указывается только путь к файлу.
| То же самое: php /path-to-site/artisan schedule:run
|
*/

// Не PHP_SAPI: часть хостингов запускает файл из cron через php-cgi.
// Веб-запрос отличает REQUEST_METHOD
if (isset($_SERVER['REQUEST_METHOD'])) {
    http_response_code(403);
    exit;
}

$_SERVER['argv'] = ['artisan', 'schedule:run'];
$_SERVER['argc'] = 2;

require __DIR__ . '/artisan';
