<?php

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR.'/app');
define('HOME', BASEDIR.'/public');
define('STORAGE', APP.'/storage');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', STORAGE.'/temp/');
define('VERSION', '6.1');

require_once BASEDIR.'/vendor/autoload.php';

if (! env('APP_ENV')) {
    $dotenv = new Dotenv\Dotenv(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG')) {
    $whoops = new Whoops\Run();
    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(function() {
        $_SERVER = array_except($_SERVER, array_keys($_ENV));
        $_ENV = [];
    });
    $whoops->register();
}

/*DBM::run()->config(
    env('DB_HOST'),
    env('DB_DATABASE'),
    env('DB_USERNAME'),
    env('DB_PASSWORD'),
    env('DB_PORT')
);*/

ORM::configure([
    'connection_string' => env('DB_DRIVER').':host='.env('DB_HOST').';dbname='.env('DB_DATABASE').';port='.env('DB_PORT'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'driver_options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'],
    //'return_result_sets' => true,
]);

ORM::configure('logging', true);
ORM::configure('logger', function($log_string, $query_time) {
    echo $log_string . ' in ' . $query_time.'<br />';
});


