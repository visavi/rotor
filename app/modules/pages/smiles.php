<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Список смайлов');

$total = DBM::run()->count('smiles');
$page = App::paginate(App::setting('smilelist'), $total);

if ($total > 0) {

	$smiles = DBM::run()->query("SELECT * FROM `smiles` ORDER BY CHAR_LENGTH(`code`) ASC LIMIT :start, :limit;", ['start' => $page['offset'], 'limit' => intval($config['smilelist'])]);

	foreach($smiles as $smile) {
		echo '<img src="/uploads/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';
	}

    App::pagination($page);

	echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
} else {
	show_error('Смайлы не найдены!');
}

App::view($config['themes'].'/foot');
