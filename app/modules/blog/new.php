<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'blogs';

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'blogs':
    show_title('Список новых статей');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs`;");

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        $page = App::paginate(App::setting('blogpost'), $total);

        $queryblog = DB::run() -> query("SELECT `blogs`.*, `name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`category_id`=`catsblog`.`id` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";");
        $blogs = $queryblog->fetchAll();

        render('blog/new_blogs', compact('blogs'));

        App::pagination($page);
    } else {
        show_error('Опубликованных статей еще нет!');
    }
break;

############################################################################################
##                                     Вывод сообщений                                    ##
############################################################################################
case 'comments':
    show_title('Список последних комментариев');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `comments` WHERE relate_type=?;", ['blog']);

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        $page = App::paginate(App::setting('blogpost'), $total);

        $querycomment = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `blogs` ON `comments`.`relate_id`=`blogs`.`id` WHERE relate_type='blog' ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";");
        $comments = $querycomment->fetchAll();

        render('blog/new_comments', compact('comments'));

        App::pagination($page);
    } else {
        show_error('Комментарии не найдены!');
    }
break;

endswitch;

render('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif']);

App::view($config['themes'].'/foot');
