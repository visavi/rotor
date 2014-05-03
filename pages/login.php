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

$cooklog = (isset($_COOKIE['cooklog'])) ? check($_COOKIE['cooklog']): '';

show_title('Авторизация');

if (!is_user()){

	echo '<div class="form">';
	echo '<form method="post" action="/input.php">';
	echo 'Логин или ник:<br /><input name="login" value="'.$cooklog.'" maxlength="20" /><br />';
	echo 'Пароль:<br /><input name="pass" type="password" maxlength="20" /><br />';
	echo 'Запомнить меня:';
	echo '<input name="cookietrue" type="checkbox" value="1" checked="checked" /><br />';

	echo '<input value="Войти" type="submit" /></form></div><br />';

	echo '<a href="registration.php">Регистрация</a><br />';
	echo '<a href="/mail/lostpassword.php">Забыли пароль?</a><br /><br />';

	echo 'Вы можете сделать закладку для быстрого входа, она будет иметь вид:<br />';
	echo '<span style="color:#ff0000">'.$config['home'].'/input.php?login=ВАШ_ЛОГИН&amp;pass=ВАШ_ПАРОЛЬ</span><br /><br />';

} else {
	redirect($config['home'].'/index.php');
}

include_once ('../themes/footer.php');
?>
