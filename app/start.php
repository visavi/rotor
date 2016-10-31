<?php

require __DIR__.'/bootstrap.php';

session_name('SID');
session_start();

############################################################################################
##                                 Проверка на ip-бан                                     ##
############################################################################################
if (file_exists(STORAGE.'/temp/ipban.dat')) {
	$arrbanip = unserialize(file_get_contents(STORAGE.'/temp/ipban.dat'));
} else {
	$arrbanip = save_ipban();
}

if (is_array($arrbanip) && count($arrbanip) > 0) {
	foreach($arrbanip as $ipdata) {
		$ipmatch = 0;
		$ipsplit = explode('.', App::getClientIp());
		$dbsplit = explode('.', $ipdata);

		for($i = 0; $i < 4; $i++) {
			if ($ipsplit[$i] == $dbsplit[$i] || $dbsplit[$i] == '*') {
				$ipmatch += 1;
			}
		}

		if ($ipmatch == 4) {
			redirect('/banip');
		} //бан по IP
	}
}
############################################################################################
##                                 Счетчик запросов                                       ##
############################################################################################
if (!empty($config['doslimit'])) {
	if (is_writeable(STORAGE.'/antidos')) {
		$dosfiles = glob(STORAGE.'/antidos/*.dat');
		foreach ($dosfiles as $filename) {
			$array_filemtime = @filemtime($filename);
			if ($array_filemtime < (time() - 60)) {
				@unlink($filename);
			}
		}
		// -------------------------- Проверка на время -----------------------------//
		if (file_exists(STORAGE.'/antidos/'.App::getClientIp().'.dat')) {
			$file_dos = file(STORAGE.'/antidos/'.App::getClientIp().'.dat');
			$file_str = explode('|', $file_dos[0]);
			if ($file_str[0] < (time() - 60)) {
				@unlink(STORAGE.'/antidos/'.App::getClientIp().'.dat');
			}
		}
		// ------------------------------ Запись логов -------------------------------//
		$write = time().'|'.App::server('REQUEST_URI').'|'.App::server('HTTP_REFERER').'|'.App::getUserAgent().'|'.App::getUsername().'|';
		write_files(STORAGE.'/antidos/'.App::getClientIp().'.dat', $write."\r\n", 0, 0666);
		// ----------------------- Автоматическая блокировка ------------------------//
		if (counter_string(STORAGE.'/antidos/'.App::getClientIp().'.dat') > $config['doslimit']) {

			if (!empty($config['errorlog'])){
				$banip = DB::run() -> querySingle("SELECT `ban_id` FROM `ban` WHERE `ban_ip`=? LIMIT 1;", array(App::getClientIp()));
				if (empty($banip)) {
					DB::run() -> query("INSERT INTO `error` (`error_num`, `error_request`, `error_referer`, `error_username`, `error_ip`, `error_brow`, `error_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", array(666, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getUsername(), App::getClientIp(), App::getUserAgent(), SITETIME));

					DB::run() -> query("INSERT IGNORE INTO ban (`ban_ip`, `ban_time`) VALUES (?, ?);", array(App::getClientIp(), SITETIME));
					save_ipban();
				}
			}

			unlink(STORAGE.'/antidos/'.App::getClientIp().'.dat');
		}
	}
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

				$_SESSION['my_ip'] = App::getClientIp();
				$_SESSION['log'] = $unlog;
				$_SESSION['par'] = md5($config['keypass'].$checkuser['users_pass']);

				$authorization = DB::run() -> querySingle("SELECT `login_id` FROM `login` WHERE `login_user`=? AND `login_time`>? LIMIT 1;", array($unlog, SITETIME-30));

				if (empty($authorization)) {
					DB::run() -> query("INSERT INTO `login` (`login_user`, `login_ip`, `login_brow`, `login_time`) VALUES (?, ?, ?, ?);", array($unlog, App::getClientIp(), App::getUserAgent(), SITETIME));
					DB::run() -> query("DELETE FROM `login` WHERE `login_user`=? AND `login_time` < (SELECT MIN(`login_time`) FROM (SELECT `login_time` FROM `login` WHERE `login_user`=? ORDER BY `login_time` DESC LIMIT 50) AS del);", array($unlog, $unlog));
				}

				DB::run() -> query("UPDATE `users` SET `users_visits`=`users_visits`+1, `users_timelastlogin`=? WHERE `users_login`=? LIMIT 1;", array(SITETIME, $unlog));
			}
		}
	}
}

// ---------------------- Установка сессионных переменных -----------------------//
$log = '';
if (empty($_SESSION['protect'])) {
	$_SESSION['protect'] = rand(1000, 9999);
}
if (!isset($_SESSION['token'])) {
	if (!empty($config['session'])){
		$_SESSION['token'] = generate_password(6);
	} else {
		$_SESSION['token'] = 0;
	}
}
ob_start('ob_processing');
############################################################################################
##                                     Авторизация                                        ##
############################################################################################
if ($udata = is_user()) {

    Registry::set('user', $udata);

    $log = $udata['users_login'];
	// ---------------------- Переопределение глобальных настроек -------------------------//
	$config['themes']     = $udata['users_themes'];      # Скин/тема по умолчанию
	$config['bookpost']   = $udata['users_postguest'];   # Вывод сообщений в гостевой
	$config['postnews']   = $udata['users_postnews'];    # Новостей на страницу
	$config['forumpost']  = $udata['users_postforum'];   # Вывод сообщений в форуме
	$config['forumtem']   = $udata['users_themesforum']; # Вывод тем в форуме
	$config['boardspost'] = $udata['users_postboard'];   # Вывод объявлений
	$config['privatpost'] = $udata['users_postprivat'];  # Вывод писем в привате

	if ($udata['users_ban'] == 1) {
		if (!strsearch(App::server('PHP_SELF'), array('/ban', '/rules'))) {
			redirect('/ban?log='.$log);
		}
	}

	if ($config['regkeys'] > 0 && $udata['users_confirmreg'] > 0 && empty($udata['users_ban'])) {
		if (!strsearch(App::server('PHP_SELF'), array('/key', '/login'))) {
			redirect('/key?log='.$log);
		}
	}

	// --------------------- Проверка соответствия ip-адреса ---------------------//
	if (!empty($udata['users_ipbinding'])) {
		if ($_SESSION['my_ip'] != App::getClientIp()) {
			$_SESSION = array();
			setcookie(session_name(), '', 0, '/', '');
			session_destroy();
			redirect(html_entity_decode(App::server('REQUEST_URI')));
		}
	}

	// ---------------------- Получение ежедневного бонуса -----------------------//
	if (isset($udata['users_timebonus']) && $udata['users_timebonus'] < time() - 82800) {  // Получение бонуса каждые 23 часа
		DB::run() -> query("UPDATE `users` SET `users_timebonus`=?, `users_money`=`users_money`+? WHERE `users_login`=? LIMIT 1;", array(SITETIME, $config['bonusmoney'], $log));
		notice('Получен ежедневный бонус '.moneys($config['bonusmoney']).'!');
	}

	// ------------------ Запись текущей страницы для админов --------------------//
	if (strstr(App::server('PHP_SELF'), '/admin')) {
		DB::run() -> query("INSERT INTO `admlog` (`admlog_user`, `admlog_request`, `admlog_referer`, `admlog_ip`, `admlog_brow`, `admlog_time`) VALUES (?, ?, ?, ?, ?, ?);", array($log, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getClientIp(), App::getUserAgent(), SITETIME));

		DB::run() -> query("DELETE FROM `admlog` WHERE `admlog_time` < (SELECT MIN(`admlog_time`) FROM (SELECT `admlog_time` FROM `admlog` ORDER BY `admlog_time` DESC LIMIT 500) AS del);");
	}
}

$browser_detect = new Mobile_Detect();

// ------------------------ Автоопределение системы -----------------------------//
if (!is_user() || empty($config['themes'])) {
    if (!empty($config['touchthemes'])) {
        if ($browser_detect->isTablet()) {
            $config['themes'] = $config['touchthemes'];
        }
    }

    if (!empty($config['webthemes'])) {
        if (!$browser_detect->isMobile() && !$browser_detect->isTablet()) {
            $config['themes'] = $config['webthemes'];
        }
    }
}

if (empty($config['themes'])) {
    $config['themes'] = 'default';
}

Registry::set('config', $config);


/*if ($config['closedsite'] == 2 && !is_admin() && !strsearch($php_self, array('/pages/closed.php', '/input.php'))) {
    redirect('/pages/closed.php');
}

if ($config['closedsite'] == 1 && !is_user() && !strsearch($php_self, array('/pages/login.php', '/pages/registration.php', '/mail/lostpassword.php', '/input.php'))) {
    notice('Для входа на сайт необходимо авторизоваться!');
    redirect('/login');
}*/
