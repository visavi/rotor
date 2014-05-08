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

//@ini_set('session.save_path', dirname($_SERVER['DOCUMENT_ROOT']).'/tmp');
session_name('SID');
session_start();

date_default_timezone_set('Europe/Moscow');

define('STARTTIME', microtime(1));

if (version_compare(PHP_VERSION, '5.2.1') < 0) {
	die('Ошибка! Версия PHP должна быть 5.2.1 или выше!');
}

define('BASEDIR', dirname(dirname(__FILE__)));
define('DATADIR', BASEDIR.'/local');

// ---------------------------- Класс для работы с базами данных -------------------------------//
class PDO_ extends PDO {
	static $counter = 0;

	function __construct($dsn, $username, $password) {
		parent::__construct($dsn, $username, $password);
		$this -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$this -> setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

	function prepare($sql, $params = array()) {
		$stmt = parent::prepare($sql, array(
				PDO::ATTR_STATEMENT_CLASS => array('PDOStatement_')
				));
		return $stmt;
	}

	function query($sql, $params = array()) {
		self::$counter++;
		$stmt = $this -> prepare($sql);
		$stmt -> execute($params);
		return $stmt;
	}

	function querySingle($sql, $params = array()) {
		$stmt = $this -> query($sql, $params);
		return $stmt -> fetchColumn(0);
	}

	function queryFetch($sql, $params = array()) {
		$stmt = $this -> query($sql, $params);
		return $stmt -> fetch();
	}

	function queryCounter() {
		return self::$counter;
	}
}
// ----------------------------------------------------//
class PDOStatement_ extends PDOStatement {
	function execute($params = array()) {
		if (func_num_args() == 1) {
			$params = func_get_arg(0);
		} else {
			$params = func_get_args();
		}
		if (!is_array($params)) {
			$params = array($params);
		}
		parent::execute($params);
		return $this;
	}

	function fetchSingle() {
		return $this -> fetchColumn(0);
	}

	function fetchAssoc() {
		$this -> setFetchMode(PDO::FETCH_NUM);
		$data = array();
		while ($row = $this -> fetch()) {
			$data[$row[0]] = $row[1];
		}
		return $data;
	}
}

include_once (BASEDIR.'/includes/connect.php');

// --------------- Класс singleton для подключения к БД -----------------//
class DB {
	private static $instance;
	private function __construct() {}
	private function __clone() {}

	public static function run() {

		if (!isset(self::$instance)) {

			try {
				self::$instance = new PDO_('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DBNAME, DBUSER, DBPASS);
				self::$instance -> exec('SET CHARACTER SET utf8');
				self::$instance -> exec('SET NAMES utf8');
			}

			catch (PDOException $e) {
				if (file_exists(BASEDIR.'/install/index.php')) {
					header ('Location: /install/index.php');
					exit;
				}
				die('Connection failed: '.$e -> getMessage());
			}
		}
		return self::$instance;
	}

	final public function __destruct() {
		self::$instance = null;
	}

}

if (file_exists(DATADIR.'/temp/setting.dat')) {
	$config = unserialize(file_get_contents(DATADIR.'/temp/setting.dat'));
} else {
	$queryset = DB::run() -> query("SELECT `setting_name`, `setting_value` FROM `setting`;");
	$config = $queryset -> fetchAssoc();
	file_put_contents(DATADIR.'/temp/setting.dat', serialize($config), LOCK_EX);
}

define('SITETIME', time() + $config['timezone'] * 3600); # Установка временного сдвига сайта

// -------- Класс валидации данных ---------- //
require_once (BASEDIR.'/includes/validation.php');
?>
