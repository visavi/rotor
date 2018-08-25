<?php

namespace App\Controllers\Forum;

use App\Controllers\BaseController;
use App\Models\Post;
use App\Models\Topic;

class NewController extends BaseController
{
    /**
     * Вывод тем
     *
     * @return string
     */
    public function topics(): string
    {
        $total = Topic::query()->count();

        if (! $total) {
            abort('default', 'Созданных тем еще нет!');
        }

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::query()
            ->orderBy('updated_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        return view('forums/new_topics', compact('topics', 'page'));
    }

    /**
     * Вывод сообшений
     *
     * @return string
     */
    public function posts(): string
    {
        $total = Post::query()->count();

        if (! $total) {
            abort('default', 'Созданных сообщений еще нет!');
        }

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('forumpost'), $total);

        $posts = Post::query()
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('topic', 'user')
            ->get();

        return view('forums/new_posts', compact('posts', 'page'));
    }
}
