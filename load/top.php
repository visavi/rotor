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
$sort = isset($_GET['sort']) ? check($_GET['sort']) : 'load';

switch ($sort) {
	case 'rated': $order = 'downs_rated';
		break;
	case 'comm': $order = 'downs_comments';
		break;
	default: $order = 'downs_load';
}
############################################################################################
##                                       Топ тем                                          ##
############################################################################################
show_title('Топ популярных файлов');

echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';
echo 'Сортировать: ';

if ($order == 'downs_load') {
	echo '<b>Скачивания</b> / ';
} else {
	echo '<a href="top.php?sort=load">Скачивания</a> / ';
}

if ($order == 'downs_rated') {
	echo '<b>Оценки</b> / ';
} else {
	echo '<a href="top.php?sort=rated">Оценки</a> / ';
}

if ($order == 'downs_comments') {
	echo '<b>Комментарии</b>';
} else {
	echo '<a href="top.php?sort=comm">Комментарии</a>';
}

echo '<hr />';

$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=?;", array(1));

if ($total > 0) {
	if ($start >= $total) {
		$start = 0;
	}

	$querydown = DB::run() -> query("SELECT `downs`.*, `cats_id`, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['downlist'].";", array(1));

	while ($data = $querydown -> fetch()) {
		$folder = $data['folder'] ? $data['folder'].'/' : '';

		$filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/load/files/'.$folder.$data['downs_link']) : 0;

		echo '<div class="b"><img src="/images/img/zip.gif" alt="image" /> ';
		echo '<b><a href="down.php?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

		echo '<div>Категория: <a href="down.php?cid='.$data['cats_id'].'">'.$data['cats_name'].'</a><br />';
		echo 'Скачиваний: '.$data['downs_load'].'<br />';
		$raiting = (!empty($data['downs_rated'])) ? round($data['downs_raiting'] / $data['downs_rated'], 1) : 0;

		echo 'Рейтинг: <b>'.$raiting.'</b> (Голосов: '.$data['downs_rated'].')<br />';
		echo '<a href="down.php?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].') ';
		echo '<a href="down.php?act=end&amp;id='.$data['downs_id'].'">&raquo;</a></div>';
	}

	page_strnavigation('top.php?sort='.$sort.'&amp;', $config['downlist'], $start, $total);
} else {
	show_error('Опубликованных файлов еще нет!');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Категории</a><br />';

include_once ('../themes/footer.php');
?>
