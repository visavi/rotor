<?php

declare(strict_types=1);

namespace Modules\Forum\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Forum\Models\Post;
use Modules\Forum\Models\Topic;

class NewController extends Controller
{
    /**
     * Вывод тем
     */
    public function topics(Request $request): View
    {
        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Topic::getSorting($sort, $order);

        $topics = Topic::query()
            ->orderBy(...$orderBy)
            ->with('forum', 'user', 'lastPost.user')
            ->limit(1000)
            ->get();

        $topics = paginate($topics, setting('forumtem'))
            ->appends(compact('sort', 'order'));

        return view('forum::forums/new_topics', compact('topics', 'sorting'));
    }

    /**
     * Вывод сообщений
     */
    public function posts(Request $request): View
    {
        $period = int($request->input('period'));

        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Post::getSorting($sort, $order);

        $posts = Post::query()
            ->when($period, static function (Builder $query) use ($period) {
                return $query->where('created_at', '>', strtotime('-' . $period . ' day', SITETIME));
            })
            ->orderBy(...$orderBy)
            ->with('topic', 'user')
            ->limit(1000)
            ->get();

        $posts = paginate($posts, setting('forumpost'), compact('period', 'sort', 'order'));

        return view('forum::forums/new_posts', compact('posts', 'period', 'sort', 'order', 'sorting'));
    }
}
