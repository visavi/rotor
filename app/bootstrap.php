<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as DB;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR.'/app');
define('HOME', BASEDIR.'/public');
define('RESOURCES', BASEDIR.'/resources');
define('STORAGE', RESOURCES.'/storage');
define('PCLZIP_TEMPORARY_DIR', STORAGE.'/temp/');
define('SITETIME', time());
define('VERSION', '7.0');

require_once BASEDIR.'/vendor/autoload.php';

if (! env('APP_ENV')) {
    $dotenv = new Dotenv(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG')) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->pushHandler(function() {
        $_SERVER = array_except($_SERVER, array_keys($_ENV));
        $_ENV    = [];
    });
    $whoops->register();
}

$db = new DB();

$db->addConnection([
    'driver'    => env('DB_DRIVER'),
    'host'      => env('DB_HOST'),
    'database'  => env('DB_DATABASE'),
    'username'  => env('DB_USERNAME'),
    'password'  => env('DB_PASSWORD'),
    'charset'   => env('DB_CHARSET'),
    'collation' => env('DB_COLLATION'),
]);

/*use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$db->setEventDispatcher(new Dispatcher(new Container));*/
$db->setAsGlobal();
$db->bootEloquent();
$db::connection()->enableQueryLog();
