<?php

$start = abs(intval(Request::input('start', 0)));
$user = check(Request::input('user', $log));

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'themes':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `topics_author`=?;", array($user));
    if (! $total) {
        App::abort('default', 'Созданных тем еще нет!');
    }

    if ($start >= $total) {
        $start = last_page($total, $config['forumtem']);
    }

    $querytopic = DB::run() -> query("SELECT `topics`.*, `forums_title` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_author`=? ORDER BY `topics_last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($user));
    $topics = $querytopic->fetchAll();

    App::view('forum/active_themes', compact('topics', 'user', 'start', 'total'));
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'posts':
    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_user`=?;", array($user));

    if (! $total) {
        App::abort('default', 'Созданных сообщений еще нет!');
    }

    if ($start >= $total) {
        $start = last_page($total, $config['forumpost']);
    }

    $querypost = DB::run() -> query("SELECT `posts`.*, `topics_title` FROM `posts` LEFT JOIN `topics` ON `posts`.`posts_topics_id`=`topics`.`topics_id` WHERE `posts_user`=? ORDER BY `posts_time` DESC LIMIT ".$start.", ".$config['forumpost'].";", array($user));
    $posts = $querypost->fetchAll();

    App::view('forum/active_posts', compact('posts', 'user', 'start', 'total'));
break;

############################################################################################
##                                    Удаление сообщений                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    $id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

    if (is_admin()) {
        if ($uid == $_SESSION['token']) {
            $topics = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($id));

            if (!empty($topics)) {
                DB::run() -> query("DELETE FROM `posts` WHERE `posts_id`=? AND `posts_topics_id`=?;", array($id, $topics['posts_topics_id']));
                DB::run() -> query("UPDATE `topics` SET `topics_posts`=`topics_posts`-? WHERE `topics_id`=?;", array(1, $topics['posts_topics_id']));
                DB::run() -> query("UPDATE `forums` SET `forums_posts`=`forums_posts`-? WHERE `forums_id`=?;", array(1, $topics['posts_forums_id']));

                notice('Сообщение успешно удалено!');
                redirect("active.php?act=posts&uz=$uz&start=$start");

            } else {
                show_error('Ошибка! Данного сообщения не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять сообщения могут только модераторы!');
    }

    render('includes/back', array('link' => 'active.php?act=posts&amp;uz='.$uz.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

endswitch;

