<?php

$start = abs(intval(Request::input('start', 0)));
$user = check(Request::input('user', $log));

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'themes':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `author`=?;", array($user));
    if (! $total) {
        App::abort('default', 'Созданных тем еще нет!');
    }

    if ($start >= $total) {
        $start = last_page($total, $config['forumtem']);
    }

    $querytopic = DB::run() -> query("SELECT `t`.*, f.`title` forum_title FROM `topics` t LEFT JOIN `forums` f ON `t`.`forums_id`=`f`.`id` WHERE t.`author`=? ORDER BY t.`last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($user));
    $topics = $querytopic->fetchAll();

    App::view('forum/active_themes', compact('topics', 'user', 'start', 'total'));
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'posts':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `user`=?;", array($user));

    if (! $total) {
        App::abort('default', 'Созданных сообщений еще нет!');
    }

    if ($start >= $total) {
        $start = last_page($total, $config['forumpost']);
    }

    $querypost = DB::run() -> query("SELECT `posts`.*, `title` FROM `posts` LEFT JOIN `topics` ON `posts`.`topics_id`=`topics`.`id` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$start.", ".$config['forumpost'].";", array($user));
    $posts = $querypost->fetchAll();

    App::view('forum/active_posts', compact('posts', 'user', 'start', 'total'));
break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'delete':

    if (! Request::ajax()) App::redirect('/');
    if (! is_admin()) App::abort(403, 'Удалять сообщения могут только модераторы!');

    $token = check(Request::input('token'));
    $tid = abs(intval(Request::input('tid')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `id`=? LIMIT 1;", array($tid));
    $validation->addRule('custom', $post, 'Ошибка! Данного сообщения не существует!');

    if ($validation->run()) {

        DB::run() -> query("DELETE FROM `posts` WHERE `id`=? AND `topics_id`=?;", array($tid, $post['topics_id']));
        DB::run() -> query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", array(1, $post['topics_id']));
        DB::run() -> query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", array(1, $post['forums_id']));

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

endswitch;

