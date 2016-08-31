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

$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

show_title('RSS комментарии');

$blog = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `blogs_id`=? LIMIT 1;", array($id));

if (!empty($blog)) {
	$querycomm = DB::run() -> query("SELECT * FROM `commblog` WHERE `commblog_blog`=? ORDER BY `commblog_time` DESC LIMIT 15;", array($id));
	$comments = $querycomm->fetchAll();

	while (ob_get_level()) {
		ob_end_clean();
	}

	header("Content-Encoding: none");
	header("Content-type:application/rss+xml; charset=utf-8");
	die(render('blog/rss', array('blog' => $blog, 'comments' => $comments)));

} else {
	show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
}

render('includes/back', array('link' => 'index.php', 'title' => 'К блогам', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
