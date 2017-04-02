<?php
header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

$ban = Ban::where('ip', App::getClientIp())
    ->whereNull('user_id')
    ->first();

if (Request::isMethod('post')) {

    $protect = check(Request::input('protect'));

    if ($ban && $protect == $_SESSION['protect']) {

        $ban->delete();

        save_ipban();

        App::setFlash('success', 'IP успешно разбанен!');
        App::redirect('/');
    }
}

App::view('pages/banip', compact('ban'));
