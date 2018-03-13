<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\File;
use App\Models\Forum;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Capsule\Manager as DB;

class ForumController extends AdminController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('lastTopic.lastPost.user')
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($forums->isEmpty()) {
            abort('default', 'Разделы форума еще не созданы!');
        }

        return view('admin/forum/index', compact('forums'));
    }

    /**
     * Создание раздела
     */
    public function create()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));
        $title = check(Request::input('title'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название раздела!']);

        if ($validator->isValid()) {

            $max = Forum::query()->max('sort') + 1;

            $forum = Forum::query()->create([
                'title' => $title,
                'sort'  => $max,
            ]);

            setFlash('success', 'Новый раздел успешно создан!');
            redirect('/admin/forum/edit/' . $forum->id);
        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/forum');
    }

    /**
     * Редактирование форума
     */
    public function edit($id)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, 'Данного раздела не существует!');
        }

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->orderBy('sort')
            ->get();

        if (Request::isMethod('post')) {
            $token       = check(Request::input('token'));
            $parent      = int(Request::input('parent'));
            $title       = check(Request::input('title'));
            $description = check(Request::input('description'));
            $sort        = check(Request::input('sort'));
            $closed      = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название раздела!'])
                ->length($description, 0, 100, ['description' => 'Слишком длинное описания раздела!'])
                ->notEqual($parent, $forum->id, ['parent' => 'Недопустимый выбор родительского раздела!']);

            if (! empty($parent) && $forum->children->isNotEmpty()) {
                $validator->addError(['parent' => 'Текущий раздел имеет подфорумы!']);
            }

            if ($validator->isValid()) {

                $forum->update([
                    'parent_id'   => $parent,
                    'title'       => $title,
                    'description' => $description,
                    'sort'        => $sort,
                    'closed'      => $closed,
                ]);

                setFlash('success', 'Раздел успешно отредактирован!');
                redirect('/admin/forum');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forum/edit', compact('forums', 'forum'));
    }

    /**
     * Удаление раздела
     */
    public function delete($id)
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $forum = Forum::query()->with('children')->find($id);

        if (! $forum) {
            abort(404, 'Данного раздела не существует!');
        }

        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($forum->children->isEmpty(), 'Удаление невозможно! Данный раздел имеет подфорумы!');

        $topic = Topic::query()->where('forum_id', $forum->id)->first();
        if ($topic) {
            $validator->addError('Удаление невозможно! В данном разделе имеются темы!');
        }

        if ($validator->isValid()) {

            $forum->delete();

            setFlash('success', 'Раздел успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/forum');
    }

    /**
     * Пересчет данных
     */
    public function restatement()
    {
        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        $token = check(Request::input('token'));

        if ($token == $_SESSION['token']) {

            restatement('forum');

            setFlash('success', 'Данные успешно пересчитаны!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/forum');
    }

    /**
     * Просмотр тем раздела
     */
    public function forum($id)
    {
        $forum = Forum::query()->with('parent', 'children.lastTopic.lastPost.user')->find($id);

        if (! $forum) {
            abort(404, 'Данного раздела не существует!');
        }

        $total = Topic::query()->where('forum_id', $forum->id)->count();

        $page = paginate(setting('forumtem'), $total);

        $topics = Topic::query()
            ->where('forum_id', $forum->id)
            ->orderBy('locked', 'desc')
            ->orderBy('updated_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('lastPost.user')
            ->get();

        return view('admin/forum/forum', compact('forum', 'topics', 'page'));
    }

    /**
     * Редактирование темы
     */
    public function editTopic($id)
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        if (Request::isMethod('post')) {

            $token      = check(Request::input('token'));
            $title      = check(Request::input('title'));
            $note       = check(Request::input('note'));
            $moderators = check(Request::input('moderators'));
            $locked     = empty(Request::input('locked')) ? 0 : 1;
            $closed     = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название темы!'])
                ->length($note, 0, 250, ['note' => 'Слишком длинное объявление!']);

            if ($validator->isValid()) {

                $moderators = implode(',', preg_split('/[\s]*[,][\s]*/', $moderators));

                $topic->update([
                    'title'      => $title,
                    'note'       => $note,
                    'moderators' => $moderators,
                    'locked'     => $locked,
                    'closed'     => $closed,
                ]);

                setFlash('success', 'Тема успешно отредактирована!');
                redirect('/admin/forum/' . $topic->forum_id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forum/edit_topic', compact('topic'));
    }

    /**
     * Перенос темы
     */
    public function moveTopic($id)
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $fid   = int(Request::input('fid'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $forum = Forum::query()->find($fid);

            if ($forum) {
                if ($forum->closed) {
                    $validator->addError(['forum' => 'В закрытый раздел запрещено перемещать темы!']);
                }

                if ($topic->forum_id == $forum->id) {
                    $validator->addError(['forum' => 'Нельзя переносить тему в этот же раздел!']);
                }
            } else {
                $validator->addError(['forum' => 'Выбранного раздела не существует!']);
            }

            if ($validator->isValid()) {

                $oldTopic = $topic->replicate();

                $topic->update([
                    'forum_id' => $forum->id,
                ]);

                // Обновление счетчиков
                $topic->forum->restatement();
                $oldTopic->forum->restatement();

                setFlash('success', 'Тема успешно перенесена!');
                redirect('/admin/forum/' . $topic->forum_id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $forums = Forum::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('admin/forum/move_topic', compact('forums', 'topic'));
    }

    /**
     * Закрытие и закрепление тем
     */
    public function actionTopic($id)
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $type  = check(Request::input('type'));

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        if ($token == $_SESSION['token']) {

            switch ($type):
                case 'closed':
                    $topic->update(['closed' => 1]);

                    $vote = Vote::query()->where('topic_id', $topic->id)->first();
                    if ($vote) {
                        $vote->update(['closed' => 1]);
                        $vote->pollings()->delete();
                    }

                    setFlash('success', 'Тема успешно закрыта!');
                    break;

                case 'open':
                    $topic->update(['closed' => 0]);

                    $vote = Vote::query()->where('topic_id', $topic->id)->first();
                    if ($vote) {
                        $vote->update(['closed' => 0]);
                    }

                    setFlash('success', 'Тема успешно открыта!');
                    break;

                case 'locked':
                    $topic->update(['locked' => 1]);
                    setFlash('success', 'Тема успешно закреплена!');
                    break;

                case 'unlocked':
                    $topic->update(['locked' => 0]);
                    setFlash('success', 'Тема успешно откреплена!');
                    break;

                default:
                    setFlash('danger', 'Не выбрано действие для темы!');
            endswitch;

        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/topic/' . $topic->id . '?page=' . $page);
    }

    /**
     * Удаление тем
     */
    public function deleteTopic($id)
    {
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($validator->isValid()) {

            // Удаление загруженных файлов
            removeDir(UPLOADS . '/forum/' . $topic->id);

            $filtered = $topic->posts->load('files')->filter(function ($post) {
                return $post->files->isNotEmpty();
            });

            $filtered->each(function($post) {
                $post->delete();
            });

            // Удаление голосований
            $topic->vote->delete();
            $topic->vote->answers()->delete();
            $topic->vote->pollings()->delete();

            // Удаление закладок
            $topic->bookmarks()->delete();

            $topic->posts()->delete();
            $topic->delete();

            // Обновление счетчиков
            $topic->forum->restatement();

            setFlash('success', 'Тема успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/forum/' . $topic->forum->id . '?page=' . $page);
    }

    /**
     * Просмотр темы
     */
    public function topic($id)
    {
        $topic = Topic::query()->where('id', $id)->with('forum.parent')->first();

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $total = Post::query()->where('topic_id', $topic->id)->count();
        $page = paginate(setting('forumpost'), $total);

        $posts = Post::query()
            ->select('posts.*', 'pollings.vote')
            ->where('topic_id', $topic->id)
            ->leftJoin('pollings', function ($join) {
                $join->on('posts.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('files', 'user', 'editUser')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->get();

        // Кураторы
        if ($topic->moderators) {
            $topic->curators = User::query()->whereIn('id', explode(',', $topic->moderators))->get();
        }

        // Голосование
        $vote = Vote::query()->where('topic_id', $topic->id)->first();

        if ($vote) {
            $vote->poll = $vote->pollings()
                ->where('user_id', getUser('id'))
                ->first();

            if ($vote->answers->isNotEmpty()) {

                $results = array_pluck($vote->answers, 'result', 'answer');
                $max = max($results);

                arsort($results);

                $vote->voted = $results;

                $vote->sum = ($vote->count > 0) ? $vote->count : 1;
                $vote->max = ($max > 0) ? $max : 1;
            }
        }

        return view('admin/forum/topic', compact('topic', 'posts', 'page', 'vote'));
    }

    /**
     * Редактирование сообщения
     */
    public function editPost($id)
    {
        $page = int(Request::input('page', 1));

        $post = Post::query()->find($id);

        if (! $post) {
            abort(404, 'Данного сообщения не существует!');
        }

        if (Request::isMethod('post')) {

            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));


            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, setting('forumtextlength'), ['msg' => 'Слишком длинное или короткое сообщение!']);

            if ($validator->isValid()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => getUser('id'),
                    'updated_at'   => SITETIME,
                ]);

                // ------ Удаление загруженных файлов -------//
                if ($delfile) {
                    $files = File::query()
                        ->where('relate_type', Post::class)
                        ->where('relate_id', $post->id)
                        ->whereIn('id', $delfile)
                        ->get();

                    if ($files->isNotEmpty()) {
                        foreach ($files as $file) {
                            deleteImage('uploads/forum/', $post->topic_id . '/' . $file->hash);
                            $file->delete();
                        }
                    }
                }

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/admin/topic/' . $post->topic_id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/forum/edit_post', compact('post', 'page'));
    }

    /**
     * Удаление тем
     */
    public function deletePosts()
    {
        $tid   = int(Request::input('tid'));
        $page  = int(Request::input('page', 1));
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));

        $topic = Topic::query()->where('id', $tid)->first();

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true($del, 'Отсутствуют выбранные сообщения для удаления!');

        if ($validator->isValid()) {

            $posts = Post::query()
                ->whereIn('id', $del)
                ->get();

            $posts->each(function($post) {
                $post->delete();
            });

            // Обновление счетчиков
            $topic->restatement();

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/topic/' . $topic->id . '?page=' . $page);
    }

    /**
     * Переадресация к последнему сообщению
     */
    public function end($id)
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $end = ceil($topic->count_posts / setting('forumpost'));
        redirect('/admin/topic/' . $topic->id . '?page=' . $end);
    }
}
