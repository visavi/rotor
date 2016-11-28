<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'blogs';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'blogs':
    show_title('Список всех статей '.$uz);

    $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `user`=?;", [$uz]);

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $queryblogs = DB::run() -> query("SELECT * FROM `blogs` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";", [$uz]);
        $blogs = $queryblogs -> fetchAll();

        render('blog/active_blogs', ['blogs' => $blogs]);

        App::pagination($page);

        echo 'Всего статей: <b>'.$total.'</b><br /><br />';
    } else {
        show_error('Статей еще нет!');
    }
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'comments':
    show_title('Список всех комментариев '.$uz);

    $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `user`=?;", ['blog', $uz]);

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $is_admin = is_admin();

        $querycomments = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `blogs` ON `comments`.`relate_id`=`blogs`.`id` WHERE relate_type='blog' AND comments.`user`=? ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";", [$uz]);
        $comments = $querycomments -> fetchAll();

        render('blog/active_comments', ['comments' => $comments, 'start' => $start]);

        App::pagination($page);
    } else {
        show_error('Комментарии не найдены!');
    }
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    $id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

    if (is_admin()) {
        if ($uid == $_SESSION['token']) {
            $blogs = DB::run() -> querySingle("SELECT `blog` FROM `comments` WHERE relate_type=? AND `id`=?;", ['blog', $id]);
            if (!empty($blogs)) {
                DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `id`=? AND relate_id=?;", ['blog', $id, $blogs]);
                DB::run() -> query("UPDATE `blogs` SET `comments`=`comments`-? WHERE `id`=?;", [1, $blogs]);

                notice('Комментарий успешно удален!');
                redirect("/blog/active?act=comments&uz=$uz&start=$start");

            } else {
                show_error('Ошибка! Данного комментария не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    render('includes/back', ['link' => '/blog/active?act=comments&amp;uz='.$uz.'&amp;start='.$start, 'title' => 'Вернуться']);
break;

endswitch;

render('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif']);

App::view($config['themes'].'/foot');
