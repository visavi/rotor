<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Мои данные');

if (isUser()) {
switch ($action):

/**
 * Главная
 */
case 'index':

    echo '<i class="fa fa-book"></i> ';
    echo '<a href="user/'.getUsername().'">Моя анкета</a> / ';
    echo '<a href="/profile">Мой профиль</a> / ';
    echo '<b>Мои данные</b> / ';
    echo '<a href="/setting">Настройки</a><hr>';

    echo '<b><big>Изменение email</big></b><br>';
    echo '<div class="form">';
    echo '<form method="post" action="/account?act=changemail&amp;uid='.$_SESSION['token'].'">';
    echo 'Е-mail:<br>';
    echo '<input name="meil" maxlength="50" value="'.user('email').'"><br>';
    echo 'Текущий пароль:<br>';
    echo '<input name="provpass" type="password" maxlength="20"><br>';
    echo '<input value="Изменить" type="submit"></form></div><br>';

    /**
     * Изменение персонального статуса
     */
    if (!empty(setting('editstatus'))) {
        echo '<b><big>Изменение статуса</big></b><br>';

        if (user('point') >= setting('editstatuspoint')) {
            echo '<div class="form">';
            echo '<form method="post" action="/account?act=editstatus&amp;uid='.$_SESSION['token'].'">';
            echo 'Персональный статус:<br>';
            echo '<input name="status" maxlength="20" value="'.user('status').'">';
            echo '<input value="Изменить" type="submit"></form>';

            if (!empty(setting('editstatusmoney'))) {
                echo '<br><i>Стоимость: '.plural(setting('editstatusmoney'), setting('moneyname')).'</i>';
            }

            echo '</div><br>';
        } else {
            showError('Изменять статус могут пользователи у которых более '.plural(setting('editstatuspoint'), setting('scorename')).'!');
        }
    }

    /**
     * Изменение пароля
     */
    echo '<b><big>Изменение пароля</big></b><br>';

    echo '<div class="form">';
    echo '<form method="post" action="/account?act=editpass&amp;uid='.$_SESSION['token'].'">';
    echo 'Новый пароль:<br><input name="newpass" maxlength="20"><br>';
    echo 'Повторите пароль:<br><input name="newpass2" maxlength="20"><br>';
    echo 'Текущий пароль:<br><input name="oldpass" type="password" maxlength="20"><br>';
    echo '<input value="Изменить" type="submit"></form></div><br>';

    /**
     * API-ключ
     */
    echo '<b><big>Ваш API-токен</big></b><br>';

    if(empty(user('apikey'))) {
        echo '<div class="form">';
        echo '<form method="post" action="/account?act=apikey&amp;uid='.$_SESSION['token'].'">';
        echo '<input value="Получить токен" type="submit"></form></div><br>';
    } else {
        echo '<div class="form">';
        echo '<form method="post" action="/account?act=apikey&amp;uid='.$_SESSION['token'].'">';
        echo 'Токен: <strong>'.user('apikey').'</strong><br>';
        echo '<input value="Изменить токен" type="submit"></form></div><br>';
    }
break;

/**
 * Изменение email
 */
case 'changemail':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $meil = (isset($_POST['meil'])) ? strtolower(check($_POST['meil'])) : '';
    $provpass = (isset($_POST['provpass'])) ? check($_POST['provpass']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_equal', [$meil, user('email')], 'Новый адрес email должен отличаться от текущего!')
        -> addRule('email', $meil, 'Неправильный адрес email, необходим формат name@site.domen!', true)
        -> addRule('bool', password_verify($provpass, user('password')), 'Введенный пароль не совпадает с данными в профиле!');

    $regmail = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$meil]);
    $validation -> addRule('empty', $regmail, 'Указанный вами адрес email уже используется в системе!');

    // Проверка email в черном списке
    $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $meil]);
    $validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');

    DB::delete("DELETE FROM `changemail` WHERE `created_at`<?;", [SITETIME]);
    $changemail = DB::run() -> querySingle("SELECT `id` FROM `changemail` WHERE `user_id`=? LIMIT 1;", [getUserId()]);
    $validation -> addRule('empty', $changemail, 'Вы уже отправили код подтверждения на новый адрес почты!');

    if ($validation->run()) {

        $genkey = str_random(rand(15,20));

        $subject = 'Изменение email на сайте '.setting('title');
        $message = 'Здравствуйте, '.getUsername().'<br>Вами была произведена операция по изменению адреса электронной почты<br><br>Для того, чтобы изменить email, необходимо подтвердить новый адрес почты<br>Перейдите по данной ссылке:<br><br><a href="'.siteLink(setting('home')).'/account?act=editmail&key='.$genkey.'">'.siteLink(setting('home')).'/account?act=editmail&key='.$genkey.'</a><br><br>Ссылка будет дейстительной в течение суток до '.date('j.m.y / H:i', SITETIME + 86400).', для изменения адреса необходимо быть авторизованным на сайте<br>Если это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо';

        $body = view('mailer.default', compact('subject', 'message'), true);
        sendMail($meil, $subject, $body);

        DB::insert("INSERT INTO `changemail` (`user_id`, `mail`, hash, `created_at`) VALUES (?, ?, ?, ?);", [getUserId(), $meil, $genkey, SITETIME + 86400]);

        setFlash('success', 'На новый адрес почты отправлено письмо для подтверждения!');
        redirect("/account");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br>';
break;

/**
 * Изменение email
 */
case 'editmail':

    $key = (isset($_GET['key'])) ? check(strval($_GET['key'])) : '';

    DB::delete("DELETE FROM `changemail` WHERE `created_at`<?;", [SITETIME]);
    $armail = DB::run() -> queryFetch("SELECT * FROM `changemail` WHERE hash=? AND `user_id`=? LIMIT 1;", [$key, getUserId()]);

    $validation = new Validation();

    $validation -> addRule('not_empty', $key, 'Вы не ввели код изменения электронной почты!')
        -> addRule('not_empty', $armail, 'Данный код изменения электронной почты не найден в списке!')
        -> addRule('not_equal', [$armail['mail'], user('email')], 'Новый адрес email должен отличаться от текущего!');

    $regmail = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$armail['mail']]);
    $validation -> addRule('empty', $regmail, 'Указанный вами адрес email уже используется в системе!');

    $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $armail['mail']]);
    $validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');

    if ($validation->run()) {

        DB::update("UPDATE `users` SET `email`=? WHERE `login`=? LIMIT 1;", [$armail['mail'], getUsername()]);
        DB::delete("DELETE FROM `changemail` WHERE hash=? AND `user_id`=? LIMIT 1;", [$key, getUserId()]);

        setFlash('success', 'Адрес электронной почты успешно изменен!');
        redirect("/account");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br>';
break;

/**
 * Изменение статуса
 */
case 'editstatus':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $status = (isset($_POST['status'])) ? check($_POST['status']) : '';
    $cost = (!empty($status)) ? setting('editstatusmoney') : 0;

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', setting('editstatus'), 'Изменение статуса запрещено администрацией сайта!')
        -> addRule('empty', user('ban'), 'Для изменения статуса у вас не должно быть нарушений!')
        -> addRule('not_equal', [$status, user('status')], 'Новый статус должен отличаться от текущего!')
        -> addRule('max', [user('point'), setting('editstatuspoint')], 'У вас недостаточно актива для изменения статуса!')
        -> addRule('max', [user('money'), $cost], 'У вас недостаточно денег для изменения статуса!')
        -> addRule('string', $status, 'Слишком длинный или короткий статус!', false, 3, 20);

    if (!empty($status)) {
        $checkstatus = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE lower(`status`)=? LIMIT 1;", [utfLower($status)]);
        $validation -> addRule('empty', $checkstatus, 'Выбранный вами статус уже используется на сайте!');
    }

    if ($validation->run()) {

        DB::update("UPDATE `users` SET `status`=?, `money`=`money`-? WHERE `login`=? LIMIT 1;", [$status, $cost, getUsername()]);
        saveStatus();

        setFlash('success', 'Ваш статус успешно изменен!');
        redirect("/account");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br>';
break;

/**
 * Изменение пароля
 */
case 'editpass':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $newpass = (isset($_POST['newpass'])) ? check($_POST['newpass']) : '';
    $newpass2 = (isset($_POST['newpass2'])) ? check($_POST['newpass2']) : '';
    $oldpass = (isset($_POST['oldpass'])) ? check($_POST['oldpass']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('bool', password_verify($oldpass, user('password')), 'Введенный пароль не совпадает с данными в профиле!')
        -> addRule('equal', [$newpass, $newpass2], 'Новые пароли не одинаковые!')
        -> addRule('string', $newpass, 'Слишком длинный или короткий новый пароль!', true, 6, 20)
        -> addRule('regex', [$newpass, '|^[a-z0-9\-]+$|i'], 'Недопустимые символы в пароле, разрешены знаки латинского алфавита, цифры и дефис!', true)
        -> addRule('not_equal', [getUsername(), $newpass], 'Пароль и логин должны отличаться друг от друга!');

    if (ctype_digit($newpass)) {
        $validation -> addError('Запрещен пароль состоящий только из цифр, используйте буквы!');
    }

    if ($validation->run()) {

        DB::update("UPDATE `users` SET `password`=? WHERE `login`=? LIMIT 1;", [password_hash($newpass, PASSWORD_BCRYPT), getUsername()]);

        $subject = 'Изменение пароля на сайте '.setting('title');
        $message = 'Здравствуйте, '.getUsername().'<br>Вами была произведена операция по изменению пароля<br><br><b>Ваш новый пароль: '.$newpass.'</b><br>Сохраните его в надежном месте<br><br>Данные инициализации:<br>IP: '.getClientIp().'<br>Браузер: '.getUserAgent().'<br>Время: '.date('j.m.y / H:i', SITETIME);

        $body = view('mailer.default', compact('subject', 'message'), true);
        sendMail(user('email'), $subject, $body);

        unset($_SESSION['id'], $_SESSION['password']);

        setFlash('success', 'Пароль успешно изменен!');
        redirect("/login");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br>';
break;

/**
 * Генерация ключа
 */
case 'apikey':
    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

    if ($uid == $_SESSION['token']) {

        $key = str_random();

        DB::update("UPDATE `users` SET `apikey`=? WHERE `login`=?;", [md5(getUsername().$key), getUsername()]);

        setFlash('success', 'Новый ключ успешно сгенерирован!');
        redirect("/account");
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br>';
break;

endswitch;

} else {
    showError('Для изменения данных необходимо авторизоваться');
}

view(setting('themes').'/foot');
