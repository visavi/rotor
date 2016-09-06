<?php
App::view($config['themes'].'/index', compact('udata'));

if (!empty($_GET['page'])){

    $page = check($_GET['page']);

    if (! preg_match('|^[a-z0-9_\-/]+$|i', $page)) {
        App::abort('default', 'Недопустимое название страницы!');
    }

    $file = explode('/', $page);

    if (empty($file[1])){
        $page = $page.'/index';
    }

    if (! file_exists(BASEDIR.'/modules/files/'.$page.'.dat')) {
        App::abort('default', 'Ошибка! Данной страницы не существует!');
    }

    include_once (BASEDIR.'/modules/files/'.$page.'.dat');

} else {
	include_once (DATADIR.'/main/files.dat');
}

App::view($config['themes'].'/foot');
