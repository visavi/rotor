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

    $total = DB::run() -> querySingle("SELECT count(*) FROM `bookmarks` WHERE `book_user`=?;", array($log));

    if ($total > 0 && $start >= $total) {
        $start = last_page($total, $config['forumtem']);
    }

    $querytopic = DB::run() -> query("SELECT `topics`.*, `bookmarks`.* FROM `bookmarks` LEFT JOIN `topics` ON `bookmarks`.`book_topic`=`topics`.`topics_id` WHERE `book_user`=?  ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($log));
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

    $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($tid));
    $validation->addRule('custom', $topic, 'Ошибка! Данной темы не существует!');
    $validation->addRule('empty', $topic['topics_closed'], 'Ошибка! Нельзя добавлять в закладки закрытую тему!');

    if ($validation->run()) {

        $bookmark = DB::run()->querySingle("SELECT `book_id` FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=? LIMIT 1;", array($tid, $log));

        if ($bookmark) {
            DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_topic`=? AND `book_user`=?;", array($tid, $log));
            exit(json_encode(['status' => 'deleted', 'message' => 'Тема успешно удалена из закладок!']));
        } else {
            DB::run()->query("INSERT INTO `bookmarks` (`book_user`, `book_topic`, `book_forum`, `book_posts`) VALUES (?, ?, ?, ?);", array($log, $tid, $topic['topics_forums_id'], $topic['topics_posts']));
            exit(json_encode(['status' => 'added', 'message' => 'Тема успешно добавлена в закладки!']));
        }

    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

############################################################################################
##                                 Удаление закладок                                      ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (!empty($del)) {
            $del = implode(',', $del);

            DB::run() -> query("DELETE FROM `bookmarks` WHERE `book_id` IN (".$del.") AND `book_user`=?;", array($log));

            notice('Выбранные темы успешно удалены из закладок!');
            redirect("bookmark.php?start=$start");

        } else {
            show_error('Ошибка! Отсутствуют выбранные закладки!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    render('includes/back', array('link' => 'bookmark.php?start='.$start, 'title' => 'Вернуться'));
break;

endswitch;
