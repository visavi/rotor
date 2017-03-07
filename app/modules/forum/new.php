<?php

switch ($act):
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

    $page = App::paginate(App::setting('forumtem'), $total);

    $topics = Topic::orderBy('updated_at', 'desc')
        ->limit(App::setting('forumtem'))
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

    $page = App::paginate(App::setting('forumpost'), $total);

    $posts = Post::orderBy('created_at', 'desc')
        ->limit(App::setting('forumpost'))
        ->offset($page['offset'])
        ->with('topic', 'user')
        ->get();

    App::view('forum/new_posts', compact('posts', 'page'));
break;

endswitch;
