<?php

$start = abs(intval(Request::input('start', 0)));

switch ($act):
############################################################################################
##                                     Последние темы                                     ##
############################################################################################
case 'themes':
	$total = DB::run() -> querySingle("SELECT count(*) FROM topics;");

	if (! $total) {
		App::abort('default', 'Созданных тем еще нет!');
	}

	if ($total > 100) {
		$total = 100;
	}
	if ($start >= $total) {
		$start = last_page($total, $config['forumtem']);
	}

	$querytopic = DB::run() -> query("SELECT `topics`.*, `title` FROM `topics` LEFT JOIN `forums` ON `topics`.`forums_id`=`forums`.`id` ORDER BY `last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";");
	$topics = $querytopic->fetchAll();

	App::view('forum/new_themes', compact('topics', 'start', 'total'));
break;

############################################################################################
##                                  Последние сообщения                                   ##
############################################################################################
case 'posts':
	$total = DB::run() -> querySingle("SELECT count(*) FROM `posts`;");

	if (! $total) {
		App::abort('default', 'Созданных сообщений еще нет!!');
	}

	if ($total > 100) {
		$total = 100;
	}
	if ($start >= $total) {
		$start = last_page($total, $config['forumpost']);
	}

	$querypost = DB::run() -> query("SELECT `posts`.*, `title`, `posts` FROM `posts` LEFT JOIN `topics` ON `posts`.`topics_id`=`topics`.`id` ORDER BY `time` DESC LIMIT ".$start.", ".$config['forumpost'].";");
	$posts = $querypost->fetchAll();

	App::view('forum/new_posts', compact('posts', 'start', 'total'));
break;

endswitch;
