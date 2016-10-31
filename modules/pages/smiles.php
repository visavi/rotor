<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Список смайлов');

$total = DBM::run()->count('smiles');

if ($total > 0) {
	if ($start >= $total) {
		$start = 0;
	}

	$smiles = DBM::run()->query("SELECT * FROM `smiles` ORDER BY CHAR_LENGTH(`smiles_code`) ASC LIMIT :start, :limit;", array('start' => intval($start), 'limit' => intval($config['smilelist'])));

	foreach($smiles as $smile) {
		echo '<img src="'.$smile['smiles_name'].'" alt="" /> — <b>'.$smile['smiles_code'].'</b><br />';
	}

	page_strnavigation('/smiles?', $config['smilelist'], $start, $total);

	echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
} else {
	show_error('Смайлы не найдены!');
}

App::view($config['themes'].'/foot');
