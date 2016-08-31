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

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'read';

switch ($sort) {
	case 'rated': $order = 'blogs_rating';
		break;
	case 'comm': $order = 'blogs_comments';
		break;
	default: $order = 'blogs_read';
}
############################################################################################
##                                       Топ тем                                          ##
############################################################################################
show_title('Топ популярных блогов');

$total = DB::run() -> querySingle("SELECT count(*) FROM `blogs`;");

if ($total > 0) {
	if ($start >= $total) {
		$start = last_page($total, $config['blogpost']);
	}

	$queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_id`, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`blogs_cats_id`=`catsblog`.`cats_id` ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['blogpost'].";");
	$blogs = $queryblog->fetchAll();

	render('blog/top', array('blogs' => $blogs, 'order' => $order));

	page_strnavigation('top.php?sort='.$sort.'&amp;', $config['blogpost'], $start, $total);
} else {
	show_error('Опубликованных статей еще нет!');
}

render('includes/back', array('link' => 'index.php', 'title' => 'Категории', 'icon' => 'reload.gif'));

include_once ('../themes/footer.php');
?>
