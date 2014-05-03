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

if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['sort'])) {
	$sort = check($_GET['sort']);
} else {
	$sort = 'rated';
}

switch ($sort) {
	case 'rated': $order = 'photo_rating';
		break;
	case 'comm': $order = 'photo_comments';
		break;
	default: $order = 'photo_rating';
}
############################################################################################
##                                       Топ фото                                         ##
############################################################################################
show_title('Топ популярных фотографий');

echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';
echo 'Сортировать: ';

if ($order == 'photo_rating') {
	echo '<b><a href="top.php?sort=rated">Оценки</a></b>, ';
} else {
	echo '<a href="top.php?sort=rated">Оценки</a>, ';
}

if ($order == 'photo_comments') {
	echo '<b><a href="top.php?sort=comm">Комментарии</a></b>';
} else {
	echo '<a href="top.php?sort=comm">Комментарии</a>';
}

echo '<hr />';

$total = DB::run() -> querySingle("SELECT count(*) FROM `photo`;");

if ($total > 0) {
	if ($start >= $total) {
		$start = last_page($total, $config['fotolist']);
	}

	$queryphoto = DB::run() -> query("SELECT * FROM `photo` ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['fotolist'].";");

	while ($data = $queryphoto -> fetch()) {

		echo '<div class="b"><img src="/images/img/gallery.gif" alt="image" /> ';
		echo '<b><a href="index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.$data['photo_title'].'</a></b> ('.read_file(BASEDIR.'/upload/pictures/'.$data['photo_link']).') ('.format_num($data['photo_rating']).')</div>';

		echo '<div><a href="index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.resize_image('upload/pictures/', $data['photo_link'], $config['previewsize'], $data['photo_title']).'</a>';

		echo '<br />'.bb_code($data['photo_text']).'<br />';

		echo 'Добавлено: '.profile($data['photo_user']).' ('.date_fixed($data['photo_time']).')<br />';
		echo '<a href="index.php?act=comments&amp;gid='.$data['photo_id'].'">Комментарии</a> ('.$data['photo_comments'].') ';
		echo '<a href="index.php?act=end&amp;gid='.$data['photo_id'].'">&raquo;</a>';
		echo '</div>';
	}

	page_strnavigation('top.php?sort='.$sort.'&amp;', $config['fotolist'], $start, $total);
} else {
	show_error('Загруженных фотографий еще нет!');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Галерея</a><br />';

include_once ('../themes/footer.php');
?>
