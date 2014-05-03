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

show_title('Мои данные');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Изменение e-mail                                    ##
############################################################################################
case 'index':

	echo '<img src="/images/img/document.gif" alt="image" /> ';
	echo '<a href="user.php">Моя анкета</a> / ';
	echo '<a href="profile.php">Мой профиль</a> / ';
	echo '<b>Мои данные</b> / ';
	echo '<a href="setting.php">Настройки</a><hr />';

	echo '<b><big>Изменение E-mail</big></b><br />';
	echo '<div class="form">';
	echo '<form method="post" action="account.php?act=changemail&amp;uid='.$_SESSION['token'].'">';
	echo 'Е-mail:<br />';
	echo '<input name="meil" maxlength="50" value="'.$udata['users_email'].'" /><br />';
	echo 'Текущий пароль:<br />';
	echo '<input name="provpass" type="password" maxlength="20" /><br />';
	echo '<input value="Изменить" type="submit" /></form></div><br />';

	############################################################################################
	##                                Изменение ника                                          ##
	############################################################################################
	echo '<b><big>Изменение ника</big></b><br />';

	if ($udata['users_point'] >= $config['editnickpoint']) {
		echo '<div class="form">';
		echo '<form method="post" action="account.php?act=editnick&amp;uid='.$_SESSION['token'].'">';
		echo 'Ваш ник:<br />';
		echo '<input name="nickname" maxlength="20" value="'.$udata['users_nickname'].'" />';
		echo '<input value="Изменить" type="submit" /></form></div><br />';
	} else {
		show_error('Изменять ник могут пользователи у которых более '.points($config['editnickpoint']).'!');
	}
	############################################################################################
	##                        Изменение персонального статуса                                 ##
	############################################################################################
	if (!empty($config['editstatus'])) {
		echo '<b><big>Изменение статуса</big></b><br />';

		if ($udata['users_point'] >= $config['editstatuspoint']) {
			echo '<div class="form">';
			echo '<form method="post" action="account.php?act=editstatus&amp;uid='.$_SESSION['token'].'">';
			echo 'Персональный статус:<br />';
			echo '<input name="status" maxlength="20" value="'.$udata['users_status'].'" />';
			echo '<input value="Изменить" type="submit" /></form>';

			if (!empty($config['editstatusmoney'])) {
				echo '<br /><i>Стоимость: '.moneys($config['editstatusmoney']).'</i>';
			}

			echo '</div><br />';
		} else {
			show_error('Изменять статус могут пользователи у которых более '.points($config['editstatuspoint']).'!');
		}
	}
	############################################################################################
	##                                  Секретный вопрос                                      ##
	############################################################################################
	echo '<b><big>Секретный вопрос</big></b><br />';
	echo '<div class="form">';
	echo '<form method="post" action="account.php?act=editsec&amp;uid='.$_SESSION['token'].'">';
	echo 'Секретный вопрос:<br />';
	echo '<input name="secquest" maxlength="50" value="'.$udata['users_secquest'].'" /><br />';
	echo 'Ответ на вопрос:<br /><input name="secanswer" maxlength="30" /><br />';
	echo 'Текущий пароль:<br /><input name="provpass" type="password" maxlength="20" /><br />';
	echo '<input value="Изменить" type="submit" /></form></div><br />';

	############################################################################################
	##                                    Изменение пароля                                    ##
	############################################################################################
	echo '<b><big>Изменение пароля</big></b><br />';

	echo '<div class="form">';
	echo '<form method="post" action="account.php?act=editpass&amp;uid='.$_SESSION['token'].'">';
	echo 'Новый пароль:<br /><input name="newpass" maxlength="20" /><br />';
	echo 'Повторите пароль:<br /><input name="newpass2" maxlength="20" /><br />';
	echo 'Текущий пароль:<br /><input name="oldpass" type="password" maxlength="20" /><br />';
	echo '<input value="Изменить" type="submit" /></form></div><br />';

	############################################################################################
	##                                    API-ключ                                            ##
	############################################################################################
	echo '<b><big>Ваш API-ключ</big></b><br />';

	if(empty($udata['users_apikey'])) {
		echo '<div class="form">';
		echo '<form method="post" action="account.php?act=apikey&amp;uid='.$_SESSION['token'].'">';
		echo '<input value="Получить ключ" type="submit" /></form></div><br />';
	} else {
		echo '<div class="form">';
		echo '<form method="post" action="account.php?act=apikey&amp;uid='.$_SESSION['token'].'">';
		echo 'Ключ: <strong>'.$udata['users_apikey'].'</strong><br />';
		echo '<input value="Изменить ключ" type="submit" /></form></div><br />';
	}
break;

############################################################################################
##                                     Изменение e-mail                                   ##
############################################################################################
case 'changemail':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$meil = (isset($_POST['meil'])) ? strtolower(check($_POST['meil'])) : '';
	$provpass = (isset($_POST['provpass'])) ? check($_POST['provpass']) : '';

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_equal', array($meil, $udata['users_email']), 'Новый адрес email должен отличаться от текущего!')
		-> addRule('email', $meil, 'Неправильный адрес e-mail, необходим формат name@site.domen!', true)
		-> addRule('equal', array(md5(md5($provpass)), $udata['users_pass']), 'Введенный пароль не совпадает с данными в профиле!');

	$regmail = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_email`=? LIMIT 1;", array($meil));
	$validation -> addRule('empty', $regmail, 'Указанный вами адрес e-mail уже используется в системе!');

	// Проверка email в черном списке
	$blackmail = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(1, $meil));
	$validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');

	DB::run() -> query("DELETE FROM `changemail` WHERE `change_time`<?;", array(SITETIME));
	$changemail = DB::run() -> querySingle("SELECT `change_id` FROM `changemail` WHERE `change_user`=? LIMIT 1;", array($log));
	$validation -> addRule('empty', $changemail, 'Вы уже отправили код подтверждения на новый адрес почты!');

	if ($validation->run(1)) {

		$genkey = generate_password(rand(15,20));

		addmail($meil, "Изменение адреса электронной почты на сайте ".$config['title'], "Здравствуйте, ".nickname($log)." \nВами была произведена операция по изменению адреса электронной почты \n\nДля того, чтобы изменить e-mail, необходимо подтвердить новый адрес почты \nПерейдите по данной ссылке: \n\n".$config['home']."/pages/account.php?act=editmail&key=".$genkey." \n\nСсылка будет дейстительной в течении суток до ".date('j.m.y / H:i', SITETIME + 86400).", для изменения адреса необходимо быть авторизованным на сайте \nЕсли это сообщение попало к вам по ошибке или вы не собираетесь менять e-mail, то просто проигнорируйте данное письмо");

		DB::run() -> query("INSERT INTO `changemail` (`change_user`, `change_mail`, `change_key`, `change_time`) VALUES (?, ?, ?, ?);", array($log, $meil, $genkey, SITETIME + 86400));

		notice('На новый адрес почты отправлено письмо для подтверждения!');
		redirect("account.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение e-mail                                   ##
############################################################################################
case 'editmail':

	$key = (isset($_GET['key'])) ? check(strval($_GET['key'])) : '';

	DB::run() -> query("DELETE FROM `changemail` WHERE `change_time`<?;", array(SITETIME));
	$armail = DB::run() -> queryFetch("SELECT * FROM `changemail` WHERE `change_key`=? AND `change_user`=? LIMIT 1;", array($key, $log));

	$validation = new Validation;

	$validation -> addRule('not_empty', $key, 'Вы не ввели код изменения электронной почты!')
		-> addRule('not_empty', $armail, 'Данный код изменения электронной почты не найден в списке!')
		-> addRule('not_equal', array($armail['change_mail'], $udata['users_email']), 'Новый адрес email должен отличаться от текущего!')
		-> addRule('email', $armail['change_mail'], 'Неправильный адрес e-mail, необходим формат name@site.domen!', true);

	$regmail = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_email`=? LIMIT 1;", array($armail['change_mail']));
	$validation -> addRule('empty', $regmail, 'Указанный вами адрес e-mail уже используется в системе!');

	$blackmail = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(1, $armail['change_mail']));
	$validation -> addRule('empty', $blackmail, 'Указанный вами адрес e-mail занесен в черный список!');

	if ($validation->run(1)) {

		DB::run() -> query("UPDATE `users` SET `users_email`=? WHERE `users_login`=? LIMIT 1;", array($armail['change_mail'], $log));
		DB::run() -> query("DELETE FROM `changemail` WHERE `change_key`=? AND `change_user`=? LIMIT 1;", array($key, $log));

		notice('Адрес электронной почты успешно изменен!');
		redirect("account.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                   Изменение статуса                                    ##
############################################################################################
case 'editstatus':
	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$status = (isset($_POST['status'])) ? check($_POST['status']) : '';
	$cost = (!empty($status)) ? $config['editstatusmoney'] : 0;

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_empty', $config['editstatus'], 'Изменение статуса запрещено администрацией сайта!')
		-> addRule('empty', $udata['users_ban'], 'Для изменения статуса у вас не должно быть нарушений!')
		-> addRule('not_equal', array($status, $udata['users_status']), 'Новый статус должен отличаться от текущего!')
		-> addRule('max', array($udata['users_point'], $config['editstatuspoint']), 'У вас недостаточно актива для изменения статуса!')
		-> addRule('max', array($udata['users_money'], $cost), 'У вас недостаточно денег для изменения статуса!')
		-> addRule('string', $status, 'Слишком длинный или короткий статус!', false, 3, 20);

	if (!empty($status)) {
		$checkstatus = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE lower(`users_status`)=? LIMIT 1;", array(utf_lower($status)));
		$validation -> addRule('empty', $checkstatus, 'Выбранный вами статус уже используется на сайте!');
	}

	if ($validation->run(1)) {

		DB::run() -> query("UPDATE `users` SET `users_status`=?, `users_money`=`users_money`-? WHERE `users_login`=? LIMIT 1;", array($status, $cost, $log));
		save_title();

		notice('Ваш статус успешно изменен!');
		redirect("account.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение ника                                     ##
############################################################################################
case 'editnick':
	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$nickname = (isset($_POST['nickname'])) ? check($_POST['nickname']) : '';

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('max', array($udata['users_point'], $config['editnickpoint']), 'У вас недостаточно актива для изменения ника!')
		-> addRule('min', array($udata['users_timenickname'], SITETIME), 'Изменять ник можно не чаще чем 1 раз в сутки!')
		-> addRule('regex', array($nickname, '|^[0-9a-zA-Zа-яА-ЯЁё_\.\-\s]+$|u'), 'Разрешены символы русского, латинского алфавита и цифры!')
		-> addRule('string', $nickname, 'Слишком длинный или короткий ник!', false, 3, 20)
		-> addRule('not_equal', array($nickname, $udata['users_nickname']), 'Новый ник должен отличаться от текущего!');

	if (!empty($nickname)) {
		$reglogin = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE lower(`users_login`)=? LIMIT 1;", array(utf_lower($nickname)));
		$validation -> addRule('empty', $reglogin, 'Выбранный вами ник используется кем-то в качестве логина!');

		$regnick = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE lower(`users_nickname`)=? LIMIT 1;", array(utf_lower($nickname)));
		$validation -> addRule('empty', $regnick, 'Выбранный вами ник уже используется на сайте!');

		$blacklogin = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(2, utf_lower($nickname)));
		$validation -> addRule('empty', $blacklogin, 'Выбранный вами ник занесен в черный список!');
	}

	if ($validation->run(1)) {

		DB::run() -> query("UPDATE `users` SET `users_nickname`=?, `users_timenickname`=? WHERE `users_login`=? LIMIT 1;", array($nickname, SITETIME + 86400, $log));
		save_nickname();

		notice('Ваш ник успешно изменен!');
		redirect("account.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                    Изменение вопроса                                   ##
############################################################################################
case 'editsec':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$secquest = (isset($_POST['secquest'])) ? check($_POST['secquest']) : '';
	$secanswer = (isset($_POST['secanswer'])) ? check($_POST['secanswer']) : '';
	$provpass = (isset($_POST['provpass'])) ? check($_POST['provpass']) : '';

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('equal', array(md5(md5($provpass)), $udata['users_pass']), 'Введенный пароль не совпадает с данными в профиле!')
		-> addRule('not_equal', array($secquest, $udata['users_secquest']), 'Новый секретный вопрос должен отличаться от текущего!')
		-> addRule('string', $secquest, 'Слишком длинный или короткий секретный вопрос!', false, 3, 50);

	if (!empty($secquest)) {
		$validation -> addRule('string', $secanswer, 'Слишком длинный или короткий секретный ответ!', true, 3, 30);
		$secanswer = md5(md5($secanswer));
	} else {
		$secanswer = '';
	}

	if ($validation->run(1)) {

		DB::run() -> query("UPDATE `users` SET `users_secquest`=?, `users_secanswer`=? WHERE `users_login`=? LIMIT 1;", array($secquest, $secanswer, $log));

		notice('Секретный вопрос и ответ успешно изменены!');
		redirect("account.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение пароля                                   ##
############################################################################################
case 'editpass':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$newpass = (isset($_POST['newpass'])) ? check($_POST['newpass']) : '';
	$newpass2 = (isset($_POST['newpass2'])) ? check($_POST['newpass2']) : '';
	$oldpass = (isset($_POST['oldpass'])) ? check($_POST['oldpass']) : '';

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('equal', array(md5(md5($oldpass)), $udata['users_pass']), 'Введенный пароль не совпадает с данными в профиле!')
		-> addRule('equal', array($newpass, $newpass2), 'Новые пароли не одинаковые!')
		-> addRule('string', $newpass, 'Слишком длинный или короткий новый пароль!', true, 6, 20)
		-> addRule('regex', array($newpass, '|^[a-z0-9\-]+$|i'), 'Недопустимые символы в пароле, разрешены знаки латинского алфавита, цифры и дефис!', true)
		-> addRule('not_equal', array($log, $newpass), 'Пароль и логин должны отличаться друг от друга!');

	if (ctype_digit($newpass)) {
		$validation -> addError('Запрещен пароль состоящий только из цифр, используйте буквы!');
	}

	if ($validation->run(1)) {

		DB::run() -> query("UPDATE `users` SET `users_pass`=? WHERE `users_login`=? LIMIT 1;", array(md5(md5($newpass)), $log));

		if (!empty($udata['users_email'])){
			addmail($udata['users_email'], "Изменение пароля на сайте ".$config['title'], "Здравствуйте, ".nickname($log)." \nВами была произведена операция по изменению пароля \n\nВаш новый пароль: ".$newpass." \nСохраните его в надежном месте \n\nДанные инициализации: \nIP: ".$ip." \nБраузер: ".$brow." \nВремя: ".date('j.m.y / H:i', SITETIME));
		}

		unset($_SESSION['log']);
		unset($_SESSION['par']);

		notice('Пароль успешно изменен!');
		redirect("login.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

############################################################################################
##                                     Генерация ключа                                    ##
############################################################################################
case 'apikey':
	$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

	if ($uid == $_SESSION['token']) {

		$key = generate_password();

		DB::run() -> query("UPDATE `users` SET `users_apikey`=? WHERE `users_login`=?;", array(md5($log.$key), $log));

		notice('Новый ключ успешно сгенерирован!');
		redirect("account.php");
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="account.php">Вернуться</a><br />';
break;

default:
	redirect("account.php");
endswitch;

} else {
	show_login('Вы не авторизованы, чтобы изменять свои данные, необходимо');
}

include_once ('../themes/footer.php');
?>
