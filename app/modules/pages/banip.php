<?php
header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

$ban = DBM::run()->queryFirst(
    'SELECT * FROM ban WHERE ip = :ip AND user IS NULL LIMIT 1;',
    ['ip' => App::getClientIp()]
);

if (Request::isMethod('post')) {

    $protect = check(Request::input('protect'));

    if ($ban && $protect == $_SESSION['protect']) {

        DBM::run()->delete('ban', ['ip' => App::getClientIp()]);
        save_ipban();

        App::setFlash('success', 'IP успешно разбанен!');
        App::redirect('/');
    }
}


App::view('pages/banip', compact('ban'));

