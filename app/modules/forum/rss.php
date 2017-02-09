<?php

switch ($act):
/**
 * RSS всех топиков
 */
case 'index':

    $topics = Topic::where('closed', 0)
        ->with('lastPost.user')
        ->orderBy('time', 'desc')
        ->limit(15)
        ->get();

    if ($topics->isEmpty()) {
        App::abort('default', 'Нет тем для отображения!');
    }

    App::view('forum/rss', compact('topics'));
break;

/**
 * RSS постов
 */
case 'posts':

    $tid = param('tid');

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

    if (empty($topic)) {
        App::abort('default', 'Данной темы не существует!');
    }

    $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `topic_id`=? ORDER BY `time` DESC LIMIT 15;", [$tid]);
    $posts = $querypost->fetchAll();

    App::view('forum/rss_posts', compact('topic', 'posts'));

break;
endswitch;

