<?php

namespace App\Controllers;

class ForumActiveController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $login = check(Request::input('user', getUsername()));

        $this->user = User::where('login', $login)->first();

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
        $total = Topic::where('user_id', $user->id)->count();

        if (!$total) {
            abort('default', 'Созданных тем еще нет!');
        }

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(setting('forumtem'))
            ->offset($page['offset'])
            ->with('forum', 'user', 'lastPost.user')
            ->get();

        view('forum/active_themes', compact('topics', 'user', 'page'));
    }

    /**
     * Вывод сообшений
     */
    public function posts()
    {
        $user  = $this->user;
        $total = Post::where('user_id', $user->id)->count();

        if (!$total) {
            abort('default', 'Созданных сообщений еще нет!');
        }

        $page = paginate(setting('forumpost'), $total);

        $posts = Post::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(setting('forumpost'))
            ->offset($page['offset'])
            ->with('topic', 'user')
            ->get();

        view('forum/active_posts', compact('posts', 'user', 'page'));
    }

    /**
     * Удаление сообщений
     */
    public function delete()
    {
        if (!Request::ajax()) redirect('/');
        if (!is_admin()) abort(403, 'Удалять сообщения могут только модераторы!');

        $token = check(Request::input('token'));
        $tid = abs(intval(Request::input('tid')));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

        $post = Post::where('id', $tid)
            ->with('topic.forum')
            ->first();

        $validation->addRule('custom', $post, 'Ошибка! Данного сообщения не существует!');

        if ($validation->run()) {

            DB::run()->query("DELETE FROM `posts` WHERE `id`=? AND `topic_id`=?;", [$tid, $post['topic_id']]);
            DB::run()->query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post['topic_id']]);
            DB::run()->query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post->getTopic()->getForum()->id]);

            exit(json_encode(['status' => 'success']));
        } else {
            exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
        }
    }
}

