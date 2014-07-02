<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
	exit(header('Location: /index.php'));
}

$ip = real_ip();
$php_self = (isset($_SERVER['PHP_SELF'])) ? check($_SERVER['PHP_SELF']) : '';
$request_uri = (isset($_SERVER['REQUEST_URI'])) ? check(urldecode($_SERVER['REQUEST_URI'])) : '/index.php';
$http_referer = (isset($_SERVER['HTTP_REFERER'])) ? check(urldecode($_SERVER['HTTP_REFERER'])) : 'Не определено';
$username = (empty($_SESSION['log'])) ? $config['guestsuser'] : $_SESSION['log'];
$brow = (empty($_SESSION['brow'])) ? $_SESSION['brow'] = get_user_agent() : $_SESSION['brow'];
############################################################################################
##                                 Проверка на ip-бан                                     ##
############################################################################################
if (file_exists(DATADIR.'/temp/ipban.dat')) {
	$arrbanip = unserialize(file_get_contents(DATADIR.'/temp/ipban.dat'));
} else {
	$arrbanip = save_ipban();
}

if (is_array($arrbanip) && count($arrbanip) > 0) {
	foreach($arrbanip as $ipdata) {
		$ipmatch = 0;
		$ipsplit = explode('.', $ip);
		$dbsplit = explode('.', $ipdata);

		for($i = 0; $i < 4; $i++) {
			if ($ipsplit[$i] == $dbsplit[$i] || $dbsplit[$i] == '*') {
				$ipmatch += 1;
			}
		}

		if ($ipmatch == 4) {
			redirect('/pages/banip.php');
		} //бан по IP
	}
}
############################################################################################
##                                 Счетчик запросов                                       ##
############################################################################################
if (!empty($config['doslimit'])) {
	if (is_writeable(DATADIR.'/antidos')) {
		$dosfiles = glob(DATADIR.'/antidos/*.dat');
		foreach ($dosfiles as $filename) {
			$array_filemtime = @filemtime($filename);
			if ($array_filemtime < (time() - 60)) {
				@unlink($filename);
			}
		}
		// -------------------------- Проверка на время -----------------------------//
		if (file_exists(DATADIR.'/antidos/'.$ip.'.dat')) {
			$file_dos = file(DATADIR.'/antidos/'.$ip.'.dat');
			$file_str = explode('|', $file_dos[0]);
			if ($file_str[0] < (time() - 60)) {
				@unlink(DATADIR.'/antidos/'.$ip.'.dat');
			}
		}
		// ------------------------------ Запись логов -------------------------------//
		$write = time().'|'.$request_uri.'|'.$http_referer.'|'.$brow.'|'.$username.'|';
		write_files(DATADIR.'/antidos/'.$ip.'.dat', $write."\r\n", 0, 0666);
		// ----------------------- Автоматическая блокировка ------------------------//
		if (counter_string(DATADIR.'/antidos/'.$ip.'.dat') > $config['doslimit']) {

			if (!empty($config['errorlog'])){
				$banip = DB::run() -> querySingle("SELECT `ban_id` FROM `ban` WHERE `ban_ip`=? LIMIT 1;", array($ip));
				if (empty($banip)) {
					DB::run() -> query("INSERT INTO `error` (`error_num`, `error_request`, `error_referer`, `error_username`, `error_ip`, `error_brow`, `error_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", array(666, $request_uri, $http_referer, $username, $ip, $brow, SITETIME));

					DB::run() -> query("INSERT IGNORE INTO ban (`ban_ip`, `ban_time`) VALUES (?, ?);", array($ip, SITETIME));
					save_ipban();
				}
			}

			unlink(DATADIR.'/antidos/'.$ip.'.dat');
		}
	}
}
############################################################################################
##                            Сжатие и буферизация данныx                                 ##
############################################################################################
if (!empty($config['gzip'])) {
	Compressor::start();
}

############################################################################################
##                               Авторизация по cookies                                   ##
############################################################################################
if (empty($_SESSION['log']) && empty($_SESSION['par'])) {
	if (isset($_COOKIE['cooklog']) && isset($_COOKIE['cookpar'])) {
		$unlog = check($_COOKIE['cooklog']);
		$unpar = check($_COOKIE['cookpar']);

		$checkuser = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($unlog));

		if (!empty($checkuser)) {
			if ($unlog == $checkuser['users_login'] && $unpar == md5($checkuser['users_pass'].$config['keypass'])) {
				session_regenerate_id(1);

				$_SESSION['my_ip'] = $ip;
				$_SESSION['log'] = $unlog;
				$_SESSION['par'] = md5($config['keypass'].$checkuser['users_pass']);

				$authorization = DB::run() -> querySingle("SELECT `login_id` FROM `login` WHERE `login_user`=? AND `login_time`>? LIMIT 1;", array($unlog, SITETIME-30));

				if (empty($authorization)) {
					DB::run() -> query("INSERT INTO `login` (`login_user`, `login_ip`, `login_brow`, `login_time`) VALUES (?, ?, ?, ?);", array($unlog, $ip, $brow, SITETIME));
					DB::run() -> query("DELETE FROM `login` WHERE `login_user`=? AND `login_time` < (SELECT MIN(`login_time`) FROM (SELECT `login_time` FROM `login` WHERE `login_user`=? ORDER BY `login_time` DESC LIMIT 50) AS del);", array($unlog, $unlog));
				}

				DB::run() -> query("UPDATE `users` SET `users_visits`=`users_visits`+1, `users_timelastlogin`=? WHERE `users_login`=? LIMIT 1;", array(SITETIME, $unlog));
			}
		}
	}
}

// ---------------------- Установка сессионных переменных -----------------------//
$log = '';
if (empty($_SESSION['counton'])) {
	$_SESSION['counton'] = 0;
}
if (empty($_SESSION['currs'])) {
	$_SESSION['currs'] = SITETIME;
}
if (!isset($_SESSION['token'])) {
	if (!empty($config['session'])){
		$_SESSION['token'] = generate_password(6);
	} else {
		$_SESSION['token'] = 0;
	}
}
ob_start('mc');
ob_start('ob_processing');
$_SESSION['timeon'] = maketime(SITETIME - $_SESSION['currs']);
############################################################################################
##                                     Авторизация                                        ##
############################################################################################
if ($udata = is_user()) {

	$log = $udata['users_login'];
	// ---------------------- Переопределение глобальных настроек -------------------------//
	$config['themes']     = $udata['users_themes'];      # Скин/тема по умолчанию
	$config['bookpost']   = $udata['users_postguest'];   # Вывод сообщений в гостевой
	$config['postnews']   = $udata['users_postnews'];    # Новостей на страницу
	$config['forumpost']  = $udata['users_postforum'];   # Вывод сообщений в форуме
	$config['forumtem']   = $udata['users_themesforum']; # Вывод тем в форуме
	$config['boardspost'] = $udata['users_postboard'];   # Вывод объявлений
	$config['privatpost'] = $udata['users_postprivat'];  # Вывод писем в привате
	$config['navigation'] = $udata['users_navigation'];  # Быстрый переход

	if ($udata['users_ban'] == 1) {
		if (!strsearch($php_self, array('/pages/ban.php', '/pages/rules.php'))) {
			redirect('/pages/ban.php?log='.$log);
		}
	}

	if ($config['regkeys'] > 0 && $udata['users_confirmreg'] > 0 && empty($udata['users_ban'])) {
		if (!strsearch($php_self, array('/pages/key.php', '/input.php'))) {
			redirect('/pages/key.php?log='.$log);
		}
	}

	// --------------------- Проверка соответствия ip-адреса ---------------------//
	if (!empty($udata['users_ipbinding'])) {
		if ($_SESSION['my_ip'] != $ip) {
			$_SESSION = array();
			setcookie(session_name(), '', 0, '/', '');
			session_destroy();
			redirect(html_entity_decode($request_uri));
		}
	}

	// ---------------------- Получение ежедневного бонуса -----------------------//
	if (isset($udata['users_timebonus']) && $udata['users_timebonus'] < time() - 82800) {  // Получение бонуса каждые 23 часа
		DB::run() -> query("UPDATE `users` SET `users_timebonus`=?, `users_money`=`users_money`+? WHERE `users_login`=? LIMIT 1;", array(SITETIME, $config['bonusmoney'], $log));
		notice('Получен ежедневный бонус '.moneys($config['bonusmoney']).'!');
	}

	// ------------------ Запись текущей страницы для админов --------------------//
	if (strstr($php_self, '/admin')) {
		DB::run() -> query("INSERT INTO `admlog` (`admlog_user`, `admlog_request`, `admlog_referer`, `admlog_ip`, `admlog_brow`, `admlog_time`) VALUES (?, ?, ?, ?, ?, ?);", array($log, $request_uri, $http_referer, $ip, $brow, SITETIME));

		DB::run() -> query("DELETE FROM `admlog` WHERE `admlog_time` < (SELECT MIN(`admlog_time`) FROM (SELECT `admlog_time` FROM `admlog` ORDER BY `admlog_time` DESC LIMIT 500) AS del);");
	}
	// -------------------------- Дайджест ------------------------------------//
	DB::run() -> query("INSERT INTO `visit` (`visit_user`, `visit_self`, `visit_ip`, `visit_nowtime`)  VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `visit_self`=?, `visit_ip`=?, `visit_count`=?, `visit_nowtime`=?;", array($log, $php_self, $ip, SITETIME, $php_self, $ip, $_SESSION['counton'], SITETIME));
}
?>
