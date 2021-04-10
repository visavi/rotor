<?php

declare(strict_types = 1);

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

define('STARTTIME', microtime(true));
define('BASEDIR', dirname(__DIR__));
define('SITETIME', time());
const APP = BASEDIR . '/app';
const HOME = BASEDIR . '/public';
const UPLOADS = HOME . '/uploads';
const RESOURCES = BASEDIR . '/resources';
const STORAGE = BASEDIR . '/storage';
const MODULES = BASEDIR . '/modules';
const VERSION = '9.2';

require_once BASEDIR . '/vendor/autoload.php';

if (config('app.debug')) {
    if (class_exists(Run::class)) {
        $handler = Misc::isCommandLine() ?
            new PlainTextHandler() :
            new PrettyPageHandler();

        $whoops = new Run();
        $whoops->prependHandler($handler);
        $whoops->pushHandler(static function () {
            $_SERVER = Arr::except($_SERVER, array_keys($_ENV));
            $_ENV    = [];
        });
        $whoops->register();
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }
}

date_default_timezone_set(config('app.timezone'));

/**
 * Setup a new app instance container
 */
$app = new Container();

$app->singleton('config', static function () {
    return new Repository(config());
});

$app->singleton('files', static function () {
    return new Filesystem();
});

$app->singleton('events', static function ($app) {
    return new Dispatcher($app);
});

$app->singleton('request', static function () {
    return request();
});

$app->singleton('translator', static function ($app) {
    $translator = new Translator(
        new FileLoader(
            $app['files'],
            RESOURCES . '/lang'
        ),
        setting('language')
    );

    $translator->setFallback(setting('language_fallback'));

    return $translator;
});

$app->singleton('view', static function ($app) {
    $resolver = new EngineResolver();

    $resolver->register('blade', static function () use ($app) {
        $blade = new BladeCompiler(
            $app['files'],
            STORAGE . '/views'
        );

        return new CompilerEngine($blade);
    });

    $resolver->register('php', static function () use ($app) {
        return new PhpEngine($app['files']);
    });

    $finder = new FileViewFinder(
        $app['files'],
        [
            HOME . '/themes/' . setting('themes') . '/views',
            RESOURCES . '/views',
            HOME . '/themes',
        ]
    );

    return new Factory($resolver, $finder, $app['events']);
});

$app->singleton('log', static function ($app) {
    return new LogManager($app);
});

if (config('cache.default') === 'redis') {
    $app->bind('redis', static function () use ($app) {
        return new RedisManager($app, 'phpredis', config('database.redis'));
    });
}

if (config('cache.default') === 'memcached') {
    $app->bind('memcached.connector', static function () {
        return new MemcachedConnector();
    });
}

$cacheManager = new CacheManager($app);
$app->instance('cache', $cacheManager);

$pagination = new PaginationServiceProvider($app);
$pagination->register();

$db = new DB();
$db->addConnection(config('database.connections.' . config('database.default')));
$db->setAsGlobal();
$db->bootEloquent();
$db::connection()->enableQueryLog();

/**
 * Set $app as FacadeApplication handler
 */
Facade::setFacadeApplication($app);
