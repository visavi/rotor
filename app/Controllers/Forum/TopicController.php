<?php

namespace App\Controllers\Forum;

use App\Controllers\BaseController;
use App\Models\File;
use App\Models\Polling;
use App\Models\Post;
use App\Models\Bookmark;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use App\Models\Flood;
use App\Classes\Request;
use App\Classes\Validator;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class TopicController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($id)
    {
        $topic = Topic::query()
            ->select('topics.*', 'bookmarks.count_posts as bookmark_posts')
            ->where('topics.id', $id)
            ->leftJoin('bookmarks', function (JoinClause $join) {
                $join->on('topics.id', '=', 'bookmarks.topic_id')
                    ->where('bookmarks.user_id', '=', getUser('id'));
            })
            ->with('forum.parent')
            ->first();

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $total = Post::query()->where('topic_id', $topic->id)->count();
        $page = paginate(setting('forumpost'), $total);

        $posts = Post::query()
            ->select('posts.*', 'pollings.vote')
            ->where('topic_id', $topic->id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('posts.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('files', 'user', 'editUser')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at')
            ->get();

        if (getUser()) {
            if ($topic->count_posts > $topic->bookmark_posts) {
                Bookmark::query()
                    ->where('topic_id', $topic->id)
                    ->where('user_id', getUser('id'))
                    ->update(['count_posts' => $topic->count_posts]);
            }
        }

        // Кураторы
        if ($topic->moderators) {
            $topic->curators = User::query()->whereIn('id', explode(',', $topic->moderators))->get();
            $topic->isModer = $topic->curators->where('id', getUser('id'))->isNotEmpty();
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

        return view('forums/topic', compact('topic', 'posts', 'page', 'vote'));
    }

    /**
     * Создание сообщения
     */
    public function create($id)
    {
        $msg   = check(Request::input('msg'));
        $token = check(Request::input('token'));
        $files = (array) Request::file('files');

        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для добавления сообщения!');
        }

        $topic = Topic::query()
            ->select('topics.*', 'forums.parent_id')
            ->where('topics.id', $id)
            ->leftJoin('forums', 'topics.forum_id', '=', 'forums.id')
            ->first();

        if (! $topic) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->empty($topic->closed, ['msg' => 'Запрещено писать в закрытую тему!'])
            ->equal(Flood::isFlood(), true, ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!'])
            ->length($msg, 5, setting('forumtextlength'), ['msg' => 'Слишком длинное или короткое сообщение!']);

        // Проверка сообщения на схожесть
        $post = Post::query()->where('topic_id', $topic->id)->orderBy('id', 'desc')->first();
        $validator->notEqual($msg, $post['text'], ['msg' => 'Ваше сообщение повторяет предыдущий пост!']);

        if ($files && $validator->isValid()) {
            $validator
                ->lte(count($files), setting('maxfiles'), ['files' => 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов'])
                ->gte(getUser('point'), setting('forumloadpoints'), 'У вас недостаточно актива для загрузки файлов!');

            $rules = [
                'maxsize'    => setting('forumloadsize'),
                'extensions' => explode(',', setting('forumextload')),
            ];

            foreach ($files as $file) {
                $validator->file($file, $rules, ['files' => 'Не удалось загрузить файл!']);
            }
        }

        if ($validator->isValid()) {

            $msg = antimat($msg);

            if (
                $post &&
                $post->created_at + 600 > SITETIME &&
                getUser('id') === $post->user_id &&
                (utfStrlen($msg) + utfStrlen($post->text) <= setting('forumtextlength')) &&
                count($files) + $post->files->count() <= setting('maxfiles')
            ) {

                $newpost = $post->text . "\n\n" . '[i][size=1]Добавлено через ' . makeTime(SITETIME - $post->created_at) . ' сек.[/size][/i]' . "\n" . $msg;

                $post->update([
                    'text' => $newpost,
                ]);
            } else {

                $post = Post::query()->create([
                    'topic_id'   => $topic->id,
                    'user_id'    => getUser('id'),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user->update([
                    'allforum' => DB::raw('allforum + 1'),
                    'point'    => DB::raw('point + 1'),
                    'money'    => DB::raw('money + 5'),
                ]);

                $topic->update([
                    'count_posts'  => DB::raw('count_posts + 1'),
                    'last_post_id' => $post->id,
                    'updated_at'   => SITETIME,
                ]);

                $topic->forum->update([
                    'count_posts'   => DB::raw('count_posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($topic->forum->parent_id) {
                    $topic->forum->parent->update([
                        'last_topic_id' => $topic->id,
                    ]);
                }
            }

            // Рассылка уведомлений в приват
            sendNotify($msg, '/topics/' . $topic->id . '/' . $post->id, $topic->title);

            if ($files) {
                foreach ($files as $file) {
                    $post->uploadFile($file);
                }
            }

            setFlash('success', 'Сообщение успешно добавлено!');

        } else {
            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/topics/end/' . $topic->id);
    }

    /**
     * Удаление сообщений
     */
    public function delete($id)
    {
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $page  = int(Request::input('page'));

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $isModer = in_array(getUser('id'), explode(',', $topic->moderators)) ? true : false;

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(getUser(), 'Для закрытия тем необходимо авторизоваться')
            ->notEmpty($del, 'Отстутствуют выбранные сообщения для удаления!')
            ->empty($topic->closed, 'Редактирование невозможно. Данная тема закрыта!')
            ->equal($isModer, true, 'Удалять сообщения могут только кураторы темы!');

        if ($validator->isValid()) {

            // ------ Удаление загруженных файлов -------//
            $files = File::query()
                ->where('relate_type', Post::class)
                ->whereIn('relate_id', $del)
                ->get();

            if ($files->isNotEmpty()) {
                foreach ($files as $file) {
                    deleteFile(UPLOADS . '/forums/' . $file->hash);
                    $file->delete();
                }
            }

            $delPosts = Post::query()->whereIn('id', $del)->delete();

            $topic->decrement('count_posts', $delPosts);
            $topic->forum->decrement('count_posts', $delPosts);

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/topics/' . $topic->id . '?page=' . $page);
    }

    /**
     * Закрытие темы
     */
    public function close($id)
    {
        $token = check(Request::input('token'));

        $topic = Topic::query()->find($id);

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(getUser(), 'Для закрытия тем необходимо авторизоваться')
            ->gte(getUser('point'), setting('editforumpoint'), 'Для закрытия тем вам необходимо набрать ' . plural(setting('editforumpoint'), setting('scorename')) . '!')
            ->notEmpty($topic, 'Выбранная вами тема не существует, возможно она была удалена!')
            ->equal($topic['user_id'], getUser('id'), 'Вы не автор данной темы!')
            ->empty($topic->closed, 'Данная тема уже закрыта!');

        if ($validator->isValid()) {

            $topic->update(['closed' => 1]);

            $vote = Vote::query()->where('topic_id', $topic->id)->first();
            if ($vote) {

                $vote->closed = 1;
                $vote->save();

                $vote->pollings()->delete();
            }

            setFlash('success', 'Тема успешно закрыта!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/topics/' . $topic->id);
    }

    /**
     * Редактирование темы
     */
    public function edit($id)
    {
        if (! getUser()) {
            abort(403, 'Авторизуйтесь для изменения темы!');
        }

        if (getUser('point') < setting('editforumpoint')) {
            abort('default', 'У вас недостаточно актива для изменения темы!');
        }

        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        if ($topic->user_id !== getUser('id')) {
            abort('default', 'Изменение невозможно, вы не автор данной темы!');
        }

        if ($topic->closed) {
            abort('default', ' Изменение невозможно, данная тема закрыта!');
        }

        $post = Post::query()->where('topic_id', $topic->id)
            ->orderBy('id')
            ->first();

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $msg   = check(Request::input('msg'));


            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название темы!']);

            if ($post) {
                $validator->length($msg, 5, setting('forumtextlength'), ['msg' => 'Слишком длинный или короткий текст сообщения!']);
            }

            if ($validator->isValid()) {

                $title = antimat($title);
                $msg   = antimat($msg);

                $topic->update(['title' => $title]);

                if ($post) {
                    $post->update([
                        'text'         => $msg,
                        'edit_user_id' => getUser('id'),
                        'updated_at'   => SITETIME,
                    ]);
                }

                setFlash('success', 'Тема успешно изменена!');
                redirect('/topics/' . $topic->id);

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/topic_edit', compact('post', 'topic'));
    }

    /**
     * Редактирование сообщения
     */
    public function editPost($id)
    {
        $page = int(Request::input('page'));

        if (! getUser()) {
            abort(403, 'Авторизуйтесь для изменения сообщения!');
        }

        $post = Post::query()
            ->select('posts.*', 'moderators', 'closed')
            ->leftJoin('topics', 'posts.topic_id', '=', 'topics.id')
            ->where('posts.id', $id)
            ->first();

        $isModer = in_array(getUser('id'), explode(',', $post->moderators)) ? true : false;

        if (! $post) {
            abort(404, 'Данного сообщения не существует!');
        }

        if ($post->closed) {
            abort('default', 'Редактирование невозможно, данная тема закрыта!');
        }

        if (! $isModer && $post->user_id != getUser('id')) {
            abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
        }

        if (! $isModer && $post->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
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
                            deleteFile(UPLOADS . '/forums/' . $file->hash);
                            $file->delete();
                        }
                    }
                }

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/topics/' . $post->topic_id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/topic_edit_post', compact('post', 'page'));
    }

    /**
     * Голосование
     */
    public function vote($id)
    {
        if (! getUser()) {
            abort(403, 'Авторизуйтесь для голосования!');
        }

        $vote = Vote::query()->where('topic_id', $id)->first();

        if (! $vote) {
            abort(404, 'Голосование не найдено!');
        }

        $token = check(Request::input('token'));
        $poll  = int(Request::input('poll'));
        $page  = int(Request::input('page'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        if ($vote->closed) {
            $validator->addError('Данное голосование закрыто!');
        }

        $polling = $vote->pollings()
            ->where('user_id', getUser('id'))
            ->first();

        if ($polling) {
            $validator->addError('Вы уже проголосовали в этом опросе!');
        }

        $voteAnswer = $vote->answers()
            ->where('id', $poll)
            ->where('vote_id', $vote->id)
            ->first();

        if (! $voteAnswer) {
            $validator->addError('Вы не выбрали вариант ответа!');
        }

        if ($validator->isValid()) {

            $vote->increment('count');
            $voteAnswer->increment('result');

            Polling::query()->create([
                'relate_type' => Vote::class,
                'relate_id'   => $vote->id,
                'user_id'     => getUser('id'),
                'vote'        => '+',
                'created_at'  => SITETIME,
            ]);

            setFlash('success', 'Ваш голос успешно принят!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/topics/' . $vote->topic_id . '?page=' . $page);
    }

    /**
     * Печать темы
     */
    public function print($id)
    {
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('forums/print', compact('topic', 'posts'));
    }

    /**
     * Переход к сообщению
     */
    public function viewpost($id, $pid)
    {
        $countTopics = Post::query()
            ->where('id', '<=', $pid)
            ->where('topic_id', $id)
            ->count();

        if (! $countTopics) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $end = ceil($countTopics / setting('forumpost'));
        redirect('/topics/' . $id . '?page=' . $end . '#post_' . $pid);
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
        redirect('/topics/' . $topic->id . '?page=' . $end);
    }
}
