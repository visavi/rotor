<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Bookmark;
use App\Models\Topic;
use Illuminate\Database\Capsule\Manager as DB;

class BookmarkController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (!isUser()) {
            abort('default', 'Для управления закладками, необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Bookmark::where('user_id', getUserId())->count();
        $page  = paginate(setting('forumtem'), $total);

        $topics = Bookmark::select('bookmarks.posts as book_posts', 'bookmarks.topic_id', 'topics.*')
            ->where('bookmarks.user_id', getUserId())
            ->leftJoin('topics', 'bookmarks.topic_id', '=', 'topics.id')
            ->with('topic.user', 'topic.lastPost.user')
            ->orderBy('updated_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('forumtem'))
            ->get();

        return view('forum/bookmark', compact('topics', 'page'));
    }

    /**
     * Добавление / удаление закладок
     */
    public function perform()
    {
        if (! Request::ajax()) redirect('/');

        $token = check(Request::input('token'));
        $tid   = abs(intval(Request::input('tid')));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

        $topic = Topic::find($tid);
        $validation->addRule('custom', $topic, 'Ошибка! Данной темы не существует!');

        if ($validation->run()) {

            $bookmark = Bookmark::where('topic_id', $tid)
                ->where('user_id', getUserId())
                ->first();

            if ($bookmark) {
                $bookmark->delete();
                exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
            } else {
                Bookmark::create([
                    'user_id'  => getUserId(),
                    'topic_id' => $tid,
                    'posts'    => $topic['posts'],
                ]);
                exit(json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']));
            }
        } else {
            exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
        }
    }

    /**
     * Удаление закладок
     */
    public function delete()
    {
        $token    = check(Request::input('token'));
        $topicIds = intar(Request::input('del'));
        $page     = abs(intval(Request::input('page')));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('not_empty', $topicIds, 'Ошибка! Отсутствуют выбранные закладки!');

        if ($validation->run()) {

            Bookmark::whereIn('topic_id', $topicIds)
                ->where('user_id', getUserId())
                ->delete();

            setFlash('success', 'Выбранные темы успешно удалены из закладок!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect('/forum/bookmark?page=' . $page);
    }
}
