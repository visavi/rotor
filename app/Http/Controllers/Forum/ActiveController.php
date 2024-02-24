<?php

declare(strict_types=1);

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ActiveController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $login = $request->input('user', getUser('login'));
            $this->user = getUserByLogin($login);

            if (! $this->user) {
                abort(404, __('validator.user'));
            }

            return $next($request);
        });
    }

    /**
     * Вывод тем
     *
     * @return View
     */
    public function topics(): View
    {
        $user = $this->user;

        $topics = Topic::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->with('forum', 'user', 'lastPost.user')
            ->paginate(setting('forumtem'))
            ->appends(['user' => $user->login]);

        return view('forums/active_topics', compact('topics', 'user'));
    }

    /**
     * Вывод сообщений
     *
     * @return View
     */
    public function posts(): View
    {
        $user = $this->user;

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('topic', 'user')
            ->paginate(setting('forumpost'))
            ->appends(['user' => $user->login]);

        return view('forums/active_posts', compact('posts', 'user'));
    }

    /**
     * Удаление сообщений
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if (! isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('forums.posts_deleted_moderators'),
            ]);
        }

        $post = Post::query()
            ->where('id', $id)
            ->with('topic.forum')
            ->first();

        if (! $post) {
            return response()->json([
                'success' => false,
                'message' => __('forums.post_not_exist'),
            ]);
        }

        $post->delete();
        $post->topic->decrement('count_posts');
        $post->topic->forum->decrement('count_posts');

        return response()->json([
            'success' => true,
            'message' => __('main.record_deleted_success'),
        ]);
    }
}
