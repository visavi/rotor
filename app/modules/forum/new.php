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

	$querytopic = DB::run() -> query("SELECT `topics`.*, `forums_title` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";");
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

	$querypost = DB::run() -> query("SELECT `posts`.*, `topics_title`, `topics_posts` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` ORDER BY `posts_time` DESC LIMIT ".$start.", ".$config['forumpost'].";");
	$posts = $querypost->fetchAll();

	App::view('forum/new_posts', compact('posts', 'start', 'total'));
break;

endswitch;
