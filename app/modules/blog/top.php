<?php
App::view($config['themes'].'/index');

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'visits';

switch ($sort) {
    case 'rated': $order = 'rating';
        break;
    case 'comm': $order = 'comments';
        break;
    default: $order = 'visits';
}
############################################################################################
##                                       Топ тем                                          ##
############################################################################################
show_title('Топ популярных блогов');

$total = DB::run() -> querySingle("SELECT count(*) FROM `blogs`;");

if ($total > 0) {
    if ($start >= $total) {
        $start = last_page($total, $config['blogpost']);
    }

    $queryblog = DB::run() -> query("SELECT `b`.*, `name` FROM `blogs` b LEFT JOIN `catsblog` cb ON `b`.`category_id`=`cb`.`id` ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['blogpost'].";");
    $blogs = $queryblog->fetchAll();

    render('blog/top', ['blogs' => $blogs, 'order' => $order]);

    page_strnavigation('/blog/top?sort='.$sort.'&amp;', $config['blogpost'], $start, $total);
} else {
    show_error('Опубликованных статей еще нет!');
}

render('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif']);

App::view($config['themes'].'/foot');
