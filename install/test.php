<?php
#-----------------------------------------------------#
#          ********* ROTORCMS *********               #
#              Made by  :  VANTUZ                     #
#               E-mail  :  visavi.net@mail.ru         #
#                 Site  :  http://pizdec.ru           #
#             WAP-Site  :  http://visavi.net          #
#                  ICQ  :  36-44-66                   #
#  Вы не имеете право вносить изменения в код скрипта #
#        для его дальнейшего распространения          #
#-----------------------------------------------------#
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('html_errors', true);
ini_set('error_reporting', E_ALL);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>RotorCMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
body {
	margin: 0px;
	padding: 0px;
	font-family: verdana, arial, helvetica, sans-serif;
	color: black;
	background-color: white;
	text-align: center;
	font-size:11px;
	}

#main {
	width: 600px; 
	padding: 15px;
	margin-top: 20px;
	margin-bottom: 20px;
	margin-right: auto;
	margin-left: auto; 	
	background: #eff5fb;
	border: 1px groove #333;
	text-align:left; 
	}

.text {
	font-family:verdana, sans-serif;
	font-size:11px;
	margin-left:20px;
	margin-right:20px;
	line-height:140%;
	text-align:justify;
    color: #858585;
}
</style>
</head>

<body>

<div id="main">
    <div align="center"><h1>RotorCMS</h1></div>
<div class="text">
<?php 
// =====================================================================//
function parsePHPModules() {
	ob_start();
	phpinfo(INFO_MODULES);
	$s = ob_get_contents();
	ob_end_clean();

	$s = strip_tags($s, '<h2><th><td>');
	$s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s);
	$s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s);
	$vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
	$vModules = array();
	for ($i = 1;$i < count($vTmp);$i++) {
		if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $vTmp[$i], $vMat)) {
			$vName = trim($vMat[1]);
			$vTmp2 = explode("\n", $vTmp[$i + 1]);
			foreach ($vTmp2 AS $vOne) {
				$vPat = '<info>([^<]+)<\/info>';
				$vPat3 = "/$vPat\s*$vPat\s*$vPat/";
				$vPat2 = "/$vPat\s*$vPat/";
				if (preg_match($vPat3, $vOne, $vMat)) {
					$vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]), trim($vMat[3]));
				} elseif (preg_match($vPat2, $vOne, $vMat)) {
					$vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
				} 
			} 
		} 
	} 
	return $vModules;
} 

function getModuleSetting($pModuleName, $pSetting) {
	$vModules = parsePHPModules();

	if (!empty($vModules[$pModuleName][$pSetting])) {
		return $vModules[$pModuleName][$pSetting];
	} 
} 
// --------------------------------------------------//
$error_setting = 0;
echo '<b>Рекомендуемая версия PHP - 5.4.0</b><br /><br />';

echo 'Версия PHP 5.2.1 и выше: ';
if (version_compare(PHP_VERSION, '5.2.1') > 0) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Версия ' . phpversion() . ')<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b>  (Версия ' . phpversion() . ')<br />';
	$error_critical = 1;
} 

echo 'Расширение PDO-MySQL: ';
if (extension_loaded('pdo_mysql')) {
	if (getModuleSetting('pdo_mysql', 'Client API version') != "") {
		$pdoversion = strtok(getModuleSetting('pdo_mysql', 'Client API version'), '-');
	} elseif (getModuleSetting('pdo_mysql', 'PDO Driver for MySQL, client library version') != "") {
		$pdoversion = getModuleSetting('pdo_mysql', 'PDO Driver for MySQL, client library version');
	} else {
		$pdoversion = 'Не определено';
	} 

	echo '<b><span style="color:#00cc00">ОК</span></b> (Версия ' . $pdoversion . ')<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
	$error_critical = 1;
} 

echo 'Библиотека GD: ';
if (extension_loaded('gd')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Версия ' . getModuleSetting('gd', 'GD Version') . ')<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Библиотека не загружена)<br />';
	$error_setting++;
} 

echo 'Библиотека Zlib: ';
if (extension_loaded('zlib')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Версия ' . getModuleSetting('zlib', 'Compiled Version') . ')<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Библиотека не загружена)<br />';
	$error_setting++;
} 

echo 'Safe Mode: ';
if (!ini_get('safe_mode')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Включено)<br />';
	$error_setting++;
} 

echo 'Буферизация вывода: ';
if (!ini_get('output_buffering')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Включено)<br />';
	$error_setting++;
} 

echo 'Magic Quotes Runtime: ';
if (!ini_get('magic_quotes_runtime')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Включено)<br />';
	$error_setting++;
} 

echo 'Session auto start: ';
if (!ini_get('session.auto_start')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Включено)<br />';
	$error_setting++;
} 

echo 'Register Globals: ';
if (!ini_get('register_globals')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Выключено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Включено)<br />';
	$error_setting++;
} 

echo 'Загрузка файлов: ';
if (ini_get('file_uploads')) {
	echo '<b><span style="color:#00cc00">ОК</span></b> (Включено)<br />';
} else {
	echo '<b><span style="color:#ff0000">Ошибка</span></b> (Выключено)<br />';
	$error_setting++;
} 

echo '<br />';

if (empty($error_critical)) {
	echo '<b><span style="color:#00cc00">Поздравляем! Вы можете уставновить движок на ваш сайт!</span></b><br /><br />';

	if (empty($error_setting)) {
		echo 'Все модули и библиотеки присутствуют, настройки корректны<br /><br />';
	} else {
		echo '<b><span style="color:#ff0000">У вас имеются ошибки!</span></b> (Всего ошибок: ' . $error_setting . ')<br />';
		echo 'Данные ошибки не являются критическими, но тем не менее для стабильной и безопасной работы желательно их устранить<br />';
		echo 'Вы можете установить скрипт на свой сайт, но нет никаких гарантий, что движок будет работать стабильно<br /><br />';
	} 
} else {
	echo '<b><span style="color:#ff0000">Имеются критические ошибки!</span></b><br />';
	echo 'Вы не сможете установить движок на свой сайт, так как у вас не установлена библиотека PDO или вы используете устаревшую версию PHP<br /><br />';
} 

echo '<p style="text-align:center">';
echo '<a href="http://visavi.net">Powered by RotorCMS</a><br />';
echo '</p>';

?>

</div></div></body></html>
