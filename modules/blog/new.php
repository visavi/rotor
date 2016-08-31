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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'blogs';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'blogs':
	show_title('Список новых статей');
	//$config['header'] = array('site.png', 'Список новых статей2');

	$total = DB::run() -> querySingle("SELECT count(*) FROM `blogs`;");

	if ($total > 0) {
		if ($total > 100) {
			$total = 100;
		}
		if ($start >= $total) {
			$start = last_page($total, $config['blogpost']);
		}

		$queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`blogs_cats_id`=`catsblog`.`cats_id` ORDER BY `blogs_time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
		$blogs = $queryblog->fetchAll();

		render('blog/new_blogs', array('blogs' => $blogs));

		page_strnavigation('new.php?act=blogs&amp;', $config['blogpost'], $start, $total);
	} else {
		show_error('Опубликованных статей еще нет!');
	}
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'comments':
	show_title('Список последних комментариев');

	$total = DB::run() -> querySingle("SELECT count(*) FROM `commblog`;");

	if ($total > 0) {
		if ($total > 100) {
			$total = 100;
		}
		if ($start >= $total) {
			$start = last_page($total, $config['blogpost']);
		}

		$querycomment = DB::run() -> query("SELECT `commblog`.*, `blogs_title`, `blogs_comments` FROM `commblog` LEFT JOIN `blogs` ON `commblog`.`commblog_blog`=`blogs`.`blogs_id` ORDER BY `commblog_time` DESC LIMIT ".$start.", ".$config['blogpost'].";");
		$comments = $querycomment->fetchAll();

		render('blog/new_comments', array('comments' => $comments));

		page_strnavigation('new.php?act=comments&amp;', $config['blogpost'], $start, $total);
	} else {
		show_error('Комментарии не найдены!');
	}
break;

default:
	redirect("new.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'Категории', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
