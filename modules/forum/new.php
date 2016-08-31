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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'themes';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                     Последние темы                                     ##
############################################################################################
case 'themes':
	show_title('Список последних тем');

	$total = DB::run() -> querySingle("SELECT count(*) FROM topics;");

	if ($total > 0) {
		if ($total > 100) {
			$total = 100;
		}
		if ($start >= $total) {
			$start = last_page($total, $config['forumtem']);
		}

		$querytopic = DB::run() -> query("SELECT `topics`.*, `forums_title` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";");
		$topics = $querytopic->fetchAll();

		render('forum/new_themes', array('topics' => $topics));

		page_strnavigation('new.php?act=themes&amp;', $config['forumtem'], $start, $total);
	} else {
		show_error('Созданных тем еще нет!');
	}
break;

############################################################################################
##                                  Последние сообщения                                   ##
############################################################################################
case 'posts':
	show_title('Список последних сообщений');

	$total = DB::run() -> querySingle("SELECT count(*) FROM `posts`;");

	if ($total > 0) {
		if ($total > 100) {
			$total = 100;
		}
		if ($start >= $total) {
			$start = last_page($total, $config['forumpost']);
		}

		$querypost = DB::run() -> query("SELECT `posts`.*, `topics_title`, `topics_posts` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` ORDER BY `posts_time` DESC LIMIT ".$start.", ".$config['forumpost'].";");
		$posts = $querypost->fetchAll();

		render('forum/new_posts', array('posts' => $posts, 'start' => $start));

		page_strnavigation('new.php?act=posts&amp;', $config['forumpost'], $start, $total);
	} else {
		show_error('Сообщений еще нет!');
	}
break;

default:
	redirect("new.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
