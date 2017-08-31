<?php

if (! is_user()) {
    abort(403, 'Для подтверждение регистрации  необходимо быть авторизованным!');
}

if (empty(setting('regkeys'))) {
    abort('default', 'Подтверждение регистрации выключено на сайте!');
}

if (empty(setting('regkeys'))) {
    abort('default', 'Вашему профилю не требуется подтверждение регистрации!');
}

if (Request::has('code')) {
    $code = check(trim(Request::input('code')));

    if ($code == user('confirmregkey')) {
        DB::run() -> query("UPDATE users SET confirmreg=?, confirmregkey=? WHERE id=?;", [0, '', getUserId()]);

        setFlash('success', 'Мастер-код успешно подтвержден!');
        redirect("/");

    } else {
        setFlash('danger', 'Мастер-код не совпадает с данными, проверьте правильность ввода');
    }
}

view('pages/key');
