<?php
header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

$ban = ORM::forTable('ban')->where('ip', App::getClientIp())->whereNull('user')->findOne();

if (Request::isMethod('post')) {

    $protect = check(Request::input('protect'));

    if ($ban && $protect == $_SESSION['protect']) {

        $ban = ORM::forTable('ban')->where('ip', App::getClientIp())->deleteMany();

        save_ipban();

        App::setFlash('success', 'IP успешно разбанен!');
        App::redirect('/');
    }
}

App::view('pages/banip', compact('ban'));
