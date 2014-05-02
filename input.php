<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('includes/start.php');
require_once ('includes/functions.php');
require_once ('includes/header.php');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$domain = check_string($config['home']);

switch ($act):
############################################################################################
##                                       Авторизация                                      ##
############################################################################################
case 'index':

	$login = (isset($_REQUEST['login'])) ? check(utf_lower($_REQUEST['login'])) : '';
	$pass = (isset($_REQUEST['pass'])) ? md5(md5(trim($_REQUEST['pass']))) : '';


	if (!empty($_POST['cookietrue']) || !empty($_GET['login'])) {
		$cookietrue = 1;
	}

	if (!empty($login) && !empty($pass)) {


		$udata = DB::run() -> queryFetch("SELECT `users_login`, `users_pass` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array($login, $login));

		if (!empty($udata)) {
			if ($pass == $udata['users_pass']) {

				if (!empty($cookietrue)) {
					setcookie('cooklog', $udata['users_login'], time() + 3600 * 24 * 365, '/', $domain);
					setcookie('cookpar', md5($pass.$config['keypass']), time() + 3600 * 24 * 365, '/', $domain, null, true);
				}

				$_SESSION['log'] = $udata['users_login'];
				$_SESSION['par'] = md5($config['keypass'].$pass);
				$_SESSION['my_ip'] = $ip;

				DB::run() -> query("UPDATE `users` SET `users_visits`=`users_visits`+1, `users_timelastlogin`=? WHERE `users_login`=?", array(SITETIME, $udata['users_login']));

				$authorization = DB::run() -> querySingle("SELECT `login_id` FROM `login` WHERE `login_user`=? AND `login_time`>? LIMIT 1;", array($udata['users_login'], SITETIME-30));

				if (empty($authorization)) {
					DB::run() -> query("INSERT INTO `login` (`login_user`, `login_ip`, `login_brow`, `login_time`, `login_type`) VALUES (?, ?, ?, ?, ?);", array($udata['users_login'], $ip, $brow, SITETIME, 1));
					DB::run() -> query("DELETE FROM `login` WHERE `login_user`=? AND `login_time` < (SELECT MIN(`login_time`) FROM (SELECT `login_time` FROM `login` WHERE `login_user`=? ORDER BY `login_time` DESC LIMIT 50) AS del);", array($udata['users_login'], $udata['users_login']));
				}

				notice('Вы успешно авторизованы!');
				redirect($config['home'].'/index.php');
			}
		}
	}

	notice('Ошибка авторизации. Неправильный логин или пароль!');
	redirect($config['home'].'/pages/login.php');
break;
############################################################################################
##                                           Выход                                        ##
############################################################################################
case 'exit':

	$_SESSION = array();
	setcookie('cookpar', '', time() - 3600, '/', $domain, null, true);
	setcookie(session_name(), '', time() - 3600, '/', '');
	session_unset();
	session_destroy();

	redirect($config['home'].'/index.php');
break;

default:
	redirect($config['home'].'/index.php');
endswitch;
?>
