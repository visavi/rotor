<?php

switch ($act):
/**
 * RSS всех топиков
 */
case 'index':

    $topics = Topic::where('closed', 0)
        ->with('lastPost.user')
        ->orderBy('updated_at', 'desc')
        ->limit(15)
        ->get();

    if ($topics->isEmpty()) {
        App::abort('default', 'Нет тем для отображения!');
    }

    App::view('forum/rss', compact('topics'));
    var_dump(getQueryLog()); exit;
break;

/**
 * RSS постов
 */
case 'posts':

    $tid = param('tid');

    $topic = Topic::find($tid);

    if (empty($topic)) {
        App::abort('default', 'Данной темы не существует!');
    }

    $posts = Post::where('topic_id', $tid)
        ->orderBy('created_at', 'desc')
        ->with('user')
        ->limit(15)
        ->get();

    App::view('forum/rss_posts', compact('topic', 'posts'));
break;
endswitch;

