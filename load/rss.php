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

if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}

show_title('Комментарии');

$down = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `downs_id`=? LIMIT 1;", array($id));

if (!empty($down)) {
	if (!empty($down['downs_active'])) {
		ob_implicit_flush();
		ob_end_clean();
		ob_clean();
		header("Content-type:application/rss+xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<rss version="2.0"><channel>';
		echo '<title>Комментарии - '.$down['downs_title'].'</title>';
		echo '<link>'.$config['home'].'</link>';
		echo '<description>Комментарии RSS - '.$config['title'].'</description>';
		echo '<image><url>'.$config['logotip'].'</url>';
		echo '<title>Комментарии - '.$down['downs_title'].'</title>';
		echo '<link>'.$config['home'].'</link></image>';
		echo '<language>ru</language>';
		echo '<copyright>'.$config['copy'].'</copyright>';
		echo '<managingEditor>'.$config['emails'].'</managingEditor>';
		echo '<webMaster>'.$config['emails'].'</webMaster>';
		echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

		$querycomm = DB::run() -> query("SELECT * FROM `commload` WHERE `commload_down`=? ORDER BY `commload_time` DESC LIMIT 15;", array($id));

		while ($data = $querycomm -> fetch()) {
			$data['commload_text'] = bb_code($data['commload_text']);
			$data['commload_text'] = str_replace('/images/smiles', $config['home'].'/images/smiles', $data['commload_text']);
			$data['commload_text'] = htmlspecialchars($data['commload_text']);

			echo '<item><title>'.$down['downs_title'].'</title><link>'.$config['home'].'/load/down.php?act=comments&amp;id='.$down['downs_id'].'</link>';
			echo '<description>'.$data['commload_text'].' </description><author>'.nickname($data['commload_author']).'</author>';
			echo '<pubDate>'.date("r", $data['commload_time']).'</pubDate><category>Комментарии</category><guid>'.$config['home'].'/load/down.php?act=comments&amp;id='.$down['downs_id'].'&amp;pid='.$data['commload_id'].'</guid></item>';
		}

		echo '</channel></rss>';
		ob_end_flush();
		exit;
	} else {
		show_error('Ошибка! Данный файл еще не проверен модератором!');
	}
} else {
	show_error('Ошибка! Выбранный вами файл не существует, возможно он был удален!');
}

echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">Категории</a>';

include_once ('../themes/footer.php');
?>
