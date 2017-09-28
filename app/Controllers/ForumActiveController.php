<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

class ForumActiveController extends BaseController
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
            abort('default', 'Пользователь не найден!');
        }
    }

    /**
     * Вывод тем
     */
    public function themes()
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
            ->limit(setting('forumtem'))
            ->offset($page['offset'])
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        return view('forum/active_themes', compact('topics', 'user', 'page'));
    }

    /**
     * Вывод сообшений
     */
    public function posts()
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
            ->limit(setting('forumpost'))
            ->offset($page['offset'])
            ->with('topic', 'user')
            ->get();

        return view('forum/active_posts', compact('posts', 'user', 'page'));
    }

    /**
     * Удаление сообщений
     */
    public function delete()
    {
        if (! Request::ajax()) {
            redirect('/');
        }

        if (! isAdmin()) {
            abort(403, 'Удалять сообщения могут только модераторы!');
        }

        $token = check(Request::input('token'));
        $tid = abs(intval(Request::input('tid')));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $post = Post::query()
            ->where('id', $tid)
            ->with('topic.forum')
            ->first();

        $validator->true($post, 'Ошибка! Данного сообщения не существует!');

        if ($validator->isValid()) {

            $post->delete();
            $post->topic->decrement('posts');
            $post->topic->forum->decrement('posts');

            exit(json_encode(['status' => 'success']));
        } else {
            exit(json_encode(['status' => 'error', 'message' => current($validator->getErrors())]));
        }
    }
}

