<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use Illuminate\View\View;

class NewController extends Controller
{
    /**
     * Вывод тем
     *
     * @return View
     */
    public function topics(): View
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
     * @return View
     */
    public function posts(): View
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
