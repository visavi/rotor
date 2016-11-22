<?php

$user = check(Request::input('user', $log));

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'themes':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `author`=?;", [$user]);
    if (! $total) {
        App::abort('default', 'Созданных тем еще нет!');
    }

    $page = App::paginate(App::setting('forumtem'), $total);

    $querytopic = DB::run() -> query("SELECT `t`.*, f.`title` forum_title FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` WHERE t.`author`=? ORDER BY t.`last_time` DESC LIMIT ".$page['offset'].", ".$config['forumtem'].";", [$user]);
    $topics = $querytopic->fetchAll();

    App::view('forum/active_themes', compact('topics', 'user', 'page'));
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'posts':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `user`=?;", [$user]);

    if (! $total) {
        App::abort('default', 'Созданных сообщений еще нет!');
    }

    $page = App::paginate(App::setting('forumpost'), $total);

    $querypost = DB::run() -> query("SELECT `posts`.*, `title` FROM `posts` LEFT JOIN `topics` ON `posts`.`topic_id`=`topics`.`id` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['forumpost'].";", [$user]);
    $posts = $querypost->fetchAll();

    App::view('forum/active_posts', compact('posts', 'user', 'page'));
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

    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `id`=? LIMIT 1;", [$tid]);
    $validation->addRule('custom', $post, 'Ошибка! Данного сообщения не существует!');

    if ($validation->run()) {

        DB::run() -> query("DELETE FROM `posts` WHERE `id`=? AND `topic_id`=?;", [$tid, $post['topic_id']]);
        DB::run() -> query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post['topic_id']]);
        DB::run() -> query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [1, $post['forum_id']]);

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
break;

endswitch;

