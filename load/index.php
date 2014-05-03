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

show_title('Загрузки');
$config['newtitle'] = 'Загрузки - Список разделов';

$querydown = DB::run() -> query("SELECT `c`.*, (SELECT SUM(`cats_count`) FROM `cats` WHERE `cats_parent`=`c`.`cats_id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `downs_cats_id`=`cats_id` AND `downs_active`=? AND `downs_time` > ?) AS `new` FROM `cats` `c` ORDER BY `cats_order` ASC;", array(1, SITETIME-86400 * 5));

$downs = $querydown -> fetchAll();

if (count($downs) > 0) {
	$output = array();

	foreach ($downs as $row) {
		$id = $row['cats_id'];
		$fp = $row['cats_parent'];
		$output[$fp][$id] = $row;
	}

	echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> ';

	if (is_user()) {
		echo 'Мои: <a href="active.php?act=files">файлы</a>, <a href="active.php?act=comments">комментарии</a> / ';
	}

	echo 'Новые: <a href="new.php?act=files">файлы</a>, <a href="new.php?act=comments">комментарии</a><hr />';

	$totalnew = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_active`=? AND `downs_time`>?;", array (1, SITETIME-3600 * 120));

	echo '<img src="/images/img/top_dir.gif" alt="image" /> <b><a href="fresh.php">Свежие загрузки</a></b> ('.$totalnew.')<br />';

	foreach($output[0] as $key => $data) {
		echo '<img src="/images/img/dir.gif" alt="image" /> ';
		echo '<b><a href="down.php?cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

		$subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
		$new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

		echo '('.$data['cats_count'] . $subcnt . $new.')<br />';
		// ---------------------- Старый вывод ------------------------------//
		/**
		* if (isset($output[$key])) {
		*
		* echo '<small><b>Подкатегории:</b> ';
		* $i = 0;
		* foreach($output[$key] as $datasub){
		* if ($i==0) {$comma = '';} else {$comma = ', ';}
		* echo $comma.'<a href="down.php?cid='.$datasub['cats_id'].'">'.$datasub['cats_name'].'</a>';
		* ++$i;}
		* echo '</small><br />';
		* }
		*/
		// ------------------------- Новый вывод ---------------------------//
		if (isset($output[$key])) {
			foreach($output[$key] as $data) {
				$subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
				$new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

				echo '<img src="/images/img/right.gif" alt="image" /> <b><a href="down.php?cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';
				echo '('.$data['cats_count'] . $subcnt . $new.')<br />';
			}
		}
		// ----------------------------------------------------//
	}

	echo '<br /><a href="#up"><img src="/images/img/ups.gif" alt="Вверх" /></a> ';
	echo '<a href="top.php">Топ файлов</a> / ';
	echo '<a href="search.php">Поиск</a> / ';
	echo '<a href="add.php">Добавить файл</a><br />';

} else {
	show_error('Разделы загрузок еще не созданы!');
}

include_once ('../themes/footer.php');
?>
