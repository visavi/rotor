<?php
header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

$ban = ORM::for_table('ban')->where('ip', App::getClientIp())->where_null('user')->find_one();

if (Request::isMethod('post')) {

    $protect = check(Request::input('protect'));

    if ($ban && $protect == $_SESSION['protect']) {

        $ban = ORM::for_table('ban')->where('ip', App::getClientIp())->delete_Many();

        save_ipban();

        App::setFlash('success', 'IP успешно разбанен!');
        App::redirect('/');
    }
}

App::view('pages/banip', compact('ban'));
