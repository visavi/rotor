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

show_title('Онлайн пользователей');

$daytime = date("d", SITETIME);
$montime = date("d.m", SITETIME);

echo '<div class="b"><b>Пользователи онлайн:</b></div>';

$allonline = allonline();
$total = count($allonline);

if ($total > 0) {
	foreach($allonline as $key => $value) {
		$comma = (empty($key)) ? '' : ', ';
		echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
	}

	echo '<br />Всего пользователей: '.$total.' чел.<br /><br />';
} else {
	show_error('Зарегистированных пользователей нет!');
}

echo '<div class="b"><b>Поздравляем именинников:</b></div>';

$queryuser = DB::run() -> query("SELECT `users_login` FROM `users` WHERE substr(`users_birthday`,1,5)=?;", array($montime));
$arrhappy = $queryuser -> fetchAll(PDO::FETCH_COLUMN);
$total = count($arrhappy);

if ($total > 0) {
	foreach($arrhappy as $key => $value) {
		$comma = (empty($key)) ? '' : ', ';
		echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
	}

	echo '<br />Всего именниников: '.$total.' чел.<br /><br />';
} else {
	show_error('Сегодня именинников нет!');
}
// ---------------------------------------------------------------------------------//
echo '<div class="b"><b>Приветствуем новичков:</b></div>';

$queryuser = DB::run() -> query("SELECT `users_login` FROM `users` WHERE `users_joined`>?;", array(SITETIME-86400));
$arrnovice = $queryuser -> fetchAll(PDO::FETCH_COLUMN);
$total = count($arrnovice);

if ($total > 0) {
	foreach($arrnovice as $key => $value) {
		$comma = (empty($key)) ? '' : ', ';
		echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
	}

	echo '<br />Всего новичков: '.$total.' чел.<br /><br />';
} else {
	show_error('Новичков пока нет!');
}

echo '<img src="/images/img/users.gif" alt="image" /> <a href="who.php">Kто-где?</a><br />';

include_once ('../themes/footer.php');
?>
