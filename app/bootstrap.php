<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\FileLoader;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View;
use Jenssegers\Blade\Blade;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

define('STARTTIME', microtime(true));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR . '/app');
define('HOME', BASEDIR . '/public');
define('UPLOADS', HOME . '/uploads');
define('RESOURCES', BASEDIR . '/resources');
define('STORAGE', BASEDIR . '/storage');
define('MODULES', BASEDIR . '/modules');
define('SITETIME', time());
define('VERSION', '7.5');

require_once BASEDIR . '/vendor/autoload.php';

if (! env('APP_ENV')) {
    $dotenv = Dotenv::create(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG') && class_exists(Run::class)) {
    $whoops = new Run();

    if (Whoops\Util\Misc::isCommandLine()) {
        $whoops->pushHandler(new PlainTextHandler);
    } else {
        $whoops->pushHandler(new PrettyPageHandler);
    }

    $whoops->pushHandler(static function() {
        $_SERVER = Arr::except($_SERVER, array_keys($_ENV));
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


use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\Translator;
/**
 * Setup a new app instance container
 *
 * @var Illuminate\Container\Container
 */
$app = new Container();
$app->singleton('app', Container::class);


$app->singleton('view', static function () {

    // echo $this->viewPath;
    /*if(isset($viewPath)) {
        $this->viewPath = $viewPath;
    }*/

    // this path needs to be array
    $FileViewFinder = new FileViewFinder(
        new Filesystem,
        [
            HOME . '/themes/' . setting('themes') . '/views',
            RESOURCES . '/views',
            HOME . '/themes',
        ]
    );

    // use blade instead of phpengine
    // pass in filesystem object and cache path
    $compiler = new BladeCompiler(new Filesystem(), __DIR__.'/../app/storage/views');
    $BladeEngine = new CompilerEngine($compiler);

    // create a dispatcher
    $dispatcher = new Dispatcher(new Container);

    // build the factory
    $factory = new Factory(
        new EngineResolver,
        $FileViewFinder,
        $dispatcher
    );

    // this path needs to be string
    $viewObj = new View(
        $factory,
        $BladeEngine,
        'index',
        [
            HOME . '/themes/' . setting('themes') . '/views',
            RESOURCES . '/views',
            HOME . '/themes',
        ],
        []
    );

    return $viewObj;


/*
    $view = new Blade([
        HOME . '/themes/' . setting('themes') . '/views',
        RESOURCES . '/views',
        HOME . '/themes',
    ], STORAGE . '/caches');

    $view->compiler()->withoutDoubleEncoding();

    return $view;*/
});

$app->singleton('translator', static function () {
    $translator = new Translator(
        new FileLoader(
            new Filesystem(),
            RESOURCES . '/lang'
        ),
        setting('language')
    );

    $translator->setFallback('ru');

    return $translator;
});
/**
 * Set $app as FacadeApplication handler
 */
Facade::setFacadeApplication($app);


