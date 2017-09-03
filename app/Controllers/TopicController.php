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
                    ->where('bookmarks.user_id', '=', getUserId());
            })
            ->with('forum.parent')
            ->first();

        $posts = Post::select('posts.*', 'pollings.vote')
            ->where('topic_id', $tid)
            ->leftJoin('pollings', function ($join) {
                $join->on('posts.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Post::class)
                    ->where('pollings.user_id', getUserId());
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
                    ->where('user_id', getUserId())
                    ->update(['posts' => $topic['posts']]);
            }
        }

        // --------------------------------------------------------------//
        if (!empty($topic['moderators'])) {
            $topic['curators'] = User::whereIn('id', explode(',', $topic['moderators']))->get();
            $topic['isModer'] = $topic['curators']->where('id', getUserId())->isNotEmpty();
        }

        // Голосование
        $vote = Vote::where('topic_id', $tid)->first();

        if ($vote) {
            $vote['poll'] = VotePoll::where('vote_id', $vote['id'])
                ->where('user_id', getUserId())
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
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        if (!isUser()) abort(403, 'Авторизуйтесь для добавления сообщения!');

        $topics = DB::run()->queryFetch("SELECT `topics`.*, `forums`.`parent_id` FROM `topics` LEFT JOIN `forums` ON `topics`.`forum_id`=`forums`.`id` WHERE `topics`.`id`=? LIMIT 1;", [$tid]);

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('not_empty', $topics, ['msg' => 'Выбранная вами тема не существует, возможно она была удалена!'])
            ->addRule('empty', $topics['closed'], ['msg' => 'Запрещено писать в закрытую тему!'])
            ->addRule('equal', [Flood::isFlood(), true], ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' сек!'])
            ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, setting('forumtextlength'));

        // Проверка сообщения на схожесть
        $post = DB::run()->queryFetch("SELECT * FROM `posts` WHERE `topic_id`=? ORDER BY `id` DESC LIMIT 1;", [$tid]);
        $validation->addRule('not_equal', [$msg, $post['text']], ['msg' => 'Ваше сообщение повторяет предыдущий пост!']);

        if ($validation->run()) {

            $msg = antimat($msg);

            if (getUserId() == $post['user_id'] && $post['created_at'] + 600 > SITETIME && (utfStrlen($msg) + utfStrlen($post['text']) <= setting('forumtextlength'))) {

                $newpost = $post['text'] . "\n\n" . '[i][size=1]Добавлено через ' . makeTime(SITETIME - $post['created_at']) . ' сек.[/size][/i]' . "\n" . $msg;

                DB::run()->query("UPDATE `posts` SET `text`=? WHERE `id`=? LIMIT 1;", [$newpost, $post['id']]);
                $lastid = $post['id'];

            } else {

                DB::run()->query("INSERT INTO `posts` (`topic_id`, `user_id`, `text`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?);", [$tid, getUserId(), $msg, SITETIME, getClientIp(), getUserAgent()]);
                $lastid = DB::run()->lastInsertId();

                DB::run()->query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `id`=? LIMIT 1;", [getUserId()]);

                DB::run()->query("UPDATE `topics` SET `posts`=`posts`+1, `last_post_id`=? WHERE `id`=?;", [$lastid, $tid]);

                DB::run()->query("UPDATE `forums` SET `posts`=`posts`+1, `last_topic_id`=? WHERE `id`=?;", [$tid, $topics['forum_id']]);
                // Обновление родительского форума
                if ($topics['parent_id'] > 0) {
                    DB::run()->query("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$tid, $topics['parent_id']]);
                }
            }

            // Рассылка уведомлений в приват
            $parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $msg);

            preg_match_all('|\[b\](.*?)\[\/b\]|s', $parseText, $matches);

            if (isset($matches[1])) {
                $usersAnswer = array_unique($matches[1]);

                $newTopic = Topic::find($tid);
                foreach ($usersAnswer as $login) {

                    if ($login == getUsername()) {
                        continue;
                    }

                    $user = User::where('login', $login)->first();

                    if ($user['login']) {

                        if ($user['notify']) {
                            sendPrivate($user['login'], getUsername(), 'Пользователь ' . getUsername() . ' ответил вам в теме [url=' . setting('home') . '/topic/' . $newTopic['id'] . '?page=' . ceil($newTopic['posts'] / setting('forumpost')) . '#post_' . $lastid . ']' . $newTopic['title'] . '[/url]' . PHP_EOL . 'Текст сообщения: ' . $msg);
                        }
                    }
                }
            }

            // Загрузка файла
            if (!empty($_FILES['file']['name']) && !empty($lastid)) {
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

                                if (!file_exists(HOME . '/uploads/forum/' . $topics['id'])) {
                                    $old = umask(0);
                                    mkdir(HOME . '/uploads/forum/' . $topics['id'], 0777, true);
                                    umask($old);
                                }

                                $num = 0;
                                $hash = $lastid . '.' . $ext;
                                while (file_exists(HOME . '/uploads/forum/' . $topics['id'] . '/' . $hash)) {
                                    $num++;
                                    $hash = $lastid . '_' . $num . '.' . $ext;
                                }

                                move_uploaded_file($_FILES['file']['tmp_name'], HOME . '/uploads/forum/' . $topics['id'] . '/' . $hash);

                                $file = new File();
                                $file->relate_type = Post::class;
                                $file->relate_id = $lastid;
                                $file->hash = $hash;
                                $file->name = $filename;
                                $file->size = $filesize;
                                $file->user_id = getUserId();
                                $file->created_at = SITETIME;
                                $file->save();

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

        redirect('/topic/' . $tid . '/end');
    }

    /**
     * Удаление сообщений
     */
    public function delete($tid)
    {
        $token = check(Request::input('token'));
        $del   = intar(Request::input('del'));
        $page  = abs(intval(Request::input('page')));

        $topic = DB::run()->queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

        $isModer = in_array(getUserId(), explode(',', $topic['moderators'])) ? true : false;

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', isUser(), 'Для закрытия тем необходимо авторизоваться')
            ->addRule('not_empty', $del, 'Отстутствуют выбранные сообщения для удаления!')
            ->addRule('empty', $topic['closed'], 'Редактирование невозможно. Данная тема закрыта!')
            ->addRule('equal', [$isModer, true], 'Удалять сообщения могут только кураторы темы!');

        if ($validation->run()) {

            $del = implode(',', $del);

            // ------ Удаление загруженных файлов -------//
            $queryfiles = DB::run()->query("SELECT `hash` FROM `files` WHERE `relate_id` IN (" . $del . ") AND relate_type=?;", [Post::class]);
            $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($files)) {
                foreach ($files as $file) {
                    deleteImage('uploads/forum/', $topic['id'] . '/' . $file);
                }
                DB::run()->query("DELETE FROM `files` WHERE `relate_id` IN (" . $del . ")  AND relate_type=?;", [Post::class]);
            }

            $delposts = DB::run()->exec("DELETE FROM `posts` WHERE `id` IN (" . $del . ") AND `topic_id`=" . $tid . ";");
            DB::run()->query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $tid]);
            DB::run()->query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $topic['forum_id']]);

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
            ->addRule('max', [user('point'), setting('editforumpoint')], 'Для закрытия тем вам необходимо набрать ' . points(setting('editforumpoint')) . '!')
            ->addRule('not_empty', $topic, 'Выбранная вами тема не существует, возможно она была удалена!')
            ->addRule('equal', [$topic['user_id'], getUserId()], 'Вы не автор данной темы!')
            ->addRule('empty', $topic['closed'], 'Данная тема уже закрыта!');

        if ($validation->run()) {

            DB::run()->query("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [1, $tid]);

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
        if (!isUser()) abort(403, 'Авторизуйтесь для изменения темы!');

        if (user('point') < setting('editforumpoint')) {
            abort('default', 'У вас недостаточно актива для изменения темы!');
        }

        $topic = Topic::find($tid);

        if (empty($topic)) {
            abort('default', 'Выбранная вами тема не существует, возможно она была удалена!');
        }

        if ($topic['user_id'] !== getUserId()) {
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
                $msg = antimat($msg);

                DB::run()->query("UPDATE `topics` SET `title`=? WHERE id=?;", [$title, $tid]);

                if ($post) {
                    DB::run()->query("UPDATE `posts` SET `user_id`=?, `text`=?, `ip`=?, `brow`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [getUserId(), $msg, getClientIp(), getUserAgent(), getUserId(), SITETIME, $post['id']]);
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
    public function editpost($tid, $id)
    {
        $page = abs(intval(Request::input('page')));

        if (!isUser()) abort(403, 'Авторизуйтесь для изменения сообщения!');

        $post = Post::select('posts.*', 'moderators', 'closed')
            ->leftJoin('topics', 'posts.topic_id', '=', 'topics.id')
            ->where('posts.id', $id)->first();

        $isModer = in_array(getUserId(), explode(',', $post['moderators'], true)) ? true : false;

        if (empty($post)) {
            abort('default', 'Данного сообщения не существует!');
        }

        if ($post['closed']) {
            abort('default', 'Редактирование невозможно, данная тема закрыта!');
        }

        if (!$isModer && $post['user_id'] != getUserId()) {
            abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
        }

        if (!$isModer && $post['created_at'] + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, setting('forumtextlength'));

            if ($validation->run()) {

                $msg = antimat($msg);

                DB::run()->query("UPDATE `posts` SET `text`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [$msg, getUserId(), SITETIME, $id]);

                // ------ Удаление загруженных файлов -------//
                if ($delfile) {
                    $del = implode(',', $delfile);
                    $queryfiles = DB::run()->query("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (" . $del . ");", [$id, Post::class]);
                    $files = $queryfiles->fetchAll();

                    if (!empty($files)) {
                        foreach ($files as $file) {
                            deleteImage('uploads/forum/', $post['topic_id'] . '/' . $file['hash']);
                        }
                        DB::run()->query("DELETE FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (" . $del . ");", [$id, Post::class]);
                    }
                }

                setFlash('success', 'Сообщение успешно отредактировано!');
                redirect('/topic/' . $tid . '?page=' . $page);

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $queryfiles = DB::run()->query("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=?;", [$id, Post::class]);
        $files = $queryfiles->fetchAll();

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
            ->where('user_id', getUserId())
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

            $vote->count = DB::raw('count + 1');
            $vote->save();

            $voteAnswer->result = DB::raw('result + 1');
            $voteAnswer->save();

            $votePoll = new VotePoll();
            $votePoll->vote_id = $vote['id'];
            $votePoll->user_id = getUserId();
            $votePoll->created_at = SITETIME;
            $votePoll->save();

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
