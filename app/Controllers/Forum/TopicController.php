<?php

declare(strict_types=1);

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
use App\Classes\Validator;
use App\Models\VoteAnswer;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TopicController extends BaseController
{
    /**
     * Главная страница
     *
     * @param int $id
     * @return string
     */
    public function index(int $id): string
    {
        $topic = Topic::query()
            ->select('topics.*', 'bookmarks.count_posts as bookmark_posts')
            ->where('topics.id', $id)
            ->leftJoin('bookmarks', static function (JoinClause $join) {
                $join->on('topics.id', 'bookmarks.topic_id')
                    ->where('bookmarks.user_id', getUser('id'));
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
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('posts.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('files', 'user', 'editUser')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at')
            ->get();

        if ($topic->count_posts > $topic->bookmark_posts && getUser()) {
            Bookmark::query()
                ->where('topic_id', $topic->id)
                ->where('user_id', getUser('id'))
                ->update(['count_posts' => $topic->count_posts]);
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

                $results = Arr::pluck($vote->answers, 'result', 'answer');
                $max = max($results);

                arsort($results);

                $vote->voted = $results;

                $vote->sum = ($vote->count > 0) ? $vote->count : 1;
                $vote->max = ($max > 0) ? $max : 1;
            }
        }

        $description = $posts->first() ? truncateWord(bbCode($posts->first()->text)) : $topic->title;

        return view('forums/topic', compact('topic', 'posts', 'page', 'vote', 'description'));
    }

    /**
     * Создание сообщения
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     * @return void
     */
    public function create(int $id, Request $request, Validator $validator, Flood $flood): void
    {
        $msg   = check($request->input('msg'));
        $token = check($request->input('token'));
        $files = (array) $request->file('files');

        if (! $user = getUser()) {
            abort(403, 'Авторизуйтесь для добавления сообщения!');
        }

        $topic = Topic::query()
            ->select('topics.*', 'forums.parent_id')
            ->where('topics.id', $id)
            ->leftJoin('forums', 'topics.forum_id', 'forums.id')
            ->first();

        if (! $topic) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
            ->empty($topic->closed, ['msg' => 'Запрещено писать в закрытую тему!'])
            ->false($flood->isFlood(), ['msg' => trans('validator.flood', ['sec' => $flood->getPeriod()])])
            ->length($msg, 5, setting('forumtextlength'), ['msg' => trans('validator.text')]);

        // Проверка сообщения на схожесть
        /** @var Post $post */
        $post = Post::query()->where('topic_id', $topic->id)->orderBy('id', 'desc')->first();
        $validator->notEqual($msg, $post->text, ['msg' => 'Ваше сообщение повторяет предыдущий пост!']);

        if ($files && $validator->isValid()) {
            $validator
                ->lte(\count($files), setting('maxfiles'), ['files' => 'Разрешено загружать не более ' . setting('maxfiles') . ' файлов'])
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
                \count($files) + $post->files->count() <= setting('maxfiles')
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

                $user->increment('allforum');
                $user->increment('point');
                $user->increment('money', 5);

                $topic->update([
                    'count_posts'  => DB::connection()->raw('count_posts + 1'),
                    'last_post_id' => $post->id,
                    'updated_at'   => SITETIME,
                ]);

                $topic->forum->update([
                    'count_posts'   => DB::connection()->raw('count_posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($topic->forum->parent_id) {
                    $topic->forum->parent->update([
                        'last_topic_id' => $topic->id,
                    ]);
                }
            }

            $flood->saveState();
            sendNotify($msg, '/topics/' . $topic->id . '/' . $post->id, $topic->title);

            if ($files) {
                foreach ($files as $file) {
                    $post->uploadFile($file);
                }
            }

            setFlash('success', 'Сообщение успешно добавлено!');

        } else {
            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        redirect('/topics/end/' . $topic->id);
    }

    /**
     * Удаление сообщений
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));
        $del   = intar($request->input('del'));
        $page  = int($request->input('page'));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $isModer = \in_array(getUser('id'), array_map('\intval', explode(',', $topic->moderators)), true) ? true : false;

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
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
                    deleteFile(HOME . $file->hash);
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
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function close(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        $validator->equal($token, $_SESSION['token'], trans('validator.token'))
            ->true(getUser(), 'Для закрытия тем необходимо авторизоваться')
            ->gte(getUser('point'), setting('editforumpoint'), 'Для закрытия тем вам необходимо набрать ' . plural(setting('editforumpoint'), setting('scorename')) . '!')
            ->notEmpty($topic, 'Выбранная вами тема не существует, возможно она была удалена!')
            ->equal($topic->user_id, getUser('id'), 'Вы не автор данной темы!')
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
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! getUser()) {
            abort(403, 'Авторизуйтесь для изменения темы!');
        }

        if (getUser('point') < setting('editforumpoint')) {
            abort('default', 'У вас недостаточно актива для изменения темы!');
        }

        /** @var Topic $topic */
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

        /** @var Vote $vote */
        $vote = Vote::query()->where('topic_id', $id)->first();

        if ($request->isMethod('post')) {
            $token    = check($request->input('token'));
            $title    = check($request->input('title'));
            $msg      = check($request->input('msg'));
            $question = check($request->input('question'));
            $answers  = check((array) $request->input('answers'));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($title, 5, 50, ['title' => trans('validator.title')]);

            if ($post) {
                $validator->length($msg, 5, setting('forumtextlength'), ['msg' => trans('validator.text')]);
            }

            if ($vote) {
                $validator->length($question, 5, 100, ['question' => trans('validator.text')]);

                if ($answers) {
                    $validator->empty($vote->count, ['question' => 'Изменение вариантов ответа доступно только до голосований!']);

                    $answers = array_unique(array_diff($answers, ['']));

                    foreach ($answers as $answer) {
                        if (utfStrlen($answer) > 50) {
                            $validator->addError(['answers' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                            break;
                        }
                    }

                    $validator->between(\count($answers), 2, 10, ['answers' => 'Недостаточное количество вариантов ответов!']);
                }
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

                if ($vote) {
                    $vote->update([
                        'title' => $question,
                    ]);

                    if ($answers) {
                        $countAnswers = $vote->answers()->count();

                        foreach ($answers as $answerId => $answer) {
                            /** @var VoteAnswer $ans */
                            $ans = $vote->answers()->firstOrNew(['id' => $answerId]);

                            if ($ans->exists) {
                                $ans->update(['answer' => $answer]);
                            } else if ($countAnswers < 10) {
                                $ans->fill(['answer' => $answer])->save();
                                $countAnswers++;
                            }
                        }
                    }
                }

                setFlash('success', 'Тема успешно изменена!');
                redirect('/topics/' . $topic->id);

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        if ($vote) {
            $vote->getAnswers = $vote->answers->pluck('answer', 'id')->all();
        }

        return view('forums/topic_edit', compact('post', 'topic', 'vote'));
    }

    /**
     * Редактирование сообщения
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editPost(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page'));

        if (! getUser()) {
            abort(403, 'Авторизуйтесь для изменения сообщения!');
        }

        $post = Post::query()
            ->select('posts.*', 'moderators', 'closed')
            ->leftJoin('topics', 'posts.topic_id', 'topics.id')
            ->where('posts.id', $id)
            ->first();

        $isModer = \in_array(getUser('id'), array_map('\intval', explode(',', $post->moderators)), true) ? true : false;

        if (! $post) {
            abort(404, 'Данного сообщения не существует!');
        }

        if ($post->closed) {
            abort('default', 'Редактирование невозможно, данная тема закрыта!');
        }

        if (! $isModer && $post->user_id !== getUser('id')) {
            abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
        }

        if (! $isModer && $post->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {
            $token   = check($request->input('token'));
            $msg     = check($request->input('msg'));
            $delfile = intar($request->input('delfile'));

            $validator->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($msg, 5, setting('forumtextlength'), ['msg' => trans('validator.text')]);

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
                            deleteFile(HOME . $file->hash);
                            $file->delete();
                        }
                    }
                }

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/topics/' . $post->topic_id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('forums/topic_edit_post', compact('post', 'page'));
    }

    /**
     * Голосование
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return void
     */
    public function vote(int $id, Request $request, Validator $validator): void
    {
        if (! getUser()) {
            abort(403, 'Авторизуйтесь для голосования!');
        }

        $vote = Vote::query()->where('topic_id', $id)->first();

        if (! $vote) {
            abort(404, 'Голосование не найдено!');
        }

        $token = check($request->input('token'));
        $poll  = int($request->input('poll'));
        $page  = int($request->input('page'));

        $validator->equal($token, $_SESSION['token'], trans('validator.token'));

        if ($vote->closed) {
            $validator->addError('Данное голосование закрыто!');
        }

        $polling = $vote->pollings()
            ->where('user_id', getUser('id'))
            ->first();

        if ($polling) {
            $validator->addError('Вы уже проголосовали в этом опросе!');
        }

        /** @var VoteAnswer $voteAnswer */
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
     *
     * @param int $id
     * @return string
     */
    public function print(int $id): string
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Данной темы не существует!');
        }

        $posts = Post::query()
            ->where('topic_id', $topic->id)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $description = $posts->first() ? truncateWord(bbCode($posts->first()->text)) : $topic->title;

        return view('forums/print', compact('topic', 'posts', 'description'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $pid
     * @return void
     */
    public function viewpost(int $id, int $pid): void
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
     *
     * @param $id
     * @return void
     */
    public function end(int $id): void
    {
        /** @var Topic $topic */
        $topic = Topic::query()->find($id);

        if (! $topic) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $end = ceil($topic->count_posts / setting('forumpost'));
        redirect('/topics/' . $topic->id . '?page=' . $end);
    }
}
