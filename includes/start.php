<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
$debugmode = 1;

if ($debugmode) {
	@error_reporting(E_ALL);
	@ini_set('display_errors', true);
	@ini_set('html_errors', true);
	@ini_set('error_reporting', E_ALL);
} else {
	@error_reporting(E_ALL ^ E_NOTICE);
	@ini_set('display_errors', false);
	@ini_set('html_errors', false);
	@ini_set('error_reporting', E_ALL ^ E_NOTICE);
}

define('STARTTIME', microtime(1));
define('BASEDIR', dirname(dirname(__FILE__)));
define('DATADIR', BASEDIR.'/local');
define('SITETIME', time());
define('PCLZIP_TEMPORARY_DIR', BASEDIR.'/local/temp/');

//@ini_set('session.save_path', dirname(BASEDIR).'/tmp');
session_name('SID');
session_start();

include_once (BASEDIR.'/includes/connect.php');

// -------- Автозагрузка классов ---------- //
function autoloader($class) {
	include_once BASEDIR.'/includes/classes/'.$class.'.php';
}

spl_autoload_register('autoloader');

if (!file_exists(DATADIR.'/temp/setting.dat')) {
	$queryset = DB::run() -> query("SELECT `setting_name`, `setting_value` FROM `setting`;");
	$config = $queryset -> fetchAssoc();
	file_put_contents(DATADIR.'/temp/setting.dat', serialize($config), LOCK_EX);
}
$config = unserialize(file_get_contents(DATADIR.'/temp/setting.dat'));

date_default_timezone_set($config['timezone']);
?>
