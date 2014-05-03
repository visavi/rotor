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
$tid = (isset($_GET['tid'])) ? abs(intval($_GET['tid'])) : 0;

show_title('Мои закладки');

if (is_user()) {

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	$total = DB::run() -> querySingle("SELECT count(*) FROM `bookmarks` WHERE `book_user`=?;", array($log));

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['forumtem']);
		}

		$querytopic = DB::run() -> query("SELECT `topics`.*, `bookmarks`.* FROM `bookmarks` LEFT JOIN `topics` ON `bookmarks`.`book_topic`=`topics`.`topics_id` WHERE `book_user`=?  ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($log));
		$topics = $querytopic->fetchAll();

		render('forum/bookmark', array('topics' => $topics, 'start' => $start));

		page_strnavigation('bookmark.php?', $config['forumtem'], $start, $total);

	} else {
		show_error('Закладок еще нет!');
	}
break;

############################################################################################
##                                Добавление закладок                                     ##
############################################################################################
case 'add':
	$uid = check($_GET['uid']);

	$topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
	if (!empty($topic)) {
		if (empty($topic['topics_closed'])) {
			$bookmark = DB::run() -> querySingle("SELECT `book_id` FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($tid, $log));
			if (empty($bookmark)) {
				DB::run() -> query("INSERT INTO `bookmarks` (`book_user`, `book_topic`, `book_forum`, `book_posts`) VALUES (?, ?, ?, ?);", array($log, $tid, $topic['topics_forums_id'], $topic['topics_posts']));

				notice('Тема успешно добавлена в закладки!');
				redirect("topic.php?tid=$tid&start=$start");

			} else {
				show_error('Ошибка! Данная тема уже имеется в закладках!');
			}
		} else {
			show_error('Ошибка! Нельзя добавлять в закладки закрытую тему!');
		}
	} else {
		show_error('Ошибка! Данной темы не существует!');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                 Удаление закладок                                      ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($del)) {
			$del = implode(',', $del);

			DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_id` IN (".$del.") AND `book_user`=?;", array($log));

			notice('Выбранные темы успешно удалены из закладок!');
			redirect("bookmark.php?start=$start");

		} else {
			show_error('Ошибка! Отсутствуют выбранные закладки!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	render('includes/back', array('link' => 'bookmark.php?start='.$start, 'title' => 'Вернуться'));
break;

############################################################################################
##                                 Удаление закладок                                      ##
############################################################################################
case 'remove':

	$uid = check($_GET['uid']);

	if ($uid == $_SESSION['token']) {
		if (!empty($tid)) {
			$bookmark = DB::run() -> querySingle("SELECT `book_id` FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($tid, $log));
			if (!empty($bookmark)) {
				DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=?;", array($tid, $log));

				notice('Тема успешно удалена из закладок!');
				redirect("topic.php?tid=$tid&start=$start");

			} else {
				show_error('Ошибка! Данной темы нет в закладках!');
			}
		} else {
			show_error('Ошибка! Отсутствуют выбранные закладки!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	render('includes/back', array('link' => 'topic.php?tid='.$tid.'start='.$start, 'title' => 'Вернуться'));
break;

default:
	redirect("bookmark.php");
endswitch;

} else {
	show_login('Вы не авторизованы, для управления закладками, необходимо');
}

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
