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
$fid = (isset($_GET['fid'])) ? abs(intval($_GET['fid'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Форум '.$config['title']);

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	if (!empty($fid)) {
		$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));

		if (!empty($forums)) {

			$page = floor(1 + $start / $config['forumtem']);
			$config['header'] = $forums['forums_title'];
			$config['newtitle'] = $forums['forums_title'].' (Стр. '.$page.')';

			if (!empty($forums['forums_parent'])) {
				$forums['subparent'] = DB::run() -> queryFetch("SELECT `forums_id`, `forums_title` FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($forums['forums_parent']));
			}

			$querysub = DB::run() -> query("SELECT * FROM `forums` WHERE `forums_parent`=? ORDER BY `forums_order` ASC;", array($fid));
			$forums['subforums'] = $querysub -> fetchAll();

			$total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `topics_forums_id`=?;", array($fid));

			if ($total > 0 && $start >= $total) {
				$start = last_page($total, $config['forumtem']);
			}

			$querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `topics_forums_id`=? ORDER BY `topics_locked` DESC, `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($fid));
			$forums['topics'] = $querytopic->fetchAll();

			render('forum/forum', array('forums' => $forums, 'fid' => $fid, 'start' => $start, 'total' => $total));

		} else {
			show_error('Ошибка! Данного раздела не существует!');
		}
	} else {
		redirect("index.php");

	}
break;

############################################################################################
##                               Подготовка к созданию темы                               ##
############################################################################################
case 'addtheme':

	$config['newtitle'] = 'Создание новой темы';

	if (is_user()) {
		$queryforum = DB::run() -> query("SELECT `forums_id`, `forums_parent`, `forums_title` FROM `forums` WHERE `forums_closed`=? ORDER BY `forums_order` ASC;", array(0));
		$forums = $queryforum -> fetchAll();

		if (count($forums) > 0) {

			$output = array();

			foreach ($forums as $row) {
				$i = $row['forums_id'];
				$p = $row['forums_parent'];
				$output[$p][$i] = $row;
			}

			render('forum/forum_add', array('forums' => $output, 'fid' => $fid));

		} else {
			show_error('Разделы форума еще не созданы!');
		}
	} else {
		show_login('Вы не авторизованы, для создания новой темы, необходимо');
	}

	render('includes/back', array('link' => 'forum.php?fid='.$fid, 'title' => 'Вернуться'));
break;

############################################################################################
##                                     Cоздание темы                                      ##
############################################################################################
case 'add':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$fid = (isset($_POST['fid'])) ? abs(intval($_POST['fid'])) : 0;
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

	if (is_user()) {

		$forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($fid));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('not_empty', $forums, 'Раздела для новой темы не существует!')
			-> addRule('empty', $forums['forums_closed'], 'В данном разделе запрещено создавать темы!')
			-> addRule('equal', array(is_quarantine($log), true), 'Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!')
			-> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' сек!')
			-> addRule('string', $title, 'Слишком длинный или короткий заголовок темы!', true, 5, 50)
			-> addRule('string', $msg, 'Слишком длинное или короткое сообщение!', true, 5, $config['forumtextlength']);

		/* Сделать проверку поиска похожей темы */

		if ($validation->run(1)) {

			$title = antimat($title);
			$msg = antimat($msg);

			DB::run() -> query("UPDATE `users` SET `users_allforum`=`users_allforum`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

			DB::run() -> query("INSERT INTO `topics` (`topics_forums_id`, `topics_title`, `topics_author`, `topics_posts`, `topics_last_user`, `topics_last_time`) VALUES (?, ?, ?, ?, ?, ?);", array($fid, $title, $log, 1, $log, SITETIME));

			$lastid = DB::run() -> lastInsertId();

			DB::run() -> query("INSERT INTO `posts` (`posts_topics_id`, `posts_forums_id`, `posts_user`, `posts_text`, `posts_time`, `posts_ip`, `posts_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($lastid, $fid, $log, $msg, SITETIME, $ip, $brow));

			DB::run() -> query("UPDATE `forums` SET `forums_topics`=`forums_topics`+1, `forums_posts`=`forums_posts`+1, `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?", array($lastid, $title, $log, SITETIME, $fid));
			// Обновление родительского форума
			if ($forums['forums_parent'] > 0) {
				DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?", array($lastid, $title, $log, SITETIME, $forums['forums_parent']));
			}

			notice('Новая тема успешно создана!');
			redirect("topic.php?tid=$lastid");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, для создания новой темы, необходимо');
	}

	render('includes/back', array('link' => 'forum.php?fid='.$fid, 'title' => 'К темам'));
break;

default:
	redirect("index.php");
endswitch;

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
