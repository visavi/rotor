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
	$act = '404';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_admin(array(101, 102))) {
	show_title('Просмотр лог-файлов');

	if (empty($config['errorlog'])){
		echo '<b><span style="color:#ff0000">Внимание! Запись логов выключена в настройках!</span></b><br /><br />';
	}

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case '404':

			echo '<b>Ошибки 404</b> | <a href="logfiles.php?act=403">Ошибки 403</a> | <a href="logfiles.php?act=666">Автобаны</a><br /><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `error_num`=?;", array(404));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryban = DB::run() -> query("SELECT * FROM `error` WHERE `error_num`=? ORDER BY `error_time` DESC LIMIT ".$start.", ".$config['loglist'].";", array(404));

				while ($data = $queryban -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/files.gif" alt="image" /> <b>'.$data['error_request'].'</b> <small>('.date_fixed($data['error_time']).')</small></div>';
					echo '<div>Referer: '.$data['error_referer'].'<br />';
					echo 'Пользователь: '.$data['error_username'].'<br />';
					echo '<small><span class="data">('.$data['error_brow'].', '.$data['error_ip'].')</span></small></div>';
				}

				page_strnavigation('logfiles.php?act=404&amp;', $config['loglist'], $start, $total);

				if (is_admin(array(101))) {
					echo '<img src="/images/img/error.gif" alt="image" /> <a href="logfiles.php?act=clear&amp;uid='.$_SESSION['token'].'">Очистить логи</a><br />';
				}
			} else {
				show_error('Записей еще нет!');
			}
		break;

		############################################################################################
		##                                       Ошибки 403                                       ##
		############################################################################################
		case '403':

			echo '<a href="logfiles.php?act=404">Ошибки 404</a> | <b>Ошибки 403</b> | <a href="logfiles.php?act=666">Автобаны</a><br /><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `error_num`=?;", array(403));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryban = DB::run() -> query("SELECT * FROM `error` WHERE `error_num`=? ORDER BY `error_time` DESC LIMIT ".$start.", ".$config['loglist'].";", array(403));

				while ($data = $queryban -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/files.gif" alt="image" /> <b>'.$data['error_request'].'</b> <small>('.date_fixed($data['error_time']).')</small></div>';
					echo '<div>Referer: '.$data['error_referer'].'<br />';
					echo 'Пользователь: '.$data['error_username'].'<br />';
					echo '<small><span class="data">('.$data['error_brow'].', '.$data['error_ip'].')</span></small></div>';
				}

				page_strnavigation('logfiles.php?act=403&amp;', $config['loglist'], $start, $total);
			} else {
				show_error('Записей еще нет!');
			}
		break;

		############################################################################################
		##                                        Автобаны                                        ##
		############################################################################################
		case '666':

			echo '<a href="logfiles.php?act=404">Ошибки 404</a> | <a href="logfiles.php?act=403">Ошибки 403</a> | <b>Автобаны</b><br /><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `error_num`=?;", array(666));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryban = DB::run() -> query("SELECT * FROM `error` WHERE `error_num`=? ORDER BY `error_time` DESC LIMIT ".$start.", ".$config['loglist'].";", array(666));

				while ($data = $queryban -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/files.gif" alt="image" /> <b>'.$data['error_request'].'</b> <small>('.date_fixed($data['error_time']).')</small></div>';
					echo '<div>Referer: '.$data['error_referer'].'<br />';
					echo 'Пользователь: '.$data['error_username'].'<br />';
					echo '<small><span class="data">('.$data['error_brow'].', '.$data['error_ip'].')</span></small></div>';
				}

				page_strnavigation('logfiles.php?act=666&amp;', $config['loglist'], $start, $total);
			} else {
				show_error('Записей еще нет!');
			}
		break;

		############################################################################################
		##                                     Очистка логов                                      ##
		############################################################################################
		case 'clear':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if (is_admin(array(101))) {
					DB::run() -> query("TRUNCATE `error`;");

					$_SESSION['note'] = 'Лог-файлы успешно очищены!';
					redirect("logfiles.php");

				} else {
					show_error('Ошибка! Очищать логи могут только суперадмины!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="logfiles.php">Вернуться</a><br />';
		break;

	default:
		redirect("logfiles.php");
	endswitch;

	echo '<i class="fa fa-wrench"></i> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
