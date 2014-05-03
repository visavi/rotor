<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
	header('Location: /index.php');
	exit;
}

echo '<div style="text-align:center">';

include_once (DATADIR.'/main/reklama_head.dat');

echo show_advertadmin();
echo show_sponsors();
echo show_advertuser($config['rekusershow']);

echo '</div>';
?>
