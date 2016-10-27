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

if (is_admin(array(101))) {
	show_title('Админ-логи');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":

			$total = DB::run() -> querySingle("SELECT count(*) FROM admlog;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryban = DB::run() -> query("SELECT * FROM `admlog` ORDER BY `admlog_time` DESC LIMIT ".$start.", ".$config['loglist'].";");

				while ($data = $queryban -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/files.gif" alt="image" /> <b>'.profile($data['admlog_user']).'</b>';
					echo ' ('.date_fixed($data['admlog_time']).')</div>';
					echo '<div>Страница: '.$data['admlog_request'].'<br />';
					echo 'Откуда: '.$data['admlog_referer'].'<br />';
					echo '<small><span style="color:#cc00cc">('.$data['admlog_brow'].', '.$data['admlog_ip'].')</span></small></div>';
				}

				page_strnavigation('logadmin.php?', $config['loglist'], $start, $total);

				echo '<img src="/images/img/error.gif" alt="image" /> <a href="logadmin.php?act=del&amp;uid='.$_SESSION['token'].'">Очистить логи</a><br />';
			} else {
				show_error('Записей еще нет!');
			}
		break;

		############################################################################################
		##                                    Очистка логов                                       ##
		############################################################################################
		case "del":

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				DB::run() -> query("DELETE FROM admlog;");

				$_SESSION['note'] = 'Лог-файл успешно очищен!';
				redirect("logadmin.php");
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="logadmin.php">Вернуться</a><br />';
			break;

	default:
		redirect("logadmin.php");
	endswitch;

	echo '<i class="fa fa-wrench"></i> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
