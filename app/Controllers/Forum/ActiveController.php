<?php

namespace App\Controllers\Forum;

use App\Classes\Request;
use App\Classes\Validator;
use App\Controllers\BaseController;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

class ActiveController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        $login = check(Request::input('user', getUser('login')));

        $this->user = User::query()->where('login', $login)->first();

        if (! $this->user) {
            abort(404, 'Пользователь не найден!');
        }
    }

    /**
     * Вывод тем
     *
     * @return string
     */
    public function topics(): string
    {
        $user  = $this->user;
        $total = Topic::query()->where('user_id', $user->id)->count();

        if (! $total) {
            abort('default', 'Созданных тем еще нет!');
        }

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::query()
            ->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        return view('forums/active_topics', compact('topics', 'user', 'page'));
    }

    /**
     * Вывод сообшений
     *
     * @return string
     */
    public function posts(): string
    {
        $user  = $this->user;
        $total = Post::query()->where('user_id', $user->id)->count();

        if (! $total) {
            abort('default', 'Созданных сообщений еще нет!');
        }

        $page = paginate(setting('forumpost'), $total);

        $posts = Post::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('topic', 'user')
            ->get();

        return view('forums/active_posts', compact('posts', 'user', 'page'));
    }

    /**
     * Удаление сообщений
     *
     * @return string
     * @throws \Exception
     */
    public function delete(): string
    {
        if (! Request::ajax()) {
            redirect('/');
        }

        if (! isAdmin()) {
            abort(403, 'Удалять сообщения могут только модераторы!');
        }

        $token = check(Request::input('token'));
        $tid   = int(Request::input('tid'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $post = Post::query()
            ->where('id', $tid)
            ->with('topic.forum')
            ->first();

        $validator->true($post, 'Ошибка! Данного сообщения не существует!');

        if ($validator->isValid()) {

            $post->delete();
            $post->topic->decrement('count_posts');
            $post->topic->forum->decrement('count_posts');

            return json_encode(['status' => 'success']);
        }

        return json_encode(['status' => 'error', 'message' => current($validator->getErrors())]);
    }
}

