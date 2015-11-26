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

if (is_admin(array(101)) && $log == $config['nickname']) {
	show_title('Очистка базы юзеров');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo 'Удалить пользователей которые не посещали сайт:<br />';

			echo '<div class="form">';
			echo '<form action="delusers.php?act=poddel" method="post">';
			echo 'Период:<br />';
			echo '<select name="deldate">';
			echo '<option value="1080">3 года</option>';
			echo '<option value="900">2.5 года</option>';
			echo '<option value="720">2 года</option>';
			echo '<option value="560">1.5 года</option>';
			echo '<option value="360">1 год</option>';
			echo '<option value="180">0.5 года</option>';
			echo '</select><br />';
			echo 'Минимум актива:<br />';
			echo '<input type="text" name="point" value="0" /><br />';
			echo '<input value="Анализ" type="submit" /></form></div><br />';

			echo 'Всего пользователей: <b>'.stats_users().'</b><br /><br />';
		break;

		############################################################################################
		##                                Подтверждение удаления                                  ##
		############################################################################################
		case "poddel":

			$deldate = abs(intval($_POST['deldate']));
			$point = abs(intval($_POST['point']));

			if ($deldate >= 180) {
				$deltime = $deldate * 24 * 3600;

				$queryusers = DB::run() -> query("SELECT users_login FROM users WHERE users_timelastlogin<? AND users_point<=?;", array(SITETIME - $deltime, $point));
				$users = $queryusers -> fetchAll(PDO::FETCH_COLUMN);
				$total = count($users);

				if ($total > 0) {
					echo 'Будут удалены пользователи не посещавшие сайт более <b>'.$deldate.'</b> дней <br />';
					echo 'И имеющие в своем активе не более '.points($point).'<br /><br />';

					echo '<b>Список:</b> ';

					foreach ($users as $key => $value) {
						if ($key == 0) {
							$comma = '';
						} else {
							$comma = ', ';
						}
						echo $comma.' '.profile($value);
					}

					echo '<br /><br />Будет удалено пользователей: <b>'.$total.'</b><br /><br />';

					echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="delusers.php?act=del&amp;deldate='.$deldate.'&amp;point='.$point.'&amp;uid='.$_SESSION['token'].'">Удалить пользователей</a></b><br /><br />';
				} else {
					show_error('Пользователи для удаления отсутсвуют!');
				}
			} else {
				show_error('Ошибка! Указанно недопустимое время для удаления!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="delusers.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                Удаление пользователей                                  ##
		############################################################################################
		case "del":

			$uid = check($_GET['uid']);
			$deldate = abs(intval($_GET['deldate']));
			$point = abs(intval($_GET['point']));

			if ($uid == $_SESSION['token']) {
				if ($deldate >= 180) {
					$deltime = $deldate * 24 * 3600;

					$queryusers = DB::run() -> query("SELECT users_login FROM users WHERE users_timelastlogin<? AND users_point<=?;", array(SITETIME - $deltime, $point));
					$users = $queryusers -> fetchAll(PDO::FETCH_COLUMN);
					$total = count($users);

					if ($total > 0) {
						foreach ($users as $value) {
							delete_album($value);
							delete_users($value);
						}

						echo 'Пользователи не посещавшие сайт более <b>'.$deldate.'</b> дней, успешно удалены!<br />';
						echo 'Было удалено пользователей: <b>'.$total.'</b><br /><br />';
					} else {
						show_error('Пользователи для удаления отсутсвуют!');
					}
				} else {
					show_error('Ошибка! Указанно недопустимое время для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="delusers.php">Вернуться</a><br />';
		break;

	default:
		redirect("delusers.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
