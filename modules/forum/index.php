<?php
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

	App::view('forum/index', ['forums' => $output]);
} else {
	show_error('Разделы форума еще не созданы!');
}
