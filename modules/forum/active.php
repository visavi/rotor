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
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'themes':
	show_title('Список всех тем '.$uz);

	$total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `topics_author`=?;", array($uz));

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['forumtem']);
		}

		$querytopic = DB::run() -> query("SELECT `topics`.*, `forums_title` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_author`=? ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($uz));
		$topics = $querytopic->fetchAll();

		render('forum/active_themes', array('topics' => $topics));

		page_strnavigation('active.php?act=themes&amp;uz='.$uz.'&amp;', $config['forumtem'], $start, $total);
	} else {
		show_error('Созданных тем не найдено!');
	}
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'posts':
	show_title('Список всех сообщений '.$uz);

	$total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_user`=?;", array($uz));

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['forumpost']);
		}

		$querypost = DB::run() -> query("SELECT `posts`.*, `topics_title` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_user`=? ORDER BY `posts_time` DESC LIMIT ".$start.", ".$config['forumpost'].";", array($uz));
		$posts = $querypost->fetchAll();

		render('forum/active_posts', array('posts' => $posts, 'user' => $uz, 'start' => $start));

		page_strnavigation('active.php?act=posts&amp;uz='.$uz.'&amp;', $config['forumpost'], $start, $total);
	} else {
		show_error('Сообщения не найдены!');
	}
break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

	if (is_admin()) {
		if ($uid == $_SESSION['token']) {
			$topics = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($id));

			if (!empty($topics)) {
				DB::run() -> query("DELETE FROM `posts` WHERE `posts_id`=? AND `posts_topics_id`=?;", array($id, $topics['posts_topics_id']));
				DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`-? WHERE `topics_id`=?;", array(1, $topics['posts_topics_id']));
				DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`-? WHERE `forums_id`=?;", array(1, $topics['posts_forums_id']));

				notice('Сообщение успешно удалено!');
				redirect("active.php?act=posts&uz=$uz&start=$start");

			} else {
				show_error('Ошибка! Данного сообщения не существует!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_error('Ошибка! Удалять сообщения могут только модераторы!');
	}

	render('includes/back', array('link' => 'active.php?act=posts&amp;uz='.$uz.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

default:
	redirect("active.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
