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

if ($config['closedsite'] == 2) {
	echo '<center><br /><br /><h2>Внимание! Сайт закрыт по техническим причинам</h2></center>';

	echo 'Администрация сайта приносит вам свои извинения за возможные неудобства.<br />';
	echo 'Работа сайта возможно возобновится в ближайшее время.<br /><br />';
} else {
	redirect($config['home'].'/index.php');
}

include_once ('../themes/footer.php');
?>
