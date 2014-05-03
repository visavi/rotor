<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

echo'<img src="/images/img/group.png" alt="image" /> <b>Кто в онлайне</b><br /><br />';

$total_all = DB::run() -> querySingle("SELECT count(*) FROM `online`;");
$total = DB::run() -> querySingle("SELECT count(*) FROM `online` WHERE `online_user`<>?;", array(''));

echo 'Всего на сайте: <b>'.$total_all.'</b><br />';
echo 'Зарегистрированных:  <b>'.$total.'</b><br /><br />';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':

		if ($total > 0) {
			if ($start >= $total) {
				$start = 0;
			}

			$queryonline = DB::run() -> query("SELECT * FROM `online` WHERE `online_user`<>? ORDER BY `online_time` DESC LIMIT ".$start.", ".$config['onlinelist'].";", array(''));

			while ($data = $queryonline -> fetch()) {
				echo '<div class="b">';
				echo user_gender($data['online_user']).' <b>'.profile($data['online_user']).'</b> (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<div><span class="data">('.$data['online_brow'].', '.$data['online_ip'].')</span></div>';
				}
			}

			page_strnavigation('online.php?', $config['onlinelist'], $start, $total);
		} else {
			show_error('Авторизованных пользователей нет!');
		}

		echo '<img src="/images/img/users.gif" alt="image" /> <a href="online.php?act=all">Показать гостей</a><br />';
	break;

	############################################################################################
	##                                Список всех пользователей                               ##
	############################################################################################
	case 'all':

		$total = $total_all;

		if ($total > 0) {
			if ($start >= $total) {
				$start = 0;
			}

			$queryonline = DB::run() -> query("SELECT * FROM `online` ORDER BY `online_time` DESC LIMIT ".$start.", ".$config['onlinelist'].";");

			while ($data = $queryonline -> fetch()) {
				if (empty($data['online_user'])) {
					echo '<div class="b">';
					echo '<img src="/images/img/user.gif" alt="image" /> <b>'.$config['guestsuser'].'</b>  (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';
				} else {
					echo '<div class="b">';
					echo user_gender($data['online_user']).' <b>'.profile($data['online_user']).'</b> (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';
				}

				if (is_admin() || empty($config['anonymity'])) {
					echo '<div><span class="data">('.$data['online_brow'].', '.$data['online_ip'].')</span></div>';
				}
			}

			page_strnavigation('online.php?act=all&amp;', $config['onlinelist'], $start, $total);
		} else {
			show_error('На сайте никого нет!');
		}

		echo '<img src="/images/img/users.gif" alt="image" /> <a href="online.php">Cкрыть гостей</a><br />';
	break;

default:
	redirect('online.php');
endswitch;

include_once ('../themes/footer.php');
?>
