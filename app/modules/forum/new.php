<?php

switch ($action):
############################################################################################
##                                     Последние темы                                     ##
############################################################################################
case 'themes':
    $total = Topic::count();

    if (! $total) {
        App::abort('default', 'Созданных тем еще нет!');
    }

    if ($total > 500) {
        $total = 500;
    }

    $page = App::paginate(Setting::get('forumtem'), $total);

    $topics = Topic::orderBy('updated_at', 'desc')
        ->limit(Setting::get('forumtem'))
        ->offset($page['offset'])
        ->with('forum', 'user', 'lastPost.user')
        ->get();

    App::view('forum/new_themes', compact('topics', 'page'));
break;

############################################################################################
##                                  Последние сообщения                                   ##
############################################################################################
case 'posts':
    $total = Post::count();

    if (! $total) {
        App::abort('default', 'Созданных сообщений еще нет!');
    }

    if ($total > 500) {
        $total = 500;
    }

    $page = App::paginate(Setting::get('forumpost'), $total);

    $posts = Post::orderBy('created_at', 'desc')
        ->limit(Setting::get('forumpost'))
        ->offset($page['offset'])
        ->with('topic', 'user')
        ->get();

    App::view('forum/new_posts', compact('posts', 'page'));
break;

endswitch;
