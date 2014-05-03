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

show_title('Статусы пользователей');

echo 'В зависимости от вашей активности на сайте вы получаете определенный статус<br />';
echo 'При наборе определенного количества актива ваш статус меняется на вышестоящий<br />';
echo 'Актив - это сумма постов на форуме, гостевой, в комментариях и пр.<br /><br />';


$querystatus = DB::run()->query("SELECT * FROM `status` ORDER BY `status_topoint` DESC;");
$status = $querystatus->fetchAll();
$total = count($status);

if ($total>0){
	foreach ($status as $statval){

		echo '<img src="/images/img/user.gif" alt="image" /> ';

		if (empty($statval['status_color'])){
			echo '<b>'.$statval['status_name'].'</b> — '.points($statval['status_topoint']).'<br />';
		} else {
			echo '<b><span style="color:'.$statval['status_color'].'">'.$statval['status_name'].'</span></b> — '.points($statval['status_topoint']).'<br />';
		}
	}

	echo '<br />';
} else {
	show_error('Статусы еще не назначены!');
}

echo 'Некоторые статусы могут быть выделены определенными цветами<br />';
echo 'Самым активным юзерам администрация сайта может назначать особые статусы<br /><br />';

include_once ('../themes/footer.php');
?>
