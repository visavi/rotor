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

header("Content-type:application/rss+xml; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>';
echo '<title>'.$config['title'].' News</title>';
echo '<link>'.$config['home'].'</link>';
echo '<description>Новости RSS - '.$config['title'].'</description>';
echo '<image><url>'.$config['logotip'].'</url>';
echo '<title>'.$config['title'].' News</title>';
echo '<link>'.$config['home'].'</link></image>';
echo '<language>ru</language>';
echo '<copyright>'.$config['copy'].'</copyright>';
echo '<managingEditor>'.$config['emails'].' ('.$config['nickname'].')</managingEditor>';
echo '<webMaster>'.$config['emails'].' ('.$config['nickname'].')</webMaster>';
echo '<lastBuildDate>'.date("r", SITETIME).'</lastBuildDate>';

$querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `news_id` DESC LIMIT 15;");

while ($data = $querynews -> fetch()) {

	$data['news_text'] = bb_code($data['news_text']);
	$data['news_text'] = str_replace(array('/images/smiles', '[cut]'), array($config['home'].'/images/smiles', ''), $data['news_text']);
	$data['news_text'] = htmlspecialchars($data['news_text']);

	echo '<item><title>'.$data['news_title'].'</title><link>'.$config['home'].'/news/index.php?act=read&amp;id='.$data['news_id'].'</link>';
	echo '<description>'.$data['news_text'].' </description><author>'.nickname($data['news_author']).'</author>';
	echo '<pubDate>'.date("r", $data['news_time']).'</pubDate><category>Новости</category><guid>'.$config['home'].'/news/index.php?act=read&amp;id='.$data['news_id'].'</guid></item>';
}

echo '</channel></rss>';
?>
