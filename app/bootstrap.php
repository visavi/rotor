<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as DB;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR.'/app');
define('HOME', BASEDIR.'/public');
define('UPLOADS', HOME.'/uploads');
define('RESOURCES', BASEDIR.'/resources');
define('STORAGE', BASEDIR.'/storage');
define('SITETIME', time());
define('VERSION', '7.0');

require_once BASEDIR.'/vendor/autoload.php';

if (! env('APP_ENV')) {
    $dotenv = new Dotenv(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG')) {
    $whoops = new Run();

    if (Whoops\Util\Misc::isCommandLine()) {
        $whoops->pushHandler(new PlainTextHandler);
    } else {
        $whoops->pushHandler(new PrettyPageHandler);
    }

    $whoops->pushHandler(function() {
        $_SERVER = array_except($_SERVER, array_keys($_ENV));
        $_ENV    = [];
    });
    $whoops->register();
}

$db = new DB();

$db->addConnection([
    'driver'    => env('DB_DRIVER'),
    'port'      => env('DB_PORT'),
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
