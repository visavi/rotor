<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Bookmark;
use App\Models\Topic;

class BookmarkController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (!getUser()) {
            abort('default', 'Для управления закладками, необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Bookmark::query()->where('user_id', getUser('id'))->count();
        $page  = paginate(setting('forumtem'), $total);

        $topics = Bookmark::query()
            ->select('bookmarks.posts as book_posts', 'bookmarks.topic_id', 'topics.*')
            ->where('bookmarks.user_id', getUser('id'))
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
        $tid   = int(Request::input('tid'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $topic = Topic::query()->find($tid);
        $validator->true($topic, 'Ошибка! Данной темы не существует!');

        if ($validator->isValid()) {

            $bookmark = Bookmark::query()
                ->where('topic_id', $tid)
                ->where('user_id', getUser('id'))
                ->first();

            if ($bookmark) {
                $bookmark->delete();
                exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
            } else {
                Bookmark::query()->create([
                    'user_id'  => getUser('id'),
                    'topic_id' => $tid,
                    'posts'    => $topic['posts'],
                ]);
                exit(json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']));
            }
        } else {
            exit(json_encode(['status' => 'error', 'message' => current($validator->getErrors())]));
        }
    }

    /**
     * Удаление закладок
     */
    public function delete()
    {
        $token    = check(Request::input('token'));
        $topicIds = intar(Request::input('del'));
        $page     = int(Request::input('page'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($topicIds, 'Ошибка! Отсутствуют выбранные закладки!');

        if ($validator->isValid()) {

            Bookmark::query()
                ->whereIn('topic_id', $topicIds)
                ->where('user_id', getUser('id'))
                ->delete();

            setFlash('success', 'Выбранные темы успешно удалены из закладок!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/forum/bookmark?page=' . $page);
    }
}
