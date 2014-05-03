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

show_title('Правила сайта');

$rules = DB::run() -> queryFetch("SELECT * FROM `rules`;");

if (!empty($rules)) {
	$rules['rules_text'] = str_replace(array('%SITENAME%', '%MAXBAN%'), array($config['title'], round($config['maxbantime'] / 1440)), $rules['rules_text']);

	echo bb_code($rules['rules_text']).'<br />';
} else {
	show_error('Правила сайта еще не установлены!');
}

include_once ('../themes/footer.php');
?>
