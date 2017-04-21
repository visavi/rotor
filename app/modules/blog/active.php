<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'blogs';
$uz = (empty($_GET['uz'])) ? check(App::getUsername()) : check($_GET['uz']);
$page = abs(intval(Request::input('page', 1)));

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'blogs':
    //show_title('Список всех статей '.$uz);

    $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `user`=?;", [$uz]);
    $page = App::paginate(App::setting('blogpost'), $total);

    if ($total > 0) {

        $queryblogs = DB::run() -> query("SELECT * FROM `blogs` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('blogpost').";", [$uz]);
        $blogs = $queryblogs -> fetchAll();

        App::view('blog/active_blogs', compact('blogs'));

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
    //show_title('Список всех комментариев '.$uz);

    $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=? AND `user`=?;", ['blog', $uz]);
    $page = App::paginate(App::setting('blogpost'), $total);

    if ($total > 0) {

        $is_admin = is_admin();

        $querycomments = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `blogs` ON `comments`.`relate_id`=`blogs`.`id` WHERE relate_type='blog' AND comments.`user`=? ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".App::setting('blogpost').";", [$uz]);
        $comments = $querycomments -> fetchAll();

        App::view('blog/active_comments', compact('comments', 'page'));

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
                redirect("/blog/active?act=comments&uz=$uz&page=$page");

            } else {
                show_error('Ошибка! Данного комментария не существует!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    App::view('includes/back', ['link' => '/blog/active?act=comments&amp;uz='.$uz.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

endswitch;

App::view('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'fa-arrow-circle-up']);

App::view(App::setting('themes').'/foot');
