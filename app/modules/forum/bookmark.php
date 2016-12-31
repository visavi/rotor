<?php

$tid  = isset($params['tid']) ? abs(intval($params['tid'])) : 0;

if (! is_user()) {
    App::abort('default', 'Для управления закладками, необходимо авторизоваться!');
}

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = DB::run() -> querySingle("SELECT count(*) FROM `bookmarks` WHERE `user`=?;", [$log]);
    $page = App::paginate(App::setting('forumtem'), $total);

    $querytopic = DB::run() -> query("SELECT `bookmarks`.posts book_posts, `topics`.* FROM `bookmarks` LEFT JOIN `topics` ON `bookmarks`.`topic_id`=`topics`.`id` WHERE `user`=?  ORDER BY `last_time` DESC LIMIT ".$page['offset'].", ".$config['forumtem'].";", [$log]);
    $topics = $querytopic->fetchAll();

    App::view('forum/bookmark', compact('topics', 'page'));
break;

############################################################################################
##                         Добавление / удаление закладок                                 ##
############################################################################################
case 'perform':

    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $tid = abs(intval(Request::input('tid')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);
    $validation->addRule('custom', $topic, 'Ошибка! Данной темы не существует!');

    if ($validation->run()) {

        $bookmark = DB::run()->querySingle("SELECT `id` FROM `bookmarks` WHERE `topic_id`=? AND `user`=? LIMIT 1;", [$tid, $log]);

        if ($bookmark) {
            DB::run() -> query("DELETE FROM `bookmarks` WHERE `topic_id`=? AND `user`=?;", [$tid, $log]);
            exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
        } else {
            DB::run()->query("INSERT INTO `bookmarks` (`user`, `topic_id`, `forum_id`, `posts`) VALUES (?, ?, ?, ?);", [$log, $tid, $topic['forum_id'], $topic['posts']]);
            exit(json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']));
        }

    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

############################################################################################
##                                 Удаление закладок                                      ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $topicIds = intar(Request::input('del'));
    $page = abs(intval(Request::input('page')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('not_empty', $topicIds, 'Ошибка! Отсутствуют выбранные закладки!');

    if ($validation->run()) {
        $topicIds = implode(',', $topicIds);

        DB::run()->query("DELETE FROM `bookmarks` WHERE `id` IN (".$topicIds.") AND `user`=?;", [$log]);

        App::setFlash('success', 'Выбранные темы успешно удалены из закладок!');
    } else {
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect('/forum/bookmark?page='.$page);

    render('includes/back', ['link' => '/bookmark?page='.$page, 'title' => 'Вернуться']);
break;

endswitch;
