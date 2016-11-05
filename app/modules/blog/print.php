<?php
App::view($config['themes'].'/index');

$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

show_title('Блоги - Печать страницы');

$blog = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", array($id));

if (!empty($blog)) {

    while (ob_get_level()) {
        ob_end_clean();
    }
    $blog['text'] = preg_replace('|\[nextpage\](<br * /?>)*|', '', $blog['text']);

    header("Content-Encoding: none");
    header('Content-type:text/html; charset=utf-8');
    die(render('blog/print', array('blog' => $blog)));

} else {
    show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
}

render('includes/back', array('link' => '/blog', 'title' => 'К блогам', 'icon' => 'reload.gif'));

App::view($config['themes'].'/foot');
