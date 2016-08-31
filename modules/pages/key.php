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

show_title('Подтверждение регистрации');

if (is_user()) {
	if (!empty($config['regkeys'])) {
		if (!empty($udata['users_confirmreg'])) {
			if ($udata['users_confirmreg'] == 1) {
				switch ($act):
				############################################################################################
				##                                    Главная страница                                    ##
				############################################################################################
					case "index":

						echo 'Добро пожаловать, <b>'.check($log).'!</b><br />';
						echo 'Для подтверждения регистрации вам необходимо ввести мастер-ключ, который был отправлен вам на E-mail<br /><br />';

						echo '<div class="form">';
						echo 'Мастер-код:<br />';
						echo '<form method="post" action="key.php?act=inkey">';
						echo '<input name="key" maxlength="30" />';
						echo '<input value="Подтвердить" type="submit" /></form></div><br />';

						echo 'Пока вы не подтвердите регистрацию вы не сможете войти на сайт<br />';
						echo 'Ваш профиль будет ждать активации в течении 24 часов, после чего автоматически удален<br /><br />';

						echo '<img src="/images/img/error.gif" alt="image" /> <a href="/input.php?act=exit">Выход</a><br />';
					break;

					############################################################################################
					##                                   Проверка мастер-ключа                                ##
					############################################################################################
					case "inkey":

						if (isset($_GET['key'])) {
							$key = check(trim($_GET['key']));
						} else {
							$key = check(trim($_POST['key']));
						}

						if (!empty($key)) {
							if ($key == $udata['users_confirmregkey']) {
								DB::run() -> query("UPDATE users SET users_confirmreg=?, users_confirmregkey=? WHERE users_login=?;", array(0, '', $log));

								echo 'Мастер-код подтвержден, теперь вы можете войти на сайт!<br /><br />';
								echo '<img src="/images/img/open.gif" alt="image" /> <b><a href="/index.php">Вход на сайт!</a></b><br /><br />';
							} else {
								show_error('Ошибка! Мастер-код не совпадает с данными, проверьте правильность ввода!');
							}
						} else {
							show_error('Ошибка! Вы не ввели мастер-код, пожалуйста повторите!');
						}

						echo '<img src="/images/img/back.gif" alt="image" /> <a href="key.php">Вернуться</a><br />';
					break;

				default:
					redirect("key.php");
				endswitch;
			} else {
				echo 'Добро пожаловать, <b>'.check($log).'!</b><br />';
				echo 'Ваш аккаунт еще не прошел проверку администрацией<br />';
				echo 'Если после авторизации вы видите эту страницу, значит ваш профиль еще не активирован!<br /><br />';
				echo '<img src="/images/img/error.gif" alt="image" /> <a href="/input.php?act=exit">Выход</a><br />';
			}
		} else {
			show_error('Ошибка! Вашему профилю не требуется подтверждение регистрации!');
		}
	} else {
		show_error('Ошибка! Подтверждение регистрации выключено на сайте!');
	}
} else {
	show_error('Ошибка! Для подтверждение регистрации  необходимо быть авторизованным!');
}

include_once ('../themes/footer.php');
?>
