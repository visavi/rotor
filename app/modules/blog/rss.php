<?php
App::view($config['themes'].'/index');

$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

show_title('RSS комментарии');

$blog = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

if (!empty($blog)) {
    $querycomm = DB::run() -> query("SELECT * FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `time` DESC LIMIT 15;", ['blog', $id]);
    $comments = $querycomm->fetchAll();

    while (ob_get_level()) {
        ob_end_clean();
    }

    header("Content-Encoding: none");
    header("Content-type:application/rss+xml; charset=utf-8");
    die(render('blog/rss', compact('blog', 'comments')));

} else {
    show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
}

render('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif']);

App::view($config['themes'].'/foot');
