<?php

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('APP', BASEDIR.'/app');
define('HOME', BASEDIR.'/public');
define('STORAGE', APP.'/storage');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', STORAGE.'/temp/');
define('VERSION', '6.0.0');

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

DBM::run()->config(env('DB_HOST'), env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_PORT'));

if (!file_exists(STORAGE.'/temp/setting.dat')) {
	$queryset = DB::run() -> query("SELECT `name`, `value` FROM `setting`;");
	$config = $queryset -> fetchAssoc();
	file_put_contents(STORAGE.'/temp/setting.dat', serialize($config), LOCK_EX);
}
$config = unserialize(file_get_contents(STORAGE.'/temp/setting.dat'));

date_default_timezone_set($config['timezone']);
