<?php

$start = abs(intval(Request::input('start', 0)));
$tid  = isset($params['tid']) ? abs(intval($params['tid'])) : 0;

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_id`, `forums`.`forums_title`, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_id`=? LIMIT 1;", array($tid));

    if (empty($topics)) {
        App::abort('default', 'Данной темы не существует!');
    }

    if (!empty($topics['forums_parent'])) {
        $topics['subparent'] = DB::run() -> queryFetch("SELECT `forums_id`, `forums_title` FROM `forums` WHERE `forums_id`=? LIMIT 1;", array($topics['forums_parent']));
    }

    if (is_user()) {
        $topics['bookmark'] = DB::run() -> queryFetch("SELECT * FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($tid, $log));

        if (!empty($topics['bookmark']) && $topics['topics_posts'] > $topics['bookmark']['book_posts']) {
            DB::run() -> query("UPDATE `bookmarks` SET `book_posts`=? WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($topics['topics_posts'], $tid, $log));
        }
    }

    // --------------------------------------------------------------//
    if (!empty($topics['topics_mod'])) {
        $topics['curator'] = explode(',', $topics['topics_mod']);
        $topics['is_moder'] = in_array($log, $topics['curator'], true) ? 1 : 0;
    }

    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));

    if ($total > 0 && $start >= $total) {
        $start = last_page($total, $config['forumpost']);
    }

    $page = floor(1 + $start / $config['forumpost']);

    $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT ".$start.", ".$config['forumpost'].";", array($tid));

    $topics['posts'] = $querypost->fetchAll();

    // ----- Получение массива файлов ----- //
    $ipdpost = array();
    foreach ($topics['posts'] as $val) {
        $ipdpost[] = $val['posts_id'];
    }

    $ipdpost = implode(',', $ipdpost);

    if (!empty($ipdpost)) {
        $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id` IN (".$ipdpost.");");
        $files = $queryfiles->fetchAll();
    }
    if (!empty($files)){
        $forumfiles = array();
        foreach ($files as $file){
            $topics['posts_files'][$file['file_posts_id']][] = $file;
        }
    }
    // ------------------------------------- //
    App::view('forum/topic', compact('topics', 'tid', 'start', 'total', 'page'));

break;

############################################################################################
##                                   Добавление сообщения                                 ##
############################################################################################
case 'create':

    $msg   = check(Request::input('msg'));
    $token = check(Request::input('token'));

    if (! is_user()) App::abort(403, 'Авторизуйтесь для добавления сообщения!');

    $topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics`.`topics_id`=? LIMIT 1;", array($tid));

    $validation = new Validation();
    $validation -> addRule('equal', array($token, $_SESSION['token']), ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
        -> addRule('not_empty', $topics, ['msg' => 'Выбранная вами тема не существует, возможно она была удалена!'])
        -> addRule('empty', $topics['topics_closed'], ['msg' => 'Запрещено писать в закрытую тему!'])
        -> addRule('equal', [is_flood($log), true], ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' сек!'])
        -> addRule('string', $msg, ['msg' => 'Слишком длинное или короткое сообщение!'], true, 5, $config['forumtextlength']);

        // Проверка сообщения на схожесть
        $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_id` DESC LIMIT 1;", array($tid));
        $validation -> addRule('not_equal', [$msg, $post['posts_text']], 'Ваше сообщение повторяет предыдущий пост!');

    if ($validation->run()) {

        $msg = antimat($msg);

        if ($log == $post['posts_user'] && $post['posts_time'] + 600 > SITETIME && (utf_strlen($msg) + utf_strlen($post['posts_text']) <= $config['forumtextlength'])) {

            $newpost = $post['posts_text']."\n\n".'[i][size=1]Добавлено через '.maketime(SITETIME - $post['posts_time']).' сек.[/size][/i]'."\n".$msg;

            DB::run() -> query("UPDATE `posts` SET `posts_text`=? WHERE `posts_id`=? LIMIT 1;", array($newpost, $post['posts_id']));
            $lastid = $post['posts_id'];

        } else {

            DB::run() -> query("INSERT INTO `posts` (`posts_topics_id`, `posts_forums_id`, `posts_user`, `posts_text`, `posts_time`, `posts_ip`, `posts_brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($tid, $topics['topics_forums_id'], $log, $msg, SITETIME, App::getClientIp(), App::getUserAgent()));
            $lastid = DB::run() -> lastInsertId();

            DB::run() -> query("UPDATE `users` SET `users_allforum`=`users_allforum`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=? LIMIT 1;", array($log));

            DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`+1, `topics_last_user`=?, `topics_last_time`=? WHERE `topics_id`=?;", array($log, SITETIME, $tid));

            DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`+1, `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($tid, $topics['topics_title'], $log, SITETIME, $topics['topics_forums_id']));
            // Обновление родительского форума
            if ($topics['forums_parent'] > 0) {
                DB::run() -> query("UPDATE `forums` SET `forums_last_id`=?, `forums_last_themes`=?, `forums_last_user`=?, `forums_last_time`=? WHERE `forums_id`=?;", array($tid, $topics['topics_title'], $log, SITETIME, $topics['forums_parent']));
            }
        }

        // -- Загрузка файла -- //
        if (!empty($_FILES['file']['name']) && !empty($lastid)) {
            if ($udata['users_point'] >= $config['forumloadpoints']){
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

                            if (!file_exists(HOME.'/upload/forum/'.$topics['topics_id'])){
                                $old = umask(0);
                                mkdir(HOME.'/upload/forum/'.$topics['topics_id'], 0777, true);
                                umask($old);
                            }

                            $num = 0;
                            $hash = $lastid.'.'.$ext;
                            while(file_exists(HOME.'/upload/forum/'.$topics['topics_id'].'/'.$hash)){
                                $num++;
                                $hash = $lastid.'_'.$num.'.'.$ext;
                            }

                            move_uploaded_file($_FILES['file']['tmp_name'], HOME.'/upload/forum/'.$topics['topics_id'].'/'.$hash);

                            DB::run() -> query("INSERT INTO `files_forum` (`file_topics_id`, `file_posts_id`, `file_hash`, `file_name`, `file_size`, `file_user`, `file_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($topics['topics_id'], $lastid, $hash, $filename, $filesize, $log, SITETIME));

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
        // -- Загрузка файла -- //

        App::setFlash('success', 'Сообщение успешно добавлено!');

    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid);
break;

############################################################################################
##                                      Жалоба                                            ##
############################################################################################
case 'complaint':

    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $id    = abs(intval(Request::input('id')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = DBM::run()->selectFirst('posts', array('posts_id' => $id));
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');

    $spam = DBM::run()->selectFirst('spam', array('spam_key' => 1, 'spam_idnum' => $id));
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = DBM::run()->insert('spam', array(
            'spam_key'     => 1,
            'spam_idnum'   => $data['posts_id'],
            'spam_user'    => $log,
            'spam_login'   => $data['posts_user'],
            'spam_text'    => $data['posts_text'],
            'spam_time'    => $data['posts_time'],
            'spam_addtime' => SITETIME,
            'spam_link'    => '/topic/'.$data['posts_topics_id'].'?start='.$start,
        ));

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

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

    $isModer = in_array($log, explode(',', $topic['topics_mod'], true)) ? true : false;

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('bool', is_user(), 'Для закрытия тем необходимо авторизоваться')
        -> addRule('not_empty', $del, 'Отстутствуют выбранные сообщения для удаления!')
        -> addRule('empty', $topic['topics_closed'], 'Редактирование невозможно. Данная тема закрыта!')
        -> addRule('equal', [$isModer, true], 'Удалять сообщения могут только кураторы темы!');

    if ($validation->run()) {

        $del = implode(',', $del);

        // ------ Удаление загруженных файлов -------//
        $queryfiles = DB::run() -> query("SELECT `file_hash` FROM `files_forum` WHERE `file_posts_id` IN (".$del.");");
        $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($files)){
            foreach ($files as $file){
                if (file_exists(HOME.'/upload/forum/'.$topic['topics_id'].'/'.$file)){
                    unlink(HOME.'/upload/forum/'.$topic['topics_id'].'/'.$file);
                }
            }
            DB::run() -> query("DELETE FROM `files_forum` WHERE `file_posts_id` IN (".$del.");");
        }

        $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `posts_id` IN (".$del.") AND `posts_topics_id`=".$tid.";");
        DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`-? WHERE `topics_id`=?;", array($delposts, $tid));
        DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`-? WHERE `forums_id`=?;", array($delposts, $topic['topics_forums_id']));

        App::setFlash('success', 'Выбранные сообщения успешно удалены!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/topic/'.$tid.'?start='.$start);
break;

############################################################################################
##                                       Закрытие темы                                    ##
############################################################################################
case 'close':

    $token = check(Request::input('token'));

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));

    $validation = new Validation();
    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
         -> addRule('bool', is_user(), 'Для закрытия тем необходимо авторизоваться')
         -> addRule('max', [App::user('users_point'), App::setting('editforumpoint')], 'Для закрытия тем вам необходимо набрать '.points(App::setting('editforumpoint')).'!')
        -> addRule('not_empty', $topic, 'Выбранная вами тема не существует, возможно она была удалена!')
        -> addRule('equal', [$topic['topics_author'], $log], 'Вы не автор данной темы!')
        -> addRule('empty', $topic['topics_closed'], 'Данная тема уже закрыта!');

    if ($validation->run()) {

        DB::run() -> query("UPDATE `topics` SET `topics_closed`=? WHERE `topics_id`=?;", array(1, $tid));

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

    if ($udata['users_point'] < $config['editforumpoint']) {
        App::abort('default', 'У вас недостаточно актива для изменения темы!');
    }

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
    if (empty($topic)) {
        App::abort('default', 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    if ($topic['topics_author'] !== $log) {
        App::abort('default', 'Изменение невозможно, вы не автор данной темы!');
    }

    if ($topic['topics_closed']) {
        App::abort('default', ' Изменение невозможно, данная тема закрыта!');
    }

    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY posts_id ASC LIMIT 1;", array($tid));

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

            DB::run() -> query("UPDATE `topics` SET `topics_title`=? WHERE topics_id=?;", array($title, $tid));

            if ($post) {
                DB::run()->query("UPDATE `posts` SET `posts_user`=?, `posts_text`=?, `posts_ip`=?, `posts_brow`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($log, $msg, App::getClientIp(), App::getUserAgent(), $log, SITETIME, $post['posts_id']));
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

    $id  = isset($params['id']) ? abs(intval($params['id'])) : 0;

    if (! is_user()) App::abort(403, 'Авторизуйтесь для изменения сообщения!');

    $post = DB::run() -> queryFetch("SELECT `posts`.*, `topics`.* FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_id`=? LIMIT 1;", array($id));

    $isModer = in_array($log, explode(',', $post['topics_mod'], true)) ? true : false;

    if (empty($post)) {
        App::abort('default', 'Данного сообщения не существует!');
    }

    if ($post['topics_closed']) {
        App::abort('default', 'Редактирование невозможно, данная тема закрыта!');
    }

    if (! $isModer && $post['posts_user'] != $log) {
        App::abort('default', 'Редактировать сообщения может только автор или кураторы темы!');
    }

    if (! $isModer && $post['posts_time'] + 600 < SITETIME) {
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

            DB::run() -> query("UPDATE `posts` SET `posts_text`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($msg, $log, SITETIME, $id));

            // ------ Удаление загруженных файлов -------//
            if ($delfile) {
                $del = implode(',', $delfile);

                $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id`=? AND `file_id` IN (".$del.");", array($id));
                $files = $queryfiles->fetchAll();

                if (!empty($files)){
                    foreach ($files as $file){
                        if (file_exists(HOME.'/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash'])){
                            unlink(HOME.'/upload/forum/'.$file['file_topics_id'].'/'.$file['file_hash']);
                        }
                    }
                    DB::run() -> query("DELETE FROM `files_forum` WHERE `file_posts_id`=? AND `file_id` IN (".$del.");", array($id));
                }
            }

            App::setFlash('success', 'Сообщение успешно отредактировано!');
            App::redirect('/topic/'.$tid.'?start='.$start);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `file_posts_id`=?;", array($id));
    $files = $queryfiles->fetchAll();

    App::view('forum/topic_edit_post', compact('post', 'files', 'start'));

break;


############################################################################################
##                                     Переход к сообщению                                ##
############################################################################################
case 'viewpost':

    $id  = isset($params['id']) ? abs(intval($params['id'])) : 0;

    $querytopic = DB::run() -> querySingle("SELECT COUNT(*) FROM `posts` WHERE `posts_id`<=? AND `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT 1;", array($id, $tid));

    if (empty($querytopic)) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = floor(($querytopic - 1) / $config['forumpost']) * $config['forumpost'];
    App::redirect('/topic/'.$tid.'?start='.$end.'#post_'.$id);
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

    $topic = DBM::run()->selectFirst('topics', ['topics_id' => $tid]);

    if (empty($topic)) {
        App::abort(404, 'Выбранная вами тема не существует, возможно она была удалена!');
    }

    $end = floor(($topic['topics_posts'] - 1) / $config['forumpost']) * $config['forumpost'];
    App::redirect('/topic/'.$tid.'?start='.$end);
break;

endswitch;
