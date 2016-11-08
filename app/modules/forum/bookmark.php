<?php

$start = abs(intval(Request::input('start', 0)));
$tid  = isset($params['tid']) ? abs(intval($params['tid'])) : 0;

if (! is_user()) {
    App::abort('Для управления закладками, необходимо авторизоваться!');
}

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = DB::run() -> querySingle("SELECT count(*) FROM `bookmarks` WHERE `user`=?;", [$log]);

    if ($total > 0 && $start >= $total) {
        $start = last_page($total, $config['forumtem']);
    }

    $querytopic = DB::run() -> query("SELECT `topics`.*, `bookmarks`.* FROM `bookmarks` LEFT JOIN `topics` ON `bookmarks`.`topic`=`topics`.`id` WHERE `user`=?  ORDER BY `last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", [$log]);
    $topics = $querytopic->fetchAll();

    App::view('forum/bookmark', compact('topics', 'start', 'total'));

    page_strnavigation('/forum/bookmark?', $config['forumtem'], $start, $total);
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

        $bookmark = DB::run()->querySingle("SELECT `id` FROM `bookmarks` WHERE `topic`=? AND `user`=? LIMIT 1;", [$tid, $log]);

        if ($bookmark) {
            DB::run() -> query("DELETE FROM `bookmarks` WHERE `topic`=? AND `user`=?;", [$tid, $log]);
            exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
        } else {
            DB::run()->query("INSERT INTO `bookmarks` (`user`, `topic`, `forum`, `posts`) VALUES (?, ?, ?, ?);", [$log, $tid, $topic['forum_id'], $topic['posts']]);
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

    App::redirect('/forum/bookmark?start='.$start);

    render('includes/back', ['link' => '/bookmark?start='.$start, 'title' => 'Вернуться']);
break;

endswitch;
