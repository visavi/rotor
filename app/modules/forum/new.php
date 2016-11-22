<?php

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

    $page = App::paginate(App::setting('forumtem'), $total);

	$querytopic = DB::run() -> query("SELECT `t`.*, f.`title` forum_title FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` ORDER BY `last_time` DESC LIMIT ".$page['offset'].", ".$config['forumtem'].";");
	$topics = $querytopic->fetchAll();

	App::view('forum/new_themes', compact('topics', 'page'));
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

    $page = App::paginate(App::setting('forumpost'), $total);

	$querypost = DB::run() -> query("SELECT `posts`.*, `title`, `posts` FROM `posts` LEFT JOIN `topics` ON `posts`.`topic_id`=`topics`.`id` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['forumpost'].";");
	$posts = $querypost->fetchAll();

	App::view('forum/new_posts', compact('posts', 'page'));
break;

endswitch;
