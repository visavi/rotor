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

show_title('Письмо Администратору');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':

		echo '<div class="form">';
		echo '<form method="post" action="index.php?act=send">';

		if (!is_user()) {
			echo 'Ваше имя:<br /><input name="name" maxlength="20" /><br />';
			echo 'Ваш E-mail:<br /><input name="umail" maxlength="50" /><br />';
		} else {
			if (empty($udata['users_email'])) {
				echo 'Ваш E-mail:<br /><input name="umail" maxlength="50" /><br />';
			}
		}

		echo 'Сообщение:<br />';
		echo '<textarea cols="25" rows="5" name="body"></textarea><br />';

		echo 'Проверочный код:<br />';
		echo '<img src="/gallery/protect.php" alt="" /><br />';

		echo '<input name="provkod" size="6" maxlength="6" /><br />';
		echo '<input value="Отправить" type="submit" /></form></div><br />';

		echo 'Обновите страницу если вы не видите проверочный код!<br /><br />';
	break;

	############################################################################################
	##                                    Отправка сообщения                                  ##
	############################################################################################
	case 'send':

		$body = check($_POST['body']);
		$provkod = check(strtolower($_POST['provkod']));

		if (isset($_POST['name'])) {
			$name = check($_POST['name']);
		} else {
			$name = '';
		}
		if (isset($_POST['umail'])) {
			$umail = check($_POST['umail']);
		} else {
			$umail = '';
		}

		if (is_user()) {
			$name = $log;

			if (!empty($udata['users_email'])) {
				$umail = $udata['users_email'];
			}
		}

		if ($_SESSION['protect'] == $provkod) {
			if (utf_strlen($name) >= 3 && utf_strlen($name) <= 50) {
				if (utf_strlen($body) >= 5 && utf_strlen($body) <= 5000) {
					if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $umail)) {

						if (empty($config['sendmail'])) {
							addmail($config['emails'], "Письмо с сайта ".$config['title'], html_entity_decode($body, ENT_QUOTES)."\n\nIp: $ip \nБраузер: $brow \nОтправлено: ".date('j.m.Y / H:i', SITETIME), $umail, $name);
						} else {
							if (check_user($config['nickname'])) {
								$textpriv = 'Письмо от пользователя [b]'.$name.'[/b]!<br />E-mail: '.$umail.'<br />Сообщение: '.$body;
								DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($config['nickname'], $config['nickname'], $textpriv, SITETIME));
								DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?;", array($config['nickname']));
							} else {
								show_error('Ошибка! Не удалось отправить письмо администратору так как его профиля не существует!');
							}
						}

						$_SESSION['note'] = 'Ваше письмо успешно отправлено!';
						redirect("index.php");

					} else {
						show_error('Вы ввели неверный адрес e-mail, необходим формат name@site.domen!');
					}
				} else {
					show_error('Слишком длинное или короткое сообшение, необходимо от 5 до 5000 символов!');
				}
			} else {
				show_error('Слишком длинное или короткое имя, необходимо от 3 до 50 символов!');
			}
		} else {
			show_error('Проверочное число не совпало с данными на картинке!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">Вернуться</a><br />';
	break;

default:
	redirect("index.php");
endswitch;

include_once ('../themes/footer.php');
?>
