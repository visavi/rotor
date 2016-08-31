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

if (is_admin(array(101, 102))) {
	show_title('IP-бан панель');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':
			echo '<a href="logfiles.php?act=666">История автобанов</a><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `ban`;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryban = DB::run() -> query("SELECT * FROM `ban` ORDER BY `ban_time` DESC LIMIT ".$start.", ".$config['ipbanlist'].";");

				echo '<form action="ban.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				while ($data = $queryban -> fetch()) {
					echo '<div class="b">';
					echo '<input type="checkbox" name="del[]" value="'.$data['ban_id'].'" />';
					echo '<img src="/images/img/files.gif" alt="image" /> <b>'.$data['ban_ip'].'</b></div>';

					echo '<div>Добавлено: ';

					if (!empty($data['ban_user'])) {
						echo '<b>'.profile($data['ban_user']).'</b><br />';
					} else {
						echo '<b>Автоматически</b><br />';
					}

					echo 'Время: '.date_fixed($data['ban_time']).'</div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('ban.php?', $config['ipbanlist'], $start, $total);

				echo 'Всего заблокировано: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('В бан-листе пока пусто!');
			}

			echo '<div class="form">';
			echo '<form action="ban.php?act=add&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
			echo 'IP-адрес:<br />';
			echo '<input type="text" name="ips" />';
			echo '<input value="Добавить" type="submit" /></form></div><br />';

			echo 'Примеры банов: 127.0.0.1 без отступов и пробелов<br />';
			echo 'Или по маске 127.0.0.* , 127.0.*.* , будут забанены все IP совпадающие по начальным цифрам<br /><br />';

			if ($total > 0 && is_admin(array(101))) {
				echo '<img src="/images/img/error.gif" alt="image" /> <a href="ban.php?act=clear&amp;uid='.$_SESSION['token'].'">Очистить список</a><br />';
			}
		break;

		############################################################################################
		##                                   Занесение в список                                   ##
		############################################################################################
		case 'add':

			$uid = check($_GET['uid']);
			$ips = check($_POST['ips']);

			if ($uid == $_SESSION['token']) {
				if (preg_match('|^[0-9]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}\.[0-9,*]{1,3}$|', $ips)) {
					$banip = DB::run() -> querySingle("SELECT `ban_id` FROM `ban` WHERE `ban_ip`=? LIMIT 1;", array($ips));
					if (empty($banip)) {
						DB::run() -> query("INSERT INTO ban (`ban_ip`, `ban_user`, `ban_time`) VALUES (?, ?, ?);", array($ips, $log, SITETIME));
						save_ipban();

						$_SESSION['note'] = 'IP успешно занесен в список!';
						redirect("ban.php?start=$start");
					} else {
						show_error('Ошибка! Введенный IP уже имеетеся в списке!');
					}
				} else {
					show_error('Ошибка! Вы ввели недопустимый IP-адрес для бана!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="ban.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление из списка                                   ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM `ban` WHERE `ban_id` IN (".$del.");");
					save_ipban();

					$_SESSION['note'] = 'Выбранные IP успешно удалены из списка!';
					redirect("ban.php?start=$start");
				} else {
					echo '<img src="/images/img/error.gif" alt="image" /> <b>Ошибка удаления! Отсутствуют выбранные IP</b><br />';
				}
			} else {
				echo '<img src="/images/img/error.gif" alt="image" /> <b>Ошибка! Неверный идентификатор сессии, повторите действие!</b><br />';
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="ban.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Очистка списка                                     ##
		############################################################################################
		case 'clear':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					DB::run() -> query("TRUNCATE `ban`;");
					save_ipban();

					$_SESSION['note'] = 'Список IP успешно очищен!';
					redirect("ban.php");
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Очищать бан-лист могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="ban.php?start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("ban.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
