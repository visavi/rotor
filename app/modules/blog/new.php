<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'blogs';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

switch ($act):
############################################################################################
##                                        Вывод тем                                       ##
############################################################################################
case 'blogs':
    show_title('Список новых статей');
    //$config['header'] = array('site.png', 'Список новых статей2');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs`;");

    if ($total > 0) {
        if ($total > 100) {
            $total = 100;
        }
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $queryblog = DB::run() -> query("SELECT `blogs`.*, `name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`category_id`=`catsblog`.`id` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";");
        $blogs = $queryblog->fetchAll();

        render('blog/new_blogs', ['blogs' => $blogs]);

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
        if ($start >= $total) {
            $start = last_page($total, $config['blogpost']);
        }

        $querycomment = DB::run() -> query("SELECT `comments`.*, `title`, `comments` FROM `comments` LEFT JOIN `blogs` ON `comments`.`relate_id`=`blogs`.`id` WHERE relate_type='blog' ORDER BY comments.`time` DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";");
        $comments = $querycomment->fetchAll();

        render('blog/new_comments', ['comments' => $comments]);

        App::pagination($page);
    } else {
        show_error('Комментарии не найдены!');
    }
break;

endswitch;

render('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif']);

App::view($config['themes'].'/foot');
