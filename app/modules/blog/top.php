<?php
App::view($config['themes'].'/index');

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
$page = App::paginate(App::setting('blogpost'), $total);

if ($total > 0) {

    $queryblog = DB::run() -> query("SELECT `b`.*, `name` FROM `blogs` b LEFT JOIN `catsblog` cb ON `b`.`category_id`=`cb`.`id` ORDER BY ".$order." DESC LIMIT ".$page['offset'].", ".$config['blogpost'].";");
    $blogs = $queryblog->fetchAll();

    App::view('blog/top', compact('blogs', 'order'));

    App::pagination($page);
} else {
    show_error('Опубликованных статей еще нет!');
}

App::view('includes/back', ['link' => '/blog', 'title' => 'Категории', 'icon' => 'fa-arrow-circle-up']);

App::view($config['themes'].'/foot');
