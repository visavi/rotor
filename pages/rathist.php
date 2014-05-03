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
	$act = 'received';
}
if (empty($_GET['uz'])) {
	$uz = check($log);
} else {
	$uz = check(strval($_GET['uz']));
}

show_title('История голосований '.nickname($uz));

if (is_user()) {
	$is_admin = is_admin();

	$data = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));

	if (!empty($data)) {

		switch ($act):
		############################################################################################
		##                                    Полученные голоса                                   ##
		############################################################################################
			case 'received':
				echo '<img src="/images/img/thumb-up.gif" alt="image" /> <b>Полученные</b> / <a href="rathist.php?act=gave&amp;uz='.$uz.'">Отданные</a><hr />';

				$queryrat = DB::run() -> query("SELECT * FROM `rating` WHERE `rating_login`=? ORDER BY `rating_time` DESC LIMIT 20;", array($uz));
				$rat = $queryrat -> fetchAll();

				if (count($rat) > 0) {
					if ($is_admin) {
						echo '<form action="rathist.php?act=del&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
					}

					foreach($rat as $data) {
						echo '<div class="b">';

						if ($is_admin) {
							echo '<input type="checkbox" name="del[]" value="'.$data['rating_id'].'" /> ';
						}

						if (empty($data['rating_vote'])) {
							echo '<img src="/images/img/error.gif" alt="Минус" /> ';
						} else {
							echo '<img src="/images/img/open.gif" alt="Плюс" /> ';
						}

						echo '<b>'.profile($data['rating_user']).'</b> ('.date_fixed($data['rating_time']).')</div>';
						echo '<div>Комментарий: ';

						if (!empty($data['rating_text'])) {
							echo bb_code($data['rating_text']);
						} else {
							echo 'Отсутствует';
						}

						echo '</div>';
					}

					if ($is_admin) {
						echo '<br /><input type="submit" value="Удалить выбранное" /></form>';
					}

					echo '<br />';
				} else {
					show_error('В истории еще ничего нет!');
				}
			break;

			############################################################################################
			##                                      Отданные голоса                                   ##
			############################################################################################
			case 'gave':
				echo '<img src="/images/img/thumb-up.gif" alt="image" /> <a href="rathist.php?act=received&amp;uz='.$uz.'">Полученные</a> / <b>Отданные</b><hr />';

				$queryrat = DB::run() -> query("SELECT * FROM `rating` WHERE `rating_user`=? ORDER BY `rating_time` DESC LIMIT 20;", array($uz));
				$rat = $queryrat -> fetchAll();

				if (count($rat) > 0) {
					foreach($rat as $data) {
						echo '<div class="b">';
						if (empty($data['rating_vote'])) {
							echo '<img src="/images/img/error.gif" alt="Минус" /> ';
						} else {
							echo '<img src="/images/img/open.gif" alt="Плюс" /> ';
						}

						echo '<b>'.profile($data['rating_login']).'</b> ('.date_fixed($data['rating_time']).')</div>';
						echo '<div>Комментарий: ';

						if (!empty($data['rating_text'])) {
							echo bb_code($data['rating_text']);
						} else {
							echo 'Отсутствует';
						}

						echo '</div>';
					}

					echo '<br />';
				} else {
					show_error('В истории еще ничего нет!');
				}
			break;

			############################################################################################
			##                                     Удаление истории                                   ##
			############################################################################################
			case 'del':

				$uid = check($_GET['uid']);
				if (isset($_POST['del'])) {
					$del = intar($_POST['del']);
				} else {
					$del = 0;
				}

				if (is_admin()) {
					if ($uid == $_SESSION['token']) {
						if (!empty($del)) {
							$del = implode(',', $del);

							DB::run() -> query("DELETE FROM `rating` WHERE `rating_id` IN (".$del.") AND `rating_login`=?;", array($uz));

							$_SESSION['note'] = 'Выбранные голосования успешно удалены!';
							redirect("rathist.php?uz=$uz");
						} else {
							show_error('Ошибка! Отсутствуют выбранные голосования!');
						}
					} else {
						show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
					}
				} else {
					show_error('Ошибка! Удалять голосования могут только модераторы!');
				}

				echo '<img src="/images/img/back.gif" alt="image" /> <a href="rathist.php?uz='.$uz.'">Вернуться</a><br />';
			break;

		default:
			redirect("rathist.php");
		endswitch;

	} else {
		show_error('Ошибка! Пользователь с данным логином  не зарегистрирован!');
	}
} else {
	show_login('Вы не авторизованы, чтобы просматривать историю, необходимо');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="user.php?uz='.$uz.'">В анкету</a><br />';

include_once ('../themes/footer.php');
?>
