<?php

if (! is_user()) {
    App::abort(403, 'Для подтверждение регистрации  необходимо быть авторизованным!');
}

if (empty(Setting::get('regkeys'))) {
    App::abort('default', 'Подтверждение регистрации выключено на сайте!');
}

if (empty(Setting::get('regkeys'))) {
    App::abort('default', 'Вашему профилю не требуется подтверждение регистрации!');
}

if (Request::has('code')) {
    $code = check(trim(Request::input('code')));

    if ($code == App::user('confirmregkey')) {
        DB::run() -> query("UPDATE users SET confirmreg=?, confirmregkey=? WHERE id=?;", [0, '', App::getUserId()]);

        App::setFlash('success', 'Мастер-код успешно подтвержден!');
        App::redirect("/");

    } else {
        App::setFlash('danger', 'Мастер-код не совпадает с данными, проверьте правильность ввода');
    }
}

App::view('pages/key');
