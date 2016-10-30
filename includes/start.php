<?php

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(__DIR__));
define('DATADIR', BASEDIR.'/storage');
define('HOME', BASEDIR.'/public');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', BASEDIR.'/local/temp/');

session_name('SID');
session_start();

include_once BASEDIR.'/vendor/autoload.php';
include_once BASEDIR.'/includes/functions.php';

if (! env('APP_ENV')) {
    $dotenv = new Dotenv\Dotenv(BASEDIR);
    $dotenv->load();
}

if (env('APP_DEBUG')) {
    $whoops = new Whoops\Run;
    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(function() {
        $_SERVER = array_except($_SERVER, array_keys($_ENV));
        $_ENV = [];
    });
    $whoops->register();
}

// -------- Автозагрузка классов ---------- //
function autoloader($class) {

	$class = str_replace('\\', '/', $class);
	if (file_exists(BASEDIR.'/includes/classes/'.$class.'.php')) {
		include_once BASEDIR.'/includes/classes/'.$class.'.php';
	}
}

spl_autoload_register('autoloader');

include_once BASEDIR.'/includes/routes.php';

DBM::run()->config(env('DB_HOST'), env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_PORT'));

if (!file_exists(DATADIR.'/temp/setting.dat')) {
	$queryset = DB::run() -> query("SELECT `setting_name`, `setting_value` FROM `setting`;");
	$config = $queryset -> fetchAssoc();
	file_put_contents(DATADIR.'/temp/setting.dat', serialize($config), LOCK_EX);
}
$config = unserialize(file_get_contents(DATADIR.'/temp/setting.dat'));

date_default_timezone_set($config['timezone']);

include_once BASEDIR.'/includes/header.php';
