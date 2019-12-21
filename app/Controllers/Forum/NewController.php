<?php

declare(strict_types=1);

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
        $topics = Topic::query()
            ->orderByDesc('updated_at')
            ->with('forum', 'user', 'lastPost.user')
            ->limit(100)
            ->get()
            ->all();

        $topics = paginate($topics, setting('forumtem'));

        return view('forums/new_topics', compact('topics'));
    }

    /**
     * Вывод сообшений
     *
     * @return string
     */
    public function posts(): string
    {
        $posts = Post::query()
            ->orderByDesc('created_at')
            ->with('topic', 'user')
            ->limit(100)
            ->get()
            ->all();

        $posts = paginate($posts, setting('forumpost'));

        return view('forums/new_posts', compact('posts'));
    }
}
