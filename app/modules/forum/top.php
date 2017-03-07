<?php

switch ($act):
############################################################################################
##                                     Последние темы                                     ##
############################################################################################
case 'themes':
    $total = Topic::count();

    if ($total > 0) {

        if ($total > 500) {
            $total = 500;
        }

        $page = App::paginate(App::setting('forumtem'), $total);

        $topics = Topic::where('closed', 0)
            ->orderBy('posts', 'desc')
            ->limit(App::setting('forumtem'))
            ->offset($page['offset'])
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        App::view('forum/top', compact('topics', 'page'));

    } else {
        show_error('Созданных тем еще нет!');
    }
break;

case 'posts':
    $total = Post::count();

    if ($total > 0) {

        if ($total > 500) {
            $total = 500;
        }

        $page = App::paginate(App::setting('forumpost'), $total);

        $posts = Post::orderBy('rating', 'desc')
            ->limit(App::setting('forumpost'))
            ->offset($page['offset'])
            ->with('topic', 'user')
            ->get();

        App::view('forum/top_posts', compact('posts', 'page'));

    } else {
        show_error('Созданных тем еще нет!');
    }
break;

endswitch;
