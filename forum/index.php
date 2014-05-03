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

show_title('Форум '.$config['title']);
$config['newtitle'] = 'Форум - Список разделов';

include_once (DATADIR.'/advert/forum.dat');

$queryforum = DB::run() -> query("SELECT * FROM `forums` ORDER BY `forums_order` ASC;");
$forums = $queryforum -> fetchAll();

if (count($forums) > 0) {
	$output = array();

	foreach ($forums as $row) {
		$id = $row['forums_id'];
		$fp = $row['forums_parent'];
		$output[$fp][$id] = $row;
	}

	render('forum/index', array('forums' => $output));

} else {
	show_error('Разделы форума еще не созданы!');
}

include_once ('../themes/footer.php');
?>
