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

if (is_admin(array(101, 102, 103))) {
	show_title('Администрация сайта');
	############################################################################################
	##                                     Вывод администрации                                ##
	############################################################################################
	$queryadmin = DB::run() -> query("SELECT users_login, users_level FROM users WHERE users_level>=? AND users_level<=?;", array(101, 105));
	$arradmin = $queryadmin -> fetchAll();
	$total = count($arradmin);

	if ($total > 0) {
		foreach($arradmin as $value) {
			echo '<img src="/images/img/user.gif" alt="image" /> <b>'.profile($value['users_login']).'</b>  ('.user_status($value['users_level']).') '.user_online($value['users_login']).'<br />';

			if (is_admin(array(101))) {
				echo '<img src="/images/img/edit.gif" alt="image" /> <a href="users.php?act=edit&amp;uz='.$value['users_login'].'">Изменить</a><br />';
			}
		}
		echo '<br />Всего в администрации: <b>'.$total.'</b><br /><br />';

	} else {
		show_error('Администрации еще нет!');
	}

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
