<?php

declare(strict_types = 1);

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Redis\RedisManager;
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
define('APP', BASEDIR . '/app');
define('HOME', BASEDIR . '/public');
define('UPLOADS', HOME . '/uploads');
define('RESOURCES', BASEDIR . '/resources');
define('STORAGE', BASEDIR . '/storage');
define('MODULES', BASEDIR . '/modules');
define('SITETIME', time());
define('VERSION', '8.5');

require_once BASEDIR . '/vendor/autoload.php';

if (config('APP_DEBUG') && class_exists(Run::class)) {
    $handler = Misc::isCommandLine() ?
        new PlainTextHandler() :
        new PrettyPageHandler();

    $whoops = new Run();
    $whoops->prependHandler($handler);
    $whoops->register();
}

$db = new DB();
$db->addConnection([
    'driver'    => config('DB_DRIVER'),
    'port'      => config('DB_PORT'),
    'host'      => config('DB_HOST'),
    'database'  => config('DB_DATABASE'),
    'username'  => config('DB_USERNAME'),
    'password'  => config('DB_PASSWORD'),
    'charset'   => config('DB_CHARSET'),
    'collation' => config('DB_COLLATION'),
    'prefix'    => config('DB_PREFIX'),
]);

$db->setAsGlobal();
$db->bootEloquent();
$db::connection()->enableQueryLog();

/**
 * Setup a new app instance container
 */
$app = new Container();

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

$app->singleton('config', static function () {
    return [
        'cache.default' => config('CACHE_DRIVER', 'file'),
        'cache.prefix' => '',
        'cache.stores.file' => [
            'driver'     => 'file',
            'path'       => STORAGE . '/caches',
        ],
        'cache.stores.redis' => [
            'driver'     => 'redis',
            'connection' => 'default'
        ],
        'database.redis' => [
            'cluster' => false,
            'default' => [
                'host'     => config('REDIS_HOST', '127.0.0.1'),
                'password' => config('REDIS_PASSWORD'),
                'port'     => config('REDIS_PORT', 6379),
                'database' => 0,
            ],
        ],
        'cache.stores.memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host'   => config('MEMCACHED_HOST', '127.0.0.1'),
                    'port'   => config('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'logging.default' => 'monolog',
        'logging.channels.monolog' => [
            'path' => STORAGE . '/logs/rotor.log',
            'driver' => 'daily',
            'level' => 'debug',
        ],
    ];
});

$app->singleton('log', static function ($app) {
    return new LogManager($app);
});

if (config('CACHE_DRIVER') === 'redis') {
    $app->bind('redis', static function () use ($app) {
        return new RedisManager($app, 'phpredis', $app['config']['database.redis']);
    });
}

if (config('CACHE_DRIVER') === 'memcached') {
    $app->bind('memcached.connector', static function () {
        return new MemcachedConnector();
    });
}

$cacheManager = new CacheManager($app);
$app->instance('cache', $cacheManager);

$pagination = new PaginationServiceProvider($app);
$pagination->register();

/**
 * Set $app as FacadeApplication handler
 */
Facade::setFacadeApplication($app);
