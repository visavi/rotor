<?php

namespace App\Controllers;

use App\Models\File;
use App\Models\Post;
use App\Models\Bookmark;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteAnswer;
use App\Models\VotePoll;
use App\Models\Flood;
use App\Classes\Request;
use App\Classes\Validation;
use Illuminate\Database\Capsule\Manager as DB;

class TopicController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($tid)
    {
        $total = Post::where('topic_id', $tid)->count();
        $page = paginate(setting('forumpost'), $total);

        $topic = Topic::select('topics.*', 'bookmarks.posts as bookmark_posts')
            ->where('topics.id', $tid)
            ->leftJoin('bookmarks', function ($join) {
                $join->on('topics.id', '=', 'bookmarks.topic_id')
                    ->where('bookmarks.user_id', '=', user('id'));
            })
            ->with('forum.parent')
            ->first();

        $posts = Post::select('posts.*', 'pollings.vote')
            ->where('topic_id', $tid)
            ->leftJoin('pollings', function ($join) {
                $join->on('posts.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::class)
                    ->where('pollings.user_id', user('id'));
            })
            ->with('files', 'user', 'editUser')
            ->offset($page['offset'])
            ->limit(setting('forumpost'))
            ->orderBy('created_at', 'asc')
            ->get();

        if (!$topic) {
            abort('default', 'Данной темы не существует!');
        }

        if (isUser()) {
            if ($topic['posts'] > $topic['bookmark_posts']) {
                Bookmark::where('topic_id', $tid)
                    ->where('user_id', user('id'))
                    ->update(['posts' => $topic['posts']]);
            }
        }

        // --------------------------------------------------------------//
        if (!empty($topic['moderators'])) {
            $topic['curators'] = User::whereIn('id', explode(',', $topic['moderators']))->get();
            $topic['isModer'] = $topic['curators']->where('id', user('id'))->isNotEmpty();
        }

        // Голосование
        $vote = Vote::where('topic_id', $tid)->first();

        if ($vote) {
            $vote['poll'] = VotePoll::where('vote_id', $vote['id'])
                ->where('user_id', user('id'))
                ->first();

            $vote['answers'] = VoteAnswer::where('vote_id', $vote['id'])
                ->orderBy('id')
                ->get();

            if ($vote['answers']) {

                $results = array_pluck($vote['answers'], 'result', 'answer');
                $max = max($results);

                arsort($results);

                $vote['voted'] = $results;

                $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
                $vote['max'] = ($max > 0) ? $max : 1;
            }
        }

        return view('forum/topic', compact('topic', 'posts', 'page', 'vote'));
    }

    /**
     * Создание сообщения
     */
    public function create($tid)
    {
        $msg   = check(Request::input('msg'));
        $token = check(Request::input('token'));

        if (! $user = isUser()) {
            abort(403, 'Авторизуйтесь для добавления сообщения!');
        }

        $topic = Topic::select('topics.*', 'forums.parent_id')
            ->where('topics.id', $tid)
            ->leftJoin('forums', 'topics.forum_id', '=', 'forums.id')
            ->first();

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('not_empty', $topic, ['msg' => 'Выбранная вами тема не существует, возможно она была удалена!'])
            ->addRule('empty', $topic['closed'], ['msg' => 'Запрещено писать в закрытую тему!'])
            ->addRule('equal', [Flood::isFlood(), true], ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!'])
            ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, setting('forumtextlength'));

        // Проверка сообщения на схожесть
        $post = Post::where('topic_id', $topic->id)->orderBy('id', 'desc')->first();
        $validation->addRule('not_equal', [$msg, $post['text']], ['msg' => 'Ваше сообщение повторяет предыдущий пост!']);

        if ($validation->run()) {

            $msg = antimat($msg);

            if (user('id') == $post['user_id'] && $post['created_at'] + 600 > SITETIME && (utfStrlen($msg) + utfStrlen($post['text']) <= setting('forumtextlength'))) {

                $newpost = $post['text'] . "\n\n" . '[i][size=1]Добавлено через ' . makeTime(SITETIME - $post['created_at']) . ' сек.[/size][/i]' . "\n" . $msg;

                $post->update([
                    'text' => $newpost,
                ]);
            } else {

                $post = Post::create([
                    'topic_id'   => $topic->id,
                    'user_id'    => user('id'),
                    'text'       => $msg,
                    'created_at' => SITETIME,
                    'ip'         => getClientIp(),
                    'brow'       => getUserAgent(),
                ]);

                $user->update([
                    'allforum' => DB::raw('allforum + 1'),
                    'point'    => DB::raw('point + 1'),
                    'money'    => DB::raw('money + 5'),
                ]);

                $topic->update([
                    'posts'        => DB::raw('posts + 1'),
                    'last_post_id' => $post->id,
                    'updated_at'   => SITETIME,
                ]);

                $topic->forum->update([
                    'posts'         => DB::raw('posts + 1'),
                    'last_topic_id' => $topic->id,
                ]);

                // Обновление родительского форума
                if ($topic->parent_id) {
                    $topic->forum->parent->update([
                        'last_topic_id' => $topic->id,
                    ]);
                }
            }

            // Рассылка уведомлений в приват
            $parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $msg);

            preg_match_all('|\[b\](.*?)\[\/b\]|s', $parseText, $matches);


            if (isset($matches[1])) {
                $usersAnswer = array_unique($matches[1]);

                foreach ($usersAnswer as $login) {

                    if ($login == user('login')) {
                        continue;
                    }

                    $user = User::where('login', $login)->first();
                    if ($user) {
                        if ($user['notify']) {
                            sendPrivate($user->id, user('id'), 'Пользователь ' . user('login') . ' ответил вам в теме [url=' . setting('home') . '/topic/' . $topic->id . '/' . $post->id . ']' . $topic->title . '[/url]' . PHP_EOL . 'Текст сообщения: ' . $msg);
                        }
                    }
                }
            }

            // Загрузка файла
            if (! empty($_FILES['file']['name'])) {
                if (user('point') >= setting('forumloadpoints')) {
                    if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                        $filename = check($_FILES['file']['name']);
                        $filename = (!isUtf($filename)) ? utfLower(winToUtf($filename)) : utfLower($filename);
                        $filesize = $_FILES['file']['size'];

                        if ($filesize > 0 && $filesize <= setting('forumloadsize')) {
                            $arrext = explode(',', setting('forumextload'));
                            $ext = getExtension($filename);

                            if (in_array($ext, $arrext, true)) {

                                if (utfStrlen($filename) > 50) {
                                    $filename = utfSubstr($filename, 0, 45) . '.' . $ext;
                                }

                                if (!file_exists(HOME . '/uploads/forum/' . $topic->id)) {
                                    $old = umask(0);
                                    mkdir(HOME . '/uploads/forum/' . $topic->id, 0777, true);
                                    umask($old);
                                }

                                $num = 0;
                                $hash = $post->id . '.' . $ext;
                                while (file_exists(HOME . '/uploads/forum/' . $topic->id . '/' . $hash)) {
                                    $num++;
                                    $hash = $post->id . '_' . $num . '.' . $ext;
                                }

                                move_uploaded_file($_FILES['file']['tmp_name'], HOME . '/uploads/forum/' . $topic->id . '/' . $hash);

                                File::create([
                                    'relate_type' => Post::class,
                                    'relate_id'   => $post->id,
                                    'hash'        => $hash,
                                    'name'        => $filename,
                                    'size'        => $filesize,
                                    'user_id'     => user('id'),
                                    'created_at'  => SITETIME,
                                ]);

                            } else {
                                $fileError = 'Файл не загружен! Недопустимое расширение!';
                            }
                        } else {
                            $fileError = 'Файл не загружен! Максимальный размер ' . formatSize(setting('forumloadsize')) . '!';
                        }
                    } else {
                        $fileError = 'Ошибка! Не удалось загрузить файл!';
                    }
                } else {
                    $fileError = 'Ошибка! У вас недостаточно актива для загрузки файлов!';
                }

                if (isset($fileError)) {
                    setFlash('danger', $fileError);
                }
            }

            setFlash('success', 'Сообщение успешно добавлено!');

        } else {
            setInput(Request::all());
            setFlash('danger', $validation->getErrors());
        }

        redirect('/topic/' . $topic->id . '/end');
    }

    /**
     * Удаление сообщений
     */
    public function delete($tid)
    {
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $page  = abs(intval(Request::input('page')));

        $topic = Topic::find($tid);

        $isModer = in_array(user('id'), explode(',', $topic['moderators']), true) ? true : false;

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', isUser(), 'Для закрытия тем необходимо авторизоваться')
            ->addRule('not_empty', $del, 'Отстутствуют выбранные сообщения для удаления!')
            ->addRule('empty', $topic['closed'], 'Редактирование невозможно. Данная тема закрыта!')
            ->addRule('equal', [$isModer, true], 'Удалять сообщения могут только кураторы темы!');

        if ($validation->run()) {

            // ------ Удаление загруженных файлов -------//
            $files = File::where('relate_type', Post::class)
                ->whereIn('relate_id', $del)
                ->get();

            if ($files->isNotEmpty()) {
                foreach ($files as $file) {
                    $file->delete();
                    deleteImage('uploads/forum/', $topic->id . '/' . $file->hash);
                }

            }

            $delPosts = Post::whereIn('id', $del)->delete();

            $topic->decrement('posts', $delPosts);
            $topic->forum->decrement('posts', $delPosts);

            setFlash('success', 'Выбранные сообщения успешно удалены!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect('/topic/' . $tid . '?page=' . $page);
    }

    /**
     * Закрытие темы
     */
    public function close($tid)
    {
        $token = check(Request::input('token'));

        $topic = Topic::find($tid);

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', isUser(), 'Для закрытия тем необходимо авторизоваться')
            ->addRule('max', [user('point'), setting('editforumpoint')], 'Для закрытия тем вам необходимо набрать ' . plural(setting('editforumpoint'), setting('scorename')) . '!')
            ->addRule('not_empty', $topic, 'Выбранная вами тема не существует, возможно она была удалена!')
            ->addRule('equal', [$topic['user_id'], user('id')], 'Вы не автор данной темы!')
            ->addRule('empty', $topic['closed'], 'Данная тема уже закрыта!');

        if ($validation->run()) {

            $topic->update(['closed' => 1]);

            $vote = Vote::where('topic_id', $tid)->first();
            if ($vote) {

                $vote->closed = 1;
                $vote->save();

                VotePoll::where('vote_id', $vote['id'])->delete();
            }

            setFlash('success', 'Тема успешно закрыта!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect('/topic/' . $tid);
    }

    /**
     * Редактирование темы
     */
    public function edit($tid)
    {
        if (! isUser()) {
            abort(403, 'Авторизуйтесь для изменения темы!');
        }

        if (user('point') < setting('editforumpoint')) {
            abort('default', 'У вас недостаточно актива для изменения темы!');
        }

        $topic = Topic::find($tid);

        if (empty($topic)) {
            abort('default', 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        if ($topic['user_id'] !== user('id')) {
            abort('default', 'Изменение невозможно, вы не автор данной темы!');
        }

        if ($topic['closed']) {
            abort('default', ' Изменение невозможно, данная тема закрыта!');
        }

        $post = Post::where('topic_id', $tid)
            ->orderBy('id')
            ->first();

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $msg = check(Request::input('msg'));


            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50);

            if ($post) {
                $validation->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, setting('forumtextlength'));
            }

            if ($validation->run()) {

                $title = antimat($title);
                $msg   = antimat($msg);

                $topic->update(['title' => $title]);

                if ($post) {
                    $post->update([
                        'text'         => $msg,
                        'edit_user_id' => user('id'),
                        'updated_at'   => SITETIME,
                    ]);
                }

                setFlash('success', 'Тема успешно изменена!');
                redirect('/topic/' . $tid);

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('forum/topic_edit', compact('post', 'topic'));
    }

    /**
     * Редактирование сообщения
     */
    public function editPost($tid, $id)
    {
        $page = abs(intval(Request::input('page')));

        if (! isUser()) {
            abort(403, 'Авторизуйтесь для изменения сообщения!');
        }

        $post = Post::select('posts.*', 'moderators', 'closed')
            ->leftJoin('topics', 'posts.topic_id', '=', 'topics.id')
            ->where('posts.id', $id)
            ->first();

        $isModer = in_array(user('id'), explode(',', $post['moderators'], true)) ? true : false;

        if (! $post) {
            abort('default', 'Данного сообщения не существует!');
        }

        if ($post['closed']) {
            abort('default', 'Редактирование невозможно, данная тема закрыта!');
        }

        if (! $isModer && $post['user_id'] != user('id')) {
            abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
        }

        if (! $isModer && $post['created_at'] + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {

            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, setting('forumtextlength'));

            if ($validation->run()) {

                $msg = antimat($msg);

                $post->update([
                    'text'         => $msg,
                    'edit_user_id' => user('id'),
                    'updated_at'   => SITETIME,
                ]);

                // ------ Удаление загруженных файлов -------//
                if ($delfile) {
                    $files = File::where('relate_type', Post::class)
                        ->where('relate_id', $post->id)
                        ->whereIn('id', $delfile)
                        ->get();

                    if ($files->isNotEmpty()) {
                        foreach ($files as $file) {
                            $file->delete();
                            deleteImage('uploads/forum/', $post->topic_id . '/' . $file->hash);
                        }
                    }
                }

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/topic/' . $tid . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $files = File::where('relate_type', Post::class)
            ->where('relate_id', $id)
            ->get();

        return view('forum/topic_edit_post', compact('post', 'files', 'page'));
    }


    /**
     * Голосование
     */
    public function vote($tid)
    {
        if (! isUser()) {
            abort(403, 'Авторизуйтесь для голосования!');
        }

        $vote = Vote::where('topic_id', $tid)->first();
        if (! $vote) {
            abort(404, 'Голосование не найдено!');
        }

        $token = check(Request::input('token'));
        $poll  = abs(intval(Request::input('poll')));
        $page  = abs(intval(Request::input('page')));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

        if ($vote['closed']) {
            $validation->addError('Данное голосование закрыто!');
        }

        $votePoll = VotePoll::where('vote_id', $vote['id'])
            ->where('user_id', user('id'))
            ->first();

        if ($votePoll) {
            $validation->addError('Вы уже проголосовали в этом опросе!');
        }

        $voteAnswer = VoteAnswer::where('id', $poll)
            ->where('vote_id', $vote['id'])
            ->first();

        if (!$voteAnswer) {
            $validation->addError('Вы не выбрали вариант ответа!');
        }

        if ($validation->run()) {

            $vote->increment('count');
            $voteAnswer->increment('result');

            VotePoll::create([
                'vote_id'    => $vote['id'],
                'user_id'    => user('id'),
                'created_at' => SITETIME,
            ]);

            setFlash('success', 'Ваш голос успешно принят!');
        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect('/topic/' . $tid . '?page=' . $page);
    }

    /**
     * Печать темы
     */
    public function print($tid)
    {
        $topic = Topic::find($tid);

        if (empty($topic)) {
            abort('default', 'Данной темы не существует!');
        }

        $posts = Post::where('topic_id', $tid)
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return view('forum/print', compact('topic', 'posts'));
    }

    /**
     * Переход к сообщению
     */
    public function viewpost($tid, $id)
    {

        $countTopics = Post::where('id', '<=', $id)
            ->where('topic_id', $tid)
            ->count();

        if (!$countTopics) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $end = ceil($countTopics / setting('forumpost'));
        redirect('/topic/' . $tid . '?page=' . $end . '#post_' . $id);
    }

    /**
     * Переадресация к последнему сообщению
     */
    public function end($tid)
    {

        $topic = Topic::find($tid);

        if (empty($topic)) {
            abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        $end = ceil($topic['posts'] / setting('forumpost'));
        redirect('/topic/' . $tid . '?page=' . $end);
    }
}
