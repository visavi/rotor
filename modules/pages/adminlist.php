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

show_title('Администрация сайта');
############################################################################################
##                                     Вывод администрации                                ##
############################################################################################
$queryadmin = DB::run() -> query("SELECT `users_login`, `users_level` FROM `users` WHERE `users_level`>=? AND `users_level`<=?;", array(101, 105));
$arradmin = $queryadmin -> fetchAll();
$total = count($arradmin);

if ($total > 0) {
	foreach($arradmin as $value) {
		echo user_gender($value['users_login']).' <b>'.profile($value['users_login']).'</b>  ('.user_status($value['users_level']).') '.user_online($value['users_login']).'<br />';
	}

	echo '<br />Всего в администрации: <b>'.$total.'</b><br /><br />';
	############################################################################################
	##                                     Быстрая почта                                      ##
	############################################################################################
	if (is_user()) {
		echo '<big><b>Быстрая почта</b></big><br /><br />';

		echo '<div class="form">';
		echo '<form method="post" action="private.php?act=send&amp;uid='.$_SESSION['token'].'">';

		echo 'Выберите адресат:<br /><select name="uz">';

		foreach($arradmin as $value) {
			echo '<option value="'.$value['users_login'].'"> '.nickname($value['users_login']).' </option>';
		}
		echo '</select><br />';
		echo 'Сообщение:<br />';
		echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';

		if ($udata['users_point'] < $config['privatprotect']) {
			echo 'Проверочный код:<br /> ';
			echo '<img src="/gallery/protect.php" alt="" /><br />';
			echo '<input name="provkod" size="6" maxlength="6" /><br />';
		}

		echo '<input value="Отправить" type="submit" /></form></div><br />';
	}
} else {
	show_error('Администрации еще нет!');
}

include_once ('../themes/footer.php');
?>
