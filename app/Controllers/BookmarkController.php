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

        if (! getUser()) {
            abort(403, 'Для управления закладками, необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Bookmark::query()->where('user_id', getUser('id'))->count();
        $page  = paginate(setting('forumtem'), $total);

        $topics = Bookmark::query()
            ->select('bookmarks.count_posts as bookmark_posts', 'bookmarks.topic_id', 'topics.*')
            ->where('bookmarks.user_id', getUser('id'))
            ->leftJoin('topics', 'bookmarks.topic_id', '=', 'topics.id')
            ->with('topic.user', 'topic.lastPost.user')
            ->orderBy('updated_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('forums/bookmarks', compact('topics', 'page'));
    }

    /**
     * Добавление / удаление закладок
     *
     * @return string
     * @throws \Exception
     */
    public function perform(): string
    {
        if (! Request::ajax()) {
            redirect('/');
        }

        $token = check(Request::input('token'));
        $tid   = int(Request::input('tid'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $topic = Topic::query()->find($tid);
        $validator->true($topic, 'Данной темы не существует!');

        if ($validator->isValid()) {

            $bookmark = Bookmark::query()
                ->where('topic_id', $tid)
                ->where('user_id', getUser('id'))
                ->first();

            if ($bookmark) {
                $bookmark->delete();
                return json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']);
            }

            Bookmark::query()->create([
                'user_id'     => getUser('id'),
                'topic_id'    => $tid,
                'count_posts' => $topic->count_posts,
            ]);
            return json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']);
        }

        return json_encode(['status' => 'error', 'message' => current($validator->getErrors())]);
    }

    /**
     * Удаление закладок
     *
     * @return void
     */
    public function delete(): void
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

        redirect('/forums/bookmarks?page=' . $page);
    }
}
