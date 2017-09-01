<?php

namespace App\Controllers;

class ForumNewController extends BaseController
{
    /**
     * Вывод тем
     */
    public function themes()
    {
        $total = Topic::count();

        if (!$total) {
            abort('default', 'Созданных тем еще нет!');
        }

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::orderBy('updated_at', 'desc')
            ->limit(setting('forumtem'))
            ->offset($page['offset'])
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        return view('forum/new_themes', compact('topics', 'page'));
    }

    /**
     * Вывод сообшений
     */
    public function posts()
    {
        $total = Post::count();

        if (!$total) {
            abort('default', 'Созданных сообщений еще нет!');
        }

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumpost'), $total);

        $posts = Post::orderBy('created_at', 'desc')
            ->limit(setting('forumpost'))
            ->offset($page['offset'])
            ->with('topic', 'user')
            ->get();

        return view('forum/new_posts', compact('posts', 'page'));
    }
}
