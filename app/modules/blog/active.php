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

    $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `blogs_user`=?;", array($uz));

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $queryblogs = DB::run() -> query("SELECT * FROM `blogs` WHERE `blogs_user`=? ORDER BY `blogs_time` DESC LIMIT ".$start.", ".$config['blogpost'].";", array($uz));
        $blogs = $queryblogs -> fetchAll();

        render('blog/active_blogs', array('blogs' => $blogs));

        page_strnavigation('/blog/active?act=blogs&amp;uz='.$uz.'&amp;', $config['blogpost'], $start, $total);

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

    $total = DB::run() -> querySingle("SELECT count(*) FROM `commblog` WHERE `commblog_author`=?;", array($uz));

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $is_admin = is_admin();

        $querycomments = DB::run() -> query("SELECT `commblog`.*, `blogs_title`, `blogs_comments` FROM `commblog` LEFT JOIN `blogs` ON `commblog`.`commblog_blog`=`blogs`.`blogs_id` WHERE `commblog_author`=? ORDER BY `commblog_time` DESC LIMIT ".$start.", ".$config['blogpost'].";", array($uz));
        $comments = $querycomments -> fetchAll();

        render('blog/active_comments', array('comments' => $comments, 'start' => $start));

        page_strnavigation('/blog/active?act=comments&amp;uz='.$uz.'&amp;', $config['blogpost'], $start, $total);
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
            $blogs = DB::run() -> querySingle("SELECT `commblog_blog` FROM `commblog` WHERE `commblog_id`=?;", array($id));
            if (!empty($blogs)) {
                DB::run() -> query("DELETE FROM `commblog` WHERE `commblog_id`=? AND commblog_blog=?;", array($id, $blogs));
                DB::run() -> query("UPDATE `blogs` SET `blogs_comments`=`blogs_comments`-? WHERE `blogs_id`=?;", array(1, $blogs));

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

    render('includes/back', array('link' => '/blog/active?act=comments&amp;uz='.$uz.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

endswitch;

render('includes/back', array('link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif'));

App::view($config['themes'].'/foot');
