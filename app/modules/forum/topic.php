<?php

$tid = param('tid');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Post::where('topic_id', $tid)->count();
    $page = App::paginate(App::setting('forumpost'), $total);

    $topic = Topic::where('id', $tid)
        ->with('forum.parent')
        ->with(['bookmark' => function($query) {$query->where('user_id', App::getUserId());}])
        ->first();

    $posts = Post::where('topic_id', $tid)
        ->with('polling', 'files', 'user')
        ->offset($page['offset'])
        ->limit(App::setting('forumpost'))
        ->orderBy('created_at', 'asc')
        ->get();

    if (! $topic) {
        App::abort('default', 'Данной темы не существует!');
    }

    if (is_user()) {
        if ($topic->bookmark && $topic['posts'] > $topic['bookmark']['posts']) {
            Bookmark::where('topic_id', $tid)
                ->where('user_id', App::getUserId())
                ->update(['posts' => $topic['posts']]);
        }
    }

    // --------------------------------------------------------------//
    if (!empty($topic['moderators'])) {
        $topic['curators'] = User::whereIn('id', explode(',', $topic['moderators']))->get();
        $topic['isModer'] = $topic['curators']->where('id', App::getUserId())->isNotEmpty();
    }

    // Голосование
    $vote = Vote::where('topic_id', $tid)->first();

    if ($vote) {
        $vote['poll'] = VotePoll::where('vote_id', $vote['id'])
            ->where('user_id', App::getUserId())
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

    App::view('forum/topic', compact('topic', 'posts', 'page', 'vote'));
break;

############################################################################################
##                                   Добавление сообщения                                 ##
############################################################################################
case 'create':

    $msg = check(Request::input('msg'));
    $token = check(Request::input('token'));

    if (! is_user()) App::abort(403, 'Авторизуйтесь для добавления сообщения!');

    $topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`parent_id` FROM `topics` LEFT JOIN `forums` ON `topics`.`forum_id`=`forums`.`id` WHERE `topics`.`id`=? LIMIT 1;", [$tid]);

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
        -> addRule('not_empty', $topics, ['msg' => 'Выбранная вами тема не существует, возможно она была удалена!'])
        -> addRule('empty', $topics['closed'], ['msg' => 'Запрещено писать в закрытую тему!'])
        -> addRule('equal', [is_flood($log), true], ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' сек!'])
        -> addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, $config['forumtextlength']);

        // Проверка сообщения на схожесть
        $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `topic_id`=? ORDER BY `id` DESC LIMIT 1;", [$tid]);
        $validation -> addRule('not_equal', [$msg, $post['text']], ['msg' => 'Ваше сообщение повторяет предыдущий пост!']);

    if ($validation->run()) {

        $msg = antimat($msg);

        if (App::getUserId() == $post['user_id'] && $post['created_at'] + 600 > SITETIME && (utf_strlen($msg) + utf_strlen($post['text']) <= $config['forumtextlength'])) {

            $newpost = $post['text']."\n\n".'[i][size=1]Добавлено через '.maketime(SITETIME - $post['created_at']).' сек.[/size][/i]'."\n".$msg;

            DB::run() -> query("UPDATE `posts` SET `text`=? WHERE `id`=? LIMIT 1;", [$newpost, $post['id']]);
            $lastid = $post['id'];

        } else {

            DB::run() -> query("INSERT INTO `posts` (`topic_id`, `user_id`, `text`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?);", [$tid, App::getUserId(), $msg, SITETIME, App::getClientIp(), App::getUserAgent()]);
            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `id`=? LIMIT 1;", [App::getUserId()]);

            DB::run() -> query("UPDATE `topics` SET `posts`=`posts`+1, `last_post_id`=? WHERE `id`=?;", [$lastid, $tid]);

            DB::run() -> query("UPDATE `forums` SET `posts`=`posts`+1, `last_topic_id`=? WHERE `id`=?;", [$tid, $topics['forum_id']]);
            // Обновление родительского форума
            if ($topics['parent_id'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$tid, $topics['parent_id']]);
            }
        }

        // Рассылка уведомлений в приват
        $parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $msg);

        preg_match_all('|\[b\](.*?)\[\/b\]|s', $parseText, $matches);

        if (isset($matches[1])) {
            $usersAnswer = array_unique($matches[1]);

            $newTopic = Topic::find($tid);
            foreach($usersAnswer as $login) {

                if ($login == $log) {
                    continue;
                }

                $user = User::where('login', $login)->first();

                if ($user['login']) {

                    if ($user['notify']) {
                        send_private($user['login'], $log, 'Пользователь ' . $log . ' ответил вам в теме [url=' . App::setting('home') . '/topic/' . $newTopic['id'] . '?page=' . ceil($newTopic['posts'] / App::setting('forumpost')) . '#post_'.$lastid.']' . $newTopic['title'] . '[/url]'.PHP_EOL.'Текст сообщения: '.$msg);
                    }
                }
            }
        }

        // Загрузка файла
        if (!empty($_FILES['file']['name']) && !empty($lastid)) {
            if ($udata['point'] >= $config['forumloadpoints']){
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                    $filename = check($_FILES['file']['name']);
                    $filename = (!is_utf($filename)) ? utf_lower(win_to_utf($filename)) : utf_lower($filename);
                    $filesize = $_FILES['file']['size'];

                    if ($filesize > 0 && $filesize <= $config['forumloadsize']) {
                        $arrext = explode(',', $config['forumextload']);
                        $ext = getExtension($filename);

                        if (in_array($ext, $arrext, true)) {

                            if (utf_strlen($filename) > 50) {
                                $filename = utf_substr($filename, 0, 45).'.'.$ext;
                            }

                            if (!file_exists(HOME.'/uploads/forum/'.$topics['id'])){
                                $old = umask(0);
                                mkdir(HOME.'/uploads/forum/'.$topics['id'], 0777, true);
                                umask($old);
                            }

                            $num = 0;
                            $hash = $lastid.'.'.$ext;
                            while(file_exists(HOME.'/uploads/forum/'.$topics['id'].'/'.$hash)){
                                $num++;
                                $hash = $lastid.'_'.$num.'.'.$ext;
                            }

                            move_uploaded_file($_FILES['file']['tmp_name'], HOME.'/uploads/forum/'.$topics['id'].'/'.$hash);

                            $file = new File();
                            $file->relate_type = Post::class;
                            $file->relate_id = $lastid;
                            $file->hash = $hash;
                            $file->name = $filename;
                            $file->size = $filesize;
                            $file->user_id = App::getUserId();
                            $file->created_at = SITETIME;
                            $file->save();

                        } else {
                            $fileError = 'Файл не загружен! Недопустимое расширение!';
                        }
                    } else {
                        $fileError = 'Файл не загружен! Максимальный размер '.formatsize($config['forumloadsize']).'!';
                    }
                } else {
                    $fileError = 'Ошибка! Не удалось загрузить файл!';
                }
            } else {
                $fileError = 'Ошибка! У вас недостаточно актива для загрузки файлов!';
            }

            if (isset($fileError)) {
                App::setFlash('danger', $fileError);
            }
        }

        App::setFlash('success', 'Сообщение успешно добавлено!');

    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid.'/end');
break;

############################################################################################
##                                      Жалоба                                            ##
############################################################################################
case 'complaint':

    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $id    = abs(intval(Request::input('id')));
    $page = abs(intval(Request::input('page')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = Post::find($id);
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');

    $spam = Spam::where('relate_type', Post::class)->where('relate_id', $id)->first();
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = Spam::create([
            'relate_type' => Post::class,
            'relate_id'   => $data['id'],
            'user_id'     => App::getUserId(),
            'link'        => '/topic/'.$data['topic_id'].'?page='.$page,
            'created_at'  => SITETIME,
        ]);

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }

break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $del   = intar(Request::input('del'));
    $page = abs(intval(Request::input('page')));

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

    $isModer = in_array($log, explode(',', $topic['moderators'], true)) ? true : false;

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('bool', is_user(), 'Для закрытия тем необходимо авторизоваться')
        -> addRule('not_empty', $del, 'Отстутствуют выбранные сообщения для удаления!')
        -> addRule('empty', $topic['closed'], 'Редактирование невозможно. Данная тема закрыта!')
        -> addRule('equal', [$isModer, true], 'Удалять сообщения могут только кураторы темы!');

    if ($validation->run()) {

        $del = implode(',', $del);

        // ------ Удаление загруженных файлов -------//
        $queryfiles = DB::run() -> query("SELECT `hash` FROM `files` WHERE `relate_id` IN (".$del.") AND relate_type=?;", [Post::class]);
        $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($files)){
            foreach ($files as $file){
                if (file_exists(HOME.'/uploads/forum/'.$topic['id'].'/'.$file)){
                    unlink(HOME.'/uploads/forum/'.$topic['id'].'/'.$file);
                }
            }
            DB::run() -> query("DELETE FROM `files` WHERE `relate_id` IN (".$del.")  AND relate_type=?;", [Post::class]);
        }

        $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `id` IN (".$del.") AND `topic_id`=".$tid.";");
        DB::run() -> query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $tid]);
        DB::run() -> query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $topic['forum_id']]);

        App::setFlash('success', 'Выбранные сообщения успешно удалены!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid.'?page='.$page);
break;

############################################################################################
##                                       Закрытие темы                                    ##
############################################################################################
case 'close':

    $token = check(Request::input('token'));

    $topic = Topic::find($tid);

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
         -> addRule('bool', is_user(), 'Для закрытия тем необходимо авторизоваться')
         -> addRule('max', [App::user('point'), App::setting('editforumpoint')], 'Для закрытия тем вам необходимо набрать '.points(App::setting('editforumpoint')).'!')
        -> addRule('not_empty', $topic, 'Выбранная вами тема не существует, возможно она была удалена!')
        -> addRule('equal', [$topic['user_id'], App::getUserId()], 'Вы не автор данной темы!')
        -> addRule('empty', $topic['closed'], 'Данная тема уже закрыта!');

    if ($validation->run()) {

        DB::run() -> query("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [1, $tid]);

        $vote = Vote::where('topic_id', $tid)->first();
        if ($vote) {

            $vote->closed = 1;
            $vote->save();

            VotePoll::where('vote_id', $vote['id'])->delete();
        }

        App::setFlash('success', 'Тема успешно закрыта!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid);
break;

############################################################################################
##                                   Подготовка к изменению                               ##
############################################################################################
case 'edit':

    if (! is_user()) App::abort(403, 'Авторизуйтесь для изменения темы!');

    if ($udata['point'] < $config['editforumpoint']) {
        App::abort('default', 'У вас недостаточно актива для изменения темы!');
    }

    $topic = Topic::find($tid);

    if (empty($topic)) {
        App::abort('default', 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    if ($topic['user_id'] !== App::getUserId()) {
        App::abort('default', 'Изменение невозможно, вы не автор данной темы!');
    }

    if ($topic['closed']) {
        App::abort('default', ' Изменение невозможно, данная тема закрыта!');
    }

    $post = Post::where('topic_id', $tid)
        ->orderBy('id')
        ->first();

    if (Request::isMethod('post')) {

        $token = check(Request::input('token'));
        $title = check(Request::input('title'));
        $msg   = check(Request::input('msg'));


        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('string', $title, ['title' => 'Слишком длинное или короткое название темы!'], true, 5, 50);

        if ($post) {
            $validation->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий текст сообщения!'], true, 5, $config['forumtextlength']);
        }
        if ($validation->run()) {

            $title = antimat($title);
            $msg   = antimat($msg);

            DB::run() -> query("UPDATE `topics` SET `title`=? WHERE id=?;", [$title, $tid]);

            if ($post) {
                DB::run()->query("UPDATE `posts` SET `user_id`=?, `text`=?, `ip`=?, `brow`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [App::getUserId(), $msg, App::getClientIp(), App::getUserAgent(), App::getUserId(), SITETIME, $post['id']]);
            }

            App::setFlash('success', 'Тема успешно изменена!');
            App::redirect('/topic/'.$tid);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('forum/topic_edit', compact('post', 'topic'));

break;

############################################################################################
##                                Подготовка к редактированию                             ##
############################################################################################
case 'editpost':

    $id = param('id');
    $page = abs(intval(Request::input('page')));

    if (! is_user()) App::abort(403, 'Авторизуйтесь для изменения сообщения!');

    $post = DB::run() -> queryFetch("SELECT p.*, moderators, closed FROM `posts` p LEFT JOIN `topics` t ON `p`.`topic_id`=`t`.`id` WHERE p.`id`=? LIMIT 1;", [$id]);

    $isModer = in_array($log, explode(',', $post['moderators'], true)) ? true : false;

    if (empty($post)) {
        App::abort('default', 'Данного сообщения не существует!');
    }

    if ($post['closed']) {
        App::abort('default', 'Редактирование невозможно, данная тема закрыта!');
    }

    if (! $isModer && $post['user_id'] != App::getUserId()) {
        App::abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
    }

    if (! $isModer && $post['created_at'] + 600 < SITETIME) {
        App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
    }

    if (Request::isMethod('post')) {

        $token   = check(Request::input('token'));
        $msg     = check(Request::input('msg'));
        $delfile = intar(Request::input('delfile'));

        $validation = new Validation();
        $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, $config['forumtextlength']);

        if ($validation->run()) {

            $msg = antimat($msg);

            DB::run() -> query("UPDATE `posts` SET `text`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [$msg, App::getUserId(), SITETIME, $id]);

            // ------ Удаление загруженных файлов -------//
            if ($delfile) {
                $del = implode(',', $delfile);
                $queryfiles = DB::run() -> query("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$id, Post::class]);
                $files = $queryfiles->fetchAll();

                if (!empty($files)){
                    foreach ($files as $file){
                        if (file_exists(HOME.'/uploads/forum/'.$post['topic_id'].'/'.$file['hash'])){
                            unlink_image('uploads/forum/', $post['topic_id'].'/'.$file['hash']);
                        }
                    }
                    DB::run() -> query("DELETE FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$id, Post::class]);
                }
            }

            App::setFlash('success', 'Сообщение успешно отредактировано!');
            App::redirect('/topic/'.$tid.'?page='.$page);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $queryfiles = DB::run() -> query("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=?;", [$id, Post::class]);
    $files = $queryfiles->fetchAll();

    App::view('forum/topic_edit_post', compact('post', 'files', 'page'));

break;

############################################################################################
##                                        Голосование                                     ##
############################################################################################
case 'vote':
    if (! is_user()) App::abort(403, 'Авторизуйтесь для голосования!');

    $vote = Vote::where('topic_id', $tid)->first();
    if (! $vote) {
        App::abort(404, 'Голосование не найдено!');
    }

    $token = check(Request::input('token'));
    $poll  = abs(intval(Request::input('poll')));
    $page = abs(intval(Request::input('page')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    if ($vote['closed']) {
        $validation->addError('Данное голосование закрыто!');
    }

    $votePoll = VotePoll::where('vote_id', $vote['id'])
        ->where('user_id', App::getUserId())
        ->first();

    if ($votePoll) {
        $validation->addError('Вы уже проголосовали в этом опросе!');
    }

    $voteAnswer = VoteAnswer::where('id', $poll)
        ->where('vote_id', $vote['id'])
        ->first();

    if (! $voteAnswer) {
        $validation->addError('Вы не выбрали вариант ответа!');
    }

    if ($validation->run()) {

        $vote->count = Capsule::raw('count + 1');
        $vote->save();

        $voteAnswer->result = Capsule::raw('result + 1');
        $voteAnswer->save();

        $votePoll = new VotePoll();
        $votePoll->vote_id = $vote['id'];
        $votePoll->user_id = App::getUserId();
        $votePoll->created_at = SITETIME;
        $votePoll->save();

        App::setFlash('success', 'Ваш голос успешно принят!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid.'?page='.$page);
break;

############################################################################################
##                                     Переход к сообщению                                ##
############################################################################################
case 'viewpost':

    $id = param('id');

    $countTopics = Post::where('id', '<=', $id)
        ->where('topic_id', $tid)
        ->count();

    if (! $countTopics) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = ceil($countTopics / App::setting('forumpost'));
    App::redirect('/topic/'.$tid.'?page='.$end.'#post_'.$id);
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $topic = Topic::find($tid);

    if (empty($topic)) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = ceil($topic['posts'] / App::setting('forumpost'));
    App::redirect('/topic/'.$tid.'?page='.$end);
break;

endswitch;
