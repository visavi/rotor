<?php
App::view($config['themes'].'/index');

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$sort = (isset($_GET['sort'])) ? check($_GET['sort']) : 'read';

switch ($sort) {
    case 'rated': $order = 'blogs_rating';
        break;
    case 'comm': $order = 'blogs_comments';
        break;
    default: $order = 'blogs_read';
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

    $queryblog = DB::run() -> query("SELECT `blogs`.*, `cats_id`, `cats_name` FROM `blogs` LEFT JOIN `catsblog` ON `blogs`.`blogs_cats_id`=`catsblog`.`cats_id` ORDER BY ".$order." DESC LIMIT ".$start.", ".$config['blogpost'].";");
    $blogs = $queryblog->fetchAll();

    render('blog/top', array('blogs' => $blogs, 'order' => $order));

    page_strnavigation('/blog/top?sort='.$sort.'&amp;', $config['blogpost'], $start, $total);
} else {
    show_error('Опубликованных статей еще нет!');
}

render('includes/back', array('link' => '/blog', 'title' => 'Категории', 'icon' => 'reload.gif'));

App::view($config['themes'].'/foot');
