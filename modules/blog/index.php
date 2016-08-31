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
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

show_title('Блоги');
$config['newtitle'] = 'Блоги - Список разделов';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	$queryblog = DB::run() -> query("SELECT *, (SELECT COUNT(*) FROM `blogs` WHERE `blogs`.`blogs_cats_id` = `catsblog`.`cats_id` AND `blogs`.`blogs_time` > ?) AS `new` FROM `catsblog` ORDER BY `cats_order` ASC;", array(SITETIME-86400 * 3));

	$blogs = $queryblog -> fetchAll();

	if (count($blogs) > 0) {

		render('blog/index', array('blogs' => $blogs));

	} else {
		show_error('Разделы блогов еще не созданы!');
	}
break;

default:
	redirect("index.php");
endswitch;

include_once ('../themes/footer.php');
?>
