<?php

declare(strict_types=1);

namespace App\Controllers\Forum;

use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class ActiveController extends BaseController
{
    /**
     * @var User
     */
    public $user;

    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $login      = $request->input('user', getUser('login'));
        $this->user = getUserByLogin($login);

        if (! $this->user) {
            abort(404, __('validator.user'));
        }
    }

    /**
     * Вывод тем
     *
     * @return string
     */
    public function topics(): string
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
     * Вывод сообшений
     *
     * @return string
     */
    public function posts(): string
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
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     * @throws Exception
     */
    public function delete(Request $request, Validator $validator): string
    {
        if (! $request->ajax()) {
            redirect('/');
        }

        if (! isAdmin()) {
            abort(403, __('forums.posts_deleted_moderators'));
        }

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'));

        $post = Post::query()
            ->where('id', int($request->input('tid')))
            ->with('topic.forum')
            ->first();

        $validator->true($post, __('forums.post_not_exist'));

        if ($validator->isValid()) {
            $post->delete();
            $post->topic->decrement('count_posts');
            $post->topic->forum->decrement('count_posts');

            return json_encode(['status' => 'success']);
        }

        return json_encode(['status' => 'error', 'message' => current($validator->getErrors())]);
    }
}

