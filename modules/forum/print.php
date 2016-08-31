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

$tid = (isset($_GET['tid'])) ? abs(intval($_GET['tid'])) : 0;

show_title('Форум '.$config['title']);

$topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

if (!empty($topic)) {
	// -----------------------------------------------------------//
	$querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC;", array($tid));
	$posts = $querypost->fetchAll();

	while (ob_get_level()) {
		ob_end_clean();
	}
	header("Content-Encoding: none");
	die(render('forum/print', array('topic' => $topic, 'posts' => $posts)));

} else {
	show_error('Ошибка! Выбранная вами тема не существует, возможно она была удалена!');
}

render('includes/back', array('link' => 'index.php', 'title' => 'К форумам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
