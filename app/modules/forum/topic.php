<?php

$tid = param('tid');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $topic = DB::run() -> queryFetch("SELECT `t`.*, `f`.`title` forum_title, `f`.`parent` FROM `topics` t LEFT JOIN `forums` f ON t.`forum_id`=f.`id` WHERE t.`id`=? LIMIT 1;", [$tid]);

    if (empty($topic)) {
        App::abort('default', 'Данной темы не существует!');
    }

    if (!empty($topic['parent'])) {
        $topic['subparent'] = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", [$topic['parent']]);
    }

    if (is_user()) {
        $topic['bookmark'] = DB::run() -> queryFetch("SELECT * FROM `bookmarks` WHERE `topic_id`=? AND `user`=? LIMIT 1;", [$tid, $log]);

        if (!empty($topic['bookmark']) && $topic['posts'] > $topic['bookmark']['posts']) {
            DB::run() -> query("UPDATE `bookmarks` SET `posts`=? WHERE `topic_id`=? AND `user`=? LIMIT 1;", [$topic['posts'], $tid, $log]);
        }
    }

    // --------------------------------------------------------------//
    if (!empty($topic['moderators'])) {
        $topic['curator'] = explode(',', $topic['moderators']);
        $topic['is_moder'] = in_array($log, $topic['curator'], true) ? 1 : 0;
    }

    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `topic_id`=?;", [$tid]);
    $page = App::paginate(App::setting('forumpost'), $total);

    $querypost = DB::run() -> query("SELECT p.*, pl.vote FROM `posts` p LEFT JOIN pollings pl ON p.id = pl.relate_id AND relate_type = 'post' AND pl.user=? WHERE `topic_id`=? ORDER BY p.`time` ASC LIMIT ".$page['offset'].", ".App::setting('forumpost').";", [$log, $tid]);


   // var_dump($querypost->fetchAll());
    $topic['posts'] = $querypost->fetchAll();


    // ----- Получение массива файлов ----- //
    $ipdpost = [];
    foreach ($topic['posts'] as $val) {
        $ipdpost[] = $val['id'];
    }

    $ipdpost = implode(',', $ipdpost);

    if (!empty($ipdpost)) {
        $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `post_id` IN (".$ipdpost.");");
        $files = $queryfiles->fetchAll();

        if (!empty($files)){
            $forumfiles = [];
            foreach ($files as $file){
                $topic['files'][$file['post_id']][] = $file;
            }
        }
    }

    // Голосование
    $vote = DBM::run()->selectFirst('vote', ['topic_id' => $tid]);
    if ($vote) {

        $vote['poll'] = DBM::run()->selectFirst('votepoll', ['vote_id' => $vote['id'], 'user' => App::getUsername()]);
        $vote['answers'] = DBM::run()->select('voteanswer', ['vote_id' => $vote['id']], null, null, ['id' => 'ASC']);
        if ($vote['answers']) {

            $vote['voted'] = array_pluck($vote['answers'], 'result', 'answer');
            arsort($vote['voted']);

            $max = max($vote['voted']);
            $vote['sum'] = ($vote['count'] > 0) ? $vote['count'] : 1;
            $vote['max'] = ($max > 0) ? $max : 1;
        }
    }

    App::view('forum/topic', compact('topic', 'page', 'vote'));
break;

############################################################################################
##                                   Добавление сообщения                                 ##
############################################################################################
case 'create':

    $msg = check(Request::input('msg'));
    $token = check(Request::input('token'));

    if (! is_user()) App::abort(403, 'Авторизуйтесь для добавления сообщения!');

    $topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`forum_id`=`forums`.`id` WHERE `topics`.`id`=? LIMIT 1;", [$tid]);

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

        if ($log == $post['user'] && $post['time'] + 600 > SITETIME && (utf_strlen($msg) + utf_strlen($post['text']) <= $config['forumtextlength'])) {

            $newpost = $post['text']."\n\n".'[i][size=1]Добавлено через '.maketime(SITETIME - $post['time']).' сек.[/size][/i]'."\n".$msg;

            DB::run() -> query("UPDATE `posts` SET `text`=? WHERE `id`=? LIMIT 1;", [$newpost, $post['id']]);
            $lastid = $post['id'];

        } else {

            DB::run() -> query("INSERT INTO `posts` (`topic_id`, `forum_id`, `user`, `text`, `time`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$tid, $topics['forum_id'], $log, $msg, SITETIME, App::getClientIp(), App::getUserAgent()]);
            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("UPDATE `users` SET `allforum`=`allforum`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=? LIMIT 1;", [$log]);

            DB::run() -> query("UPDATE `topics` SET `posts`=`posts`+1, `last_user`=?, `last_time`=? WHERE `id`=?;", [$log, SITETIME, $tid]);

            DB::run() -> query("UPDATE `forums` SET `posts`=`posts`+1, `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", [$tid, $topics['title'], $log, SITETIME, $topics['forum_id']]);
            // Обновление родительского форума
            if ($topics['parent'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", [$tid, $topics['title'], $log, SITETIME, $topics['parent']]);
            }
        }

        // Рассылка уведомлений в приват
        $parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $msg);

        preg_match_all('|\[b\](.*?)\[\/b\]|s', $parseText, $matches);

        if (isset($matches[1])) {
            $usersAnswer = array_unique($matches[1]);

            $newTopic = DBM::run()->selectFirst('topics', ['id' => $tid]);
            foreach($usersAnswer as $user) {

                if ($user == $log) {
                    continue;
                }

                $user = DBM::run()->queryFirst('SELECT * FROM users WHERE login=:login OR nickname=:login LIMIT 1;', ['login' => $user]);

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

                            DB::run() -> query("INSERT INTO `files_forum` (`topic_id`, `post_id`, `hash`, `name`, `size`, `user`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$topics['id'], $lastid, $hash, $filename, $filesize, $log, SITETIME]);

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

    $data = DBM::run()->selectFirst('posts', ['id' => $id]);
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');

    $spam = DBM::run()->selectFirst('spam', ['relate' => 1, 'idnum' => $id]);
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = DBM::run()->insert('spam', [
            'relate'     => 1,
            'idnum'   => $data['id'],
            'user'    => $log,
            'login'   => $data['user'],
            'text'    => $data['text'],
            'time'    => $data['time'],
            'addtime' => SITETIME,
            'link'    => '/topic/'.$data['topic_id'].'?page='.$page,
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
        $queryfiles = DB::run() -> query("SELECT `hash` FROM `files_forum` WHERE `post_id` IN (".$del.");");
        $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($files)){
            foreach ($files as $file){
                if (file_exists(HOME.'/uploads/forum/'.$topic['id'].'/'.$file)){
                    unlink(HOME.'/uploads/forum/'.$topic['id'].'/'.$file);
                }
            }
            DB::run() -> query("DELETE FROM `files_forum` WHERE `post_id` IN (".$del.");");
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

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
         -> addRule('bool', is_user(), 'Для закрытия тем необходимо авторизоваться')
         -> addRule('max', [App::user('point'), App::setting('editforumpoint')], 'Для закрытия тем вам необходимо набрать '.points(App::setting('editforumpoint')).'!')
        -> addRule('not_empty', $topic, 'Выбранная вами тема не существует, возможно она была удалена!')
        -> addRule('equal', [$topic['author'], $log], 'Вы не автор данной темы!')
        -> addRule('empty', $topic['closed'], 'Данная тема уже закрыта!');

    if ($validation->run()) {

        DB::run() -> query("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [1, $tid]);

        $vote = DBM::run()->selectFirst('vote', ['topic_id' => $tid]);

        if ($vote) {
            DBM::run()->update('vote', [
                'closed' => 1,
            ], [
                'id' => $vote['id']
            ]);

            DBM::run()->delete('votepoll', ['vote_id' => $vote['id']]);
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

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);
    if (empty($topic)) {
        App::abort('default', 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    if ($topic['author'] !== $log) {
        App::abort('default', 'Изменение невозможно, вы не автор данной темы!');
    }

    if ($topic['closed']) {
        App::abort('default', ' Изменение невозможно, данная тема закрыта!');
    }

    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `topic_id`=? ORDER BY id ASC LIMIT 1;", [$tid]);

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
                DB::run()->query("UPDATE `posts` SET `user`=?, `text`=?, `ip`=?, `brow`=?, `edit`=?, `edit_time`=? WHERE `id`=?;", [$log, $msg, App::getClientIp(), App::getUserAgent(), $log, SITETIME, $post['id']]);
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

    if (! $isModer && $post['user'] != $log) {
        App::abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
    }

    if (! $isModer && $post['time'] + 600 < SITETIME) {
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

            DB::run() -> query("UPDATE `posts` SET `text`=?, `edit`=?, `edit_time`=? WHERE `id`=?;", [$msg, $log, SITETIME, $id]);

            // ------ Удаление загруженных файлов -------//
            if ($delfile) {
                $del = implode(',', $delfile);
                $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `post_id`=? AND `id` IN (".$del.");", [$id]);
                $files = $queryfiles->fetchAll();

                if (!empty($files)){
                    foreach ($files as $file){
                        if (file_exists(HOME.'/uploads/forum/'.$file['topic_id'].'/'.$file['hash'])){
                            unlink(HOME.'/uploads/forum/'.$file['topic_id'].'/'.$file['hash']);
                        }
                    }
                    DB::run() -> query("DELETE FROM `files_forum` WHERE `post_id`=? AND `id` IN (".$del.");", [$id]);
                }
            }

            App::setFlash('success', 'Сообщение успешно отредактировано!');
            App::redirect('/topic/'.$tid.'?page='.$page);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `post_id`=?;", [$id]);
    $files = $queryfiles->fetchAll();

    App::view('forum/topic_edit_post', compact('post', 'files', 'page'));

break;

############################################################################################
##                                        Голосование                                     ##
############################################################################################
case 'vote':
    if (! is_user()) App::abort(403, 'Авторизуйтесь для голосования!');

    $vote = DBM::run()->selectFirst('vote', ['topic_id' => $tid]);
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

    $votePoll = DBM::run()->selectFirst('votepoll', ['vote_id' => $vote['id'], 'user' => App::getUsername()]);
    if ($votePoll) {
        $validation->addError('Вы уже проголосовали в этом опросе!');
    }

    $voteAnswer = DBM::run()->selectFirst('voteanswer', ['id' => $poll, 'vote_id' => $vote['id']]);
    if (! $voteAnswer) {
        $validation->addError('Вы не выбрали вариант ответа!');
    }

    if ($validation->run()) {

        DBM::run()->update('vote', [
            'count' => ['+', 1],
        ], [
            'id' => $vote['id']
        ]);

        DBM::run()->update('voteanswer', [
            'result' => ['+', 1],
        ], [
            'id' => $voteAnswer['id']
        ]);

        DBM::run()->insert('votepoll', [
            'vote_id' => $vote['id'],
            'user' => App::getUsername(),
            'time' => SITETIME,
        ]);

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

    $querytopic = DB::run() -> querySingle("SELECT COUNT(*) FROM `posts` WHERE `id`<=? AND `topic_id`=? ORDER BY `time` ASC LIMIT 1;", [$id, $tid]);

    if (empty($querytopic)) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = ceil($querytopic / App::setting('forumpost'));
    App::redirect('/topic/'.$tid.'?page='.$end.'#post_'.$id);
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $topic = DBM::run()->selectFirst('topics', ['id' => $tid]);

    if (empty($topic)) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = ceil($topic['posts'] / App::setting('forumpost'));
    App::redirect('/topic/'.$tid.'?page='.$end);
break;

endswitch;
