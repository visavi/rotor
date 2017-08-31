<?php
header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

$ban = Ban::where('ip', getClientIp())
    ->whereNull('user_id')
    ->first();

if (Request::isMethod('post')) {

    $protect = check(Request::input('protect'));

    if ($ban && $protect == $_SESSION['protect']) {

        $ban->delete();

        ipBan(true);

        setFlash('success', 'IP успешно разбанен!');
        redirect('/');
    }
}

view('pages/banip', compact('ban'));
