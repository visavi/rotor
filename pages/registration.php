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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Регистрация');

if ($config['openreg'] == 1) {
	if (!is_user()) {
		if (empty($_SESSION['reguser'])) {
			switch ($act):
			############################################################################################
			##                                    Главная страница                                    ##
			############################################################################################
				case 'index':

					echo 'Регистрация на сайте означает что вы ознакомлены и согласны с <b><a href="rules.php">правилами</a></b> нашего сайта<br />';
					echo 'Длина логина или пароля должна быть от 3 до 20 символов<br />';
					echo 'В полях логин и пароль разрешено использовать только знаки латинского алфавита и цифры, а также знак дефис!<br />';

					if ($config['regkeys'] == 1 && !empty($config['regmail'])) {
						echo '<img src="/images/img/warning.gif" alt="image" /> <span style="color:#ff0000"><b>Включено подтверждение регистрации!</b> Вам на почтовый ящик будет выслан мастер-ключ, который необходим для подтверждения регистрации!</span><br />';
					}

					if ($config['regkeys'] == 2) {
						echo '<img src="/images/img/warning.gif" alt="image" /> <span style="color:#ff0000"><b>Включена модерация регистрации!</b> Ваш аккаунт будет активирован только после проверки администрацией!</span><br />';
					}

					if ($config['karantin'] > 0) {
						echo '<img src="/images/img/warning.gif" alt="image" /> <span style="color:#ff0000"><b>Включен карантин!</b> Новые пользователи не могут писать сообщения в течении '.round($config['karantin'] / 3600).' час. после регистрации!</span><br />';
					}

					if (!empty($config['invite'])) {
						echo '<img src="/images/img/warning.gif" alt="image" /> <span style="color:#ff0000"><b>Включена регистрация по приглашениям!</b> Регистрация пользователей возможна только по специальным пригласительным ключам</span><br />';
					}

					$reglogs = (!empty($_SESSION['reglogs'])) ? $_SESSION['reglogs'] : '';

					echo '<br /><div class="form">';
					echo '<form action="registration.php?act=register" method="post">';
					echo 'Логин:<br /><input name="logs" maxlength="20" value="'.$reglogs.'" /><br />';
					echo 'Пароль:<br /><input name="pars" type="password" maxlength="20" /><br />';
					echo 'Повторите пароль:<br /><input name="pars2" type="password" maxlength="20" /><br />';

					if (!empty($config['regmail'])) {
						$regmeil = (!empty($_SESSION['regmeil'])) ? $_SESSION['regmeil'] : '';
						echo 'Ваш e-mail: <br /><input name="meil" maxlength="50" value="'.$regmeil.'" /><br />';
					}

					if (!empty($config['invite'])) {
						echo 'Пригласительный ключ: <br /><input name="invite" maxlength="32" /><br />';
					}

					echo 'Пол:<br />';
					echo '<select name="gender">';
					$selected = (isset($_SESSION['gender']) && $_SESSION['gender'] == 1) ? ' selected="selected"' : '';
					echo '<option value="1"'.$selected.'>Мужской</option>';
					$selected = (isset($_SESSION['gender']) && $_SESSION['gender'] == 2) ? ' selected="selected"' : '';
					echo '<option value="2"'.$selected.'>Женский</option>';
					echo '</select><br />';

					echo 'Проверочный код:<br /> ';
					echo '<img src="/gallery/protect.php" alt="" /><br />';
					echo '<input name="provkod" size="6" maxlength="6" /><br />';

					echo '<br /><input value="Регистрация" type="submit" /></form></div><br />';

					echo 'Обновите страницу если вы не видите проверочный код!<br />';
					echo 'Все поля обязательны для заполнения, более полную информацию о себе вы можете добавить в своем профиле после регистрации<br />';
					echo 'Указывайте верный е-мэйл, на него будут высланы регистрационные данные<br /><br />';
				break;

				############################################################################################
				##                                       Регистрация                                      ##
				############################################################################################
				case 'register':

					$logs = check(strval($_POST['logs']));
					$pars = check(strval($_POST['pars']));
					$pars2 = check(strval($_POST['pars2']));
					$provkod = check(strtolower($_POST['provkod']));
					$invite = (!empty($config['invite'])) ? check(strval($_POST['invite'])) : '';
					$meil = (!empty($config['regmail'])) ? strtolower(check(strval($_POST['meil']))) : '';
					$domain = (!empty($config['regmail'])) ? utf_substr(strrchr($meil, '@'), 1) : '';
					$gender = ($_POST['gender'] == 1) ? 1 : 2;
					$registration_key = '';

					$_SESSION['reglogs'] = $logs;
					$_SESSION['regmeil'] = $meil;
					$_SESSION['gender'] = $gender;

					$validation = new Validation;

					$validation -> addRule('equal', array($provkod, $_SESSION['protect']), 'Проверочное число не совпало с данными на картинке!')
						-> addRule('regex', array($logs, '|^[a-z0-9\-]+$|i'), 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!', true)
						-> addRule('regex', array($pars, '|^[a-z0-9\-]+$|i'), 'Недопустимые символы в пароле. Разрешены знаки латинского алфавита, цифры и дефис!', true)
						-> addRule('email', $meil, 'Вы ввели неверный адрес e-mail, необходим формат name@site.domen!', $config['regmail'])
						-> addRule('string', $invite, 'Слишком длинный или короткий пригласительный ключ!', $config['invite'], 15, 20)
						-> addRule('string', $logs, 'Слишком длинный или короткий логин!', true, 3, 20)
						-> addRule('string', $pars, 'Слишком длинный или короткий пароль!',  true, 6, 20)
						-> addRule('equal', array($pars, $pars2), 'Ошибка! Введенные пароли отличаются друг от друга!')
						-> addRule('not_equal', array($logs, $pars), 'Пароль и логин должны отличаться друг от друга!');

					if (ctype_digit($pars)) {
						$validation -> addError('Запрещен пароль состоящий только из цифр, используйте буквы!');
					}

					if (substr_count($logs, '-') > 2) {
						$validation -> addError('Запрещено использовать в логине слишком много дефисов!');
					}

					if (!empty($logs)){
						// Проверка логина или ника на существование
						$reglogin = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array(strtolower($logs), strtolower($logs)));
						$validation -> addRule('empty', $reglogin, 'Пользователь с данным логином или ником уже зарегистрирован!');

						// Проверка логина в черном списке
						$blacklogin = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(2, strtolower($logs)));
						$validation -> addRule('empty', $blacklogin, 'Выбранный вами логин занесен в черный список!');
					}

					if (!empty($config['regmail']) && !empty($meil)){
						// Проверка email на существование
						$regmail = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_email`=? LIMIT 1;", array($meil));
						$validation -> addRule('empty', $regmail, 'Указанный вами адрес e-mail уже используется в системе!');

						// Проверка домена от email в черном списке
						$blackdomain = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(3, $domain));
						$validation -> addRule('empty', $blackdomain, 'Домен от вашего адреса email занесен в черный список!');

						// Проверка email в черном списке
						$blackmail = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(1, $meil));
						$validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');
					}

					// Проверка пригласительного ключа
					if (!empty($config['invite'])){
						$invitation = DB::run() -> querySingle("SELECT `id` FROM `invite` WHERE `key`=? AND `used`=? LIMIT 1;", array($invite, 0));
						$validation -> addRule('not_empty', $invitation, 'Ключ приглашения недействителен!');
					}

					// Регистрация аккаунта
					if ($validation->run(3)){

						if ($config['regkeys'] == 1 && empty($config['regmail'])) {
							$config['regkeys'] = 0;
						}

						// ------------------------- Уведомление о регистрации на E-mail --------------------------//
						$regmessage = "Добро пожаловать, ".$logs." \nТеперь вы зарегистрированный пользователь сайта ".$config['home']." , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. \nВаши данные для входа на сайт \nЛогин: ".$logs." \nПароль: ".$pars." \n\nСсылка для автоматического входа на сайт: \n".$config['home']."/input.php?login=".$logs."&pass=".$pars." \nНадеемся вам понравится на нашем портале! \nС уважением администрация сайта \nЕсли это письмо попало к вам по ошибке, то просто проигнорируйте его \n\n";

						if ($config['regkeys'] == 1) {
							$registration_key = generate_password();

							echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br />';
							echo 'Мастер-ключ был выслан вам на почтовый ящик: '.$meil.'</span></b><br /><br />';

							$regmessage .= "Внимание! \nДля подтверждения регистрации необходимо в течении 24 часов ввести мастер-ключ! \nВаш мастер-ключ: ".$registration_key." \nВведите его после авторизации на сайте \nИли перейдите по прямой ссылке: \n\n".$config['home']."/pages/key.php?act=inkey&key=".$registration_key." \n\nЕсли в течении 24 часов вы не подтвердите регистрацию, ваш профиль будет автоматически удален";
						}

						if ($config['regkeys'] == 2) {
							echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br /><br />';

							$regmessage .= "Внимание! \nВаш аккаунт будет активирован только после проверки администрацией! \nПроверить статус активации вы сможете после авторизации на сайте";
						}

						// Активация пригласительного ключа
						if (!empty($config['invite'])){
							DB::run() -> query("UPDATE `invite` SET `used`=?, `invited`=? WHERE `key`=? LIMIT 1;", array(1, $logs, $invite));
						}

						// ----------------------------------------------------------------------------------//
						DB::run() -> query("INSERT INTO `users` (`users_login`, `users_pass`, `users_email`, `users_joined`, `users_level`, `users_gender`, `users_themes`, `users_postguest`, `users_postnews`, `users_postprivat`, `users_postforum`, `users_themesforum`, `users_postboard`, `users_point`, `users_money`, `users_timelastlogin`, `users_confirmreg`, `users_confirmregkey`, `users_navigation`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array($logs, md5(md5($pars)), $meil, SITETIME, 107, $gender, 0, $config['bookpost'], $config['postnews'], $config['privatpost'], $config['forumpost'], $config['forumtem'], $config['boardspost'], 0, $config['registermoney'], SITETIME, $config['regkeys'], $registration_key, $config['navigation']));

						// ------------------------------ Уведомление в приват ----------------------------------//
						$textpriv = text_private(1, array('%USERNAME%'=>$logs, '%SITENAME%'=>$config['home']));
						send_private($logs, $config['nickname'], $textpriv);

						if (!empty($config['regmail'])) {
							addmail($meil, 'Регистрация на сайте '.$config['title'], $regmessage);
						}

						// ----------------------------------------------------------------------------------------//
						$_SESSION['reguser'] = 1;
						echo 'Вы удачно зарегистрированы!<br /><br />';

						echo 'Логин: <b>'.$logs.'</b><br />';
						echo 'Пароль: <b>'.$pars.'</b><br /><br />';

						echo 'Теперь вы можете войти<br />';
						echo '<br /><img src="/images/img/open.gif" alt="image" /> ';
						echo '<b><a href="/input.php?login='.$logs.'&amp;pass='.$pars.'">Вход на сайт</a></b><br /><br />';

						echo 'Вы можете сделать закладку для быстрого входа:<br />';
						echo '<span style="color:#ff0000">'.$config['home'].'/input.php?login='.$logs.'&amp;pass='.$pars.'</span><br /><br />';
						echo 'Cкопировать: <br /><input name="avtovhod" size="60" value="'.$config['home'].'/input.php?login='.$logs.'&amp;pass='.$pars.'"/><br /><br />';

						echo 'Если у вас включены cookies, то делать такую закладку не обязательно<br /><br />';


					} else {
						show_error($validation->errors);
					}

					echo '<img src="/images/img/back.gif" alt="image" /> <a href="registration.php">Вернуться</a><br />';
				break;

			default:
				redirect("registration.php");
			endswitch;

		} else {
			show_error('Ошибка! Вы уже регистрировались. Запрещено регистрировать несколько аккаунтов!');
		}
	} else {
		show_error('Вы уже регистрировались, нельзя регистрироваться несколько раз!');
	}
} else {
	show_error('Регистрация временно приостановлена, пожалуйста зайдите позже!');
}

include_once ('../themes/footer.php');
?>
