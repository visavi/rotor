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

$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

show_title('Список свежих загрузок');

$total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_time`>?;", array (1, SITETIME-3600 * 120));

if ($total > 0) {
	if ($start >= $total) {
		$start = 0;
	}

	$querydown = DB::run() -> query("SELECT `downs`.*, `cats_name`, folder FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_active`=? AND `downs_time`>? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array(1, SITETIME-3600 * 120));

	while ($data = $querydown -> fetch()) {
		$folder = $data['folder'] ? $data['folder'].'/' : '';

		$filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/load/files/'.$folder.$data['downs_link']) : 0;

		echo '<div class="b">';

		if ($data['downs_time'] >= (SITETIME-3600 * 24)) {
			echo '<img src="/images/img/new.gif" alt="image" /> ';
		} elseif ($data['downs_time'] >= (SITETIME-3600 * 72)) {
			echo '<img src="/images/img/new1.gif" alt="image" /> ';
		} else {
			echo '<img src="/images/img/new2.gif" alt="image" /> ';
		}

		echo '<b><a href="down.php?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')</div>';

		echo '<div>Категория: <a href="down.php?cid='.$data['downs_cats_id'].'">'.$data['cats_name'].'</a><br />';
		echo 'Скачиваний: '.$data['downs_load'].'<br />';
		echo '<a href="down.php?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].') ';
		echo '<a href="down.php?act=end&amp;id='.$data['downs_id'].'">&raquo;</a><br />';
		echo 'Добавлено: '.profile($data['downs_user']).' ('.date_fixed($data['downs_time']).')</div>';
	}

	page_strnavigation('fresh.php?', $config['downlist'], $start, $total);

	echo '<img src="/images/img/new.gif" alt="image" /> - Самая свежая загрузка<br />';
	echo '<img src="/images/img/new1.gif" alt="image" /> - Более дня назад<br />';
	echo '<img src="/images/img/new2.gif" alt="image" /> - Более 3 дней назад<br /><br />';

	echo 'Всего файлов: <b>'.$total.'</b><br /><br />';
} else {
	show_error('За последние 5 дней загрузок еще нет!');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Категории</a><br />';

include_once ('../themes/footer.php');
?>
