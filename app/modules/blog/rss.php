<?php
App::view($config['themes'].'/index');

$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

show_title('RSS комментарии');

$blog = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", array($id));

if (!empty($blog)) {
    $querycomm = DB::run() -> query("SELECT * FROM `commblog` WHERE `blog`=? ORDER BY `time` DESC LIMIT 15;", array($id));
    $comments = $querycomm->fetchAll();

    while (ob_get_level()) {
        ob_end_clean();
    }

    header("Content-Encoding: none");
    header("Content-type:application/rss+xml; charset=utf-8");
    die(render('blog/rss', array('blog' => $blog, 'comments' => $comments)));

} else {
    show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
}

render('includes/back', array('link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif'));

App::view($config['themes'].'/foot');
