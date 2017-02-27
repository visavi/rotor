<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (empty($_GET['uz'])) ? check($log) : check($_GET['uz']);

show_title('Блоги');
$config['newtitle'] = 'Блоги - Список разделов';

    $queryblog = DB::run() -> query("SELECT *, (SELECT COUNT(*) FROM `blogs` WHERE `blogs`.`category_id` = `catsblog`.`id` AND `blogs`.`time` > ?) AS `new` FROM `catsblog` ORDER BY sort ASC;", [SITETIME-86400 * 3]);

    $blogs = $queryblog -> fetchAll();

    if (count($blogs) > 0) {

        App::view('blog/index', ['blogs' => $blogs]);

    } else {
        show_error('Разделы блогов еще не созданы!');
    }

App::view($config['themes'].'/foot');
