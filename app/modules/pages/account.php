<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Мои данные');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Изменение email                                    ##
############################################################################################
case 'index':

    echo '<i class="fa fa-book"></i> ';
    echo '<a href="user/'.App::getUsername().'">Моя анкета</a> / ';
    echo '<a href="/profile">Мой профиль</a> / ';
    echo '<b>Мои данные</b> / ';
    echo '<a href="/setting">Настройки</a><hr />';

    echo '<b><big>Изменение email</big></b><br />';
    echo '<div class="form">';
    echo '<form method="post" action="/account?act=changemail&amp;uid='.$_SESSION['token'].'">';
    echo 'Е-mail:<br />';
    echo '<input name="meil" maxlength="50" value="'.App::user('email').'" /><br />';
    echo 'Текущий пароль:<br />';
    echo '<input name="provpass" type="password" maxlength="20" /><br />';
    echo '<input value="Изменить" type="submit" /></form></div><br />';

    ############################################################################################
    ##                                Изменение ника                                          ##
    ############################################################################################
/*    echo '<b><big>Изменение ника</big></b><br />';

    if (App::user('point') >= App::setting('editnickpoint')) {
        echo '<div class="form">';
        echo '<form method="post" action="/account?act=editnick&amp;uid='.$_SESSION['token'].'">';
        echo 'Ваш ник:<br />';
        echo '<input name="nickname" maxlength="20" value="'.App::user('nickname').'" />';
        echo '<input value="Изменить" type="submit" /></form></div><br />';
    } else {
        show_error('Изменять ник могут пользователи у которых более '.points(App::setting('editnickpoint')).'!');
    }*/
    ############################################################################################
    ##                        Изменение персонального статуса                                 ##
    ############################################################################################
    if (!empty(App::setting('editstatus'))) {
        echo '<b><big>Изменение статуса</big></b><br />';

        if (App::user('point') >= App::setting('editstatuspoint')) {
            echo '<div class="form">';
            echo '<form method="post" action="/account?act=editstatus&amp;uid='.$_SESSION['token'].'">';
            echo 'Персональный статус:<br />';
            echo '<input name="status" maxlength="20" value="'.App::user('status').'" />';
            echo '<input value="Изменить" type="submit" /></form>';

            if (!empty(App::setting('editstatusmoney'))) {
                echo '<br /><i>Стоимость: '.moneys(App::setting('editstatusmoney')).'</i>';
            }

            echo '</div><br />';
        } else {
            show_error('Изменять статус могут пользователи у которых более '.points(App::setting('editstatuspoint')).'!');
        }
    }
    ############################################################################################
    ##                                  Секретный вопрос                                      ##
    ############################################################################################
    echo '<b><big>Секретный вопрос</big></b><br />';
    echo '<div class="form">';
    echo '<form method="post" action="/account?act=editsec&amp;uid='.$_SESSION['token'].'">';
    echo 'Секретный вопрос:<br />';
    echo '<input name="secquest" maxlength="50" value="'.App::user('secquest').'" /><br />';
    echo 'Ответ на вопрос:<br /><input name="secanswer" maxlength="30" /><br />';
    echo 'Текущий пароль:<br /><input name="provpass" type="password" maxlength="20" /><br />';
    echo '<input value="Изменить" type="submit" /></form></div><br />';

    ############################################################################################
    ##                                    Изменение пароля                                    ##
    ############################################################################################
    echo '<b><big>Изменение пароля</big></b><br />';

    echo '<div class="form">';
    echo '<form method="post" action="/account?act=editpass&amp;uid='.$_SESSION['token'].'">';
    echo 'Новый пароль:<br /><input name="newpass" maxlength="20" /><br />';
    echo 'Повторите пароль:<br /><input name="newpass2" maxlength="20" /><br />';
    echo 'Текущий пароль:<br /><input name="oldpass" type="password" maxlength="20" /><br />';
    echo '<input value="Изменить" type="submit" /></form></div><br />';

    ############################################################################################
    ##                                    API-ключ                                            ##
    ############################################################################################
    echo '<b><big>Ваш API-ключ</big></b><br />';

    if(empty(App::user('apikey'))) {
        echo '<div class="form">';
        echo '<form method="post" action="/account?act=apikey&amp;uid='.$_SESSION['token'].'">';
        echo '<input value="Получить ключ" type="submit" /></form></div><br />';
    } else {
        echo '<div class="form">';
        echo '<form method="post" action="/account?act=apikey&amp;uid='.$_SESSION['token'].'">';
        echo 'Ключ: <strong>'.App::user('apikey').'</strong><br />';
        echo '<input value="Изменить ключ" type="submit" /></form></div><br />';
    }
break;

############################################################################################
##                                     Изменение email                                   ##
############################################################################################
case 'changemail':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $meil = (isset($_POST['meil'])) ? strtolower(check($_POST['meil'])) : '';
    $provpass = (isset($_POST['provpass'])) ? check($_POST['provpass']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_equal', [$meil, App::user('email')], 'Новый адрес email должен отличаться от текущего!')
        -> addRule('email', $meil, 'Неправильный адрес email, необходим формат name@site.domen!', true)
        -> addRule('bool', password_verify($provpass, App::user('password')), 'Введенный пароль не совпадает с данными в профиле!');

    $regmail = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$meil]);
    $validation -> addRule('empty', $regmail, 'Указанный вами адрес email уже используется в системе!');

    // Проверка email в черном списке
    $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $meil]);
    $validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');

    DB::run() -> query("DELETE FROM `changemail` WHERE `time`<?;", [SITETIME]);
    $changemail = DB::run() -> querySingle("SELECT `id` FROM `changemail` WHERE `user`=? LIMIT 1;", [App::getUsername()]);
    $validation -> addRule('empty', $changemail, 'Вы уже отправили код подтверждения на новый адрес почты!');

    if ($validation->run()) {

        $genkey = str_random(rand(15,20));

        sendMail($meil,
            'Изменение адреса электронной почты на сайте '.App::setting('title'),
            nl2br("Здравствуйте, ".App::getUsername()." \nВами была произведена операция по изменению адреса электронной почты \n\nДля того, чтобы изменить email, необходимо подтвердить новый адрес почты \nПерейдите по данной ссылке: \n\n".App::setting('home')."/account?act=editmail&key=".$genkey." \n\nСсылка будет дейстительной в течение суток до ".date('j.m.y / H:i', SITETIME + 86400).", для изменения адреса необходимо быть авторизованным на сайте \nЕсли это сообщение попало к вам по ошибке или вы не собираетесь менять email, то просто проигнорируйте данное письмо")
        );

        DB::run() -> query("INSERT INTO `changemail` (`user`, `mail`, hash, `time`) VALUES (?, ?, ?, ?);", [App::getUsername(), $meil, $genkey, SITETIME + 86400]);

        App::setFlash('success', 'На новый адрес почты отправлено письмо для подтверждения!');
        App::redirect("/account");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение email                                   ##
############################################################################################
case 'editmail':

    $key = (isset($_GET['key'])) ? check(strval($_GET['key'])) : '';

    DB::run() -> query("DELETE FROM `changemail` WHERE `time`<?;", [SITETIME]);
    $armail = DB::run() -> queryFetch("SELECT * FROM `changemail` WHERE hash=? AND `user`=? LIMIT 1;", [$key, App::getUsername()]);

    $validation = new Validation();

    $validation -> addRule('not_empty', $key, 'Вы не ввели код изменения электронной почты!')
        -> addRule('not_empty', $armail, 'Данный код изменения электронной почты не найден в списке!')
        -> addRule('not_equal', [$armail['mail'], App::user('email')], 'Новый адрес email должен отличаться от текущего!');

    $regmail = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$armail['mail']]);
    $validation -> addRule('empty', $regmail, 'Указанный вами адрес email уже используется в системе!');

    $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $armail['mail']]);
    $validation -> addRule('empty', $blackmail, 'Указанный вами адрес email занесен в черный список!');

    if ($validation->run()) {

        DB::run() -> query("UPDATE `users` SET `email`=? WHERE `login`=? LIMIT 1;", [$armail['mail'], App::getUsername()]);
        DB::run() -> query("DELETE FROM `changemail` WHERE hash=? AND `user`=? LIMIT 1;", [$key, App::getUsername()]);

        App::setFlash('success', 'Адрес электронной почты успешно изменен!');
        App::redirect("/account");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

############################################################################################
##                                   Изменение статуса                                    ##
############################################################################################
case 'editstatus':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $status = (isset($_POST['status'])) ? check($_POST['status']) : '';
    $cost = (!empty($status)) ? App::setting('editstatusmoney') : 0;

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', App::setting('editstatus'), 'Изменение статуса запрещено администрацией сайта!')
        -> addRule('empty', App::user('ban'), 'Для изменения статуса у вас не должно быть нарушений!')
        -> addRule('not_equal', [$status, App::user('status')], 'Новый статус должен отличаться от текущего!')
        -> addRule('max', [App::user('point'), App::setting('editstatuspoint')], 'У вас недостаточно актива для изменения статуса!')
        -> addRule('max', [App::user('money'), $cost], 'У вас недостаточно денег для изменения статуса!')
        -> addRule('string', $status, 'Слишком длинный или короткий статус!', false, 3, 20);

    if (!empty($status)) {
        $checkstatus = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE lower(`status`)=? LIMIT 1;", [utf_lower($status)]);
        $validation -> addRule('empty', $checkstatus, 'Выбранный вами статус уже используется на сайте!');
    }

    if ($validation->run()) {

        DB::run() -> query("UPDATE `users` SET `status`=?, `money`=`money`-? WHERE `login`=? LIMIT 1;", [$status, $cost, App::getUsername()]);
        save_title();

        App::setFlash('success', 'Ваш статус успешно изменен!');
        App::redirect("/account");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение ника                                     ##
############################################################################################
/*case 'editnick':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $nickname = (isset($_POST['nickname'])) ? check($_POST['nickname']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('max', [App::user('point'), App::setting('editnickpoint')], 'У вас недостаточно актива для изменения ника!')
        -> addRule('min', [App::user('timenickname'), SITETIME], 'Изменять ник можно не чаще чем 1 раз в сутки!')
        -> addRule('regex', [$nickname, '|^[0-9a-zA-Zа-яА-ЯЁё_\.\-\s]+$|u'], 'Разрешены символы русского, латинского алфавита и цифры!')
        -> addRule('string', $nickname, 'Слишком длинный или короткий ник!', false, 3, 20)
        -> addRule('not_equal', [$nickname, App::user('nickname')], 'Новый ник должен отличаться от текущего!');

    if (!empty($nickname)) {
        $reglogin = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE lower(`login`)=? LIMIT 1;", [utf_lower($nickname)]);
        $validation -> addRule('empty', $reglogin, 'Выбранный вами ник используется кем-то в качестве логина!');

        $regnick = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE lower(`nickname`)=? LIMIT 1;", [utf_lower($nickname)]);
        $validation -> addRule('empty', $regnick, 'Выбранный вами ник уже используется на сайте!');

        $blacklogin = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, utf_lower($nickname)]);
        $validation -> addRule('empty', $blacklogin, 'Выбранный вами ник занесен в черный список!');
    }

    if ($validation->run()) {

        DB::run() -> query("UPDATE `users` SET `nickname`=?, `timenickname`=? WHERE `login`=? LIMIT 1;", [$nickname, SITETIME + 86400, App::getUsername()]);
        save_nickname();

        App::setFlash('success', 'Ваш ник успешно изменен!');
        App::redirect("/account");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;*/

############################################################################################
##                                    Изменение вопроса                                   ##
############################################################################################
case 'editsec':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $secquest = (isset($_POST['secquest'])) ? check($_POST['secquest']) : '';
    $secanswer = (isset($_POST['secanswer'])) ? check($_POST['secanswer']) : '';
    $provpass = (isset($_POST['provpass'])) ? check($_POST['provpass']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('bool', password_verify($provpass, App::user('password')), 'Введенный пароль не совпадает с данными в профиле!')
        -> addRule('not_equal', [$secquest, App::user('secquest')], 'Новый секретный вопрос должен отличаться от текущего!')
        -> addRule('string', $secquest, 'Слишком длинный или короткий секретный вопрос!', false, 3, 50);

    if (!empty($secquest)) {
        $validation -> addRule('string', $secanswer, 'Слишком длинный или короткий секретный ответ!', true, 3, 30);
        $secanswer = md5(md5($secanswer));
    } else {
        $secanswer = '';
    }

    if ($validation->run()) {

        DB::run() -> query("UPDATE `users` SET `secquest`=?, `secanswer`=? WHERE `login`=? LIMIT 1;", [$secquest, $secanswer, App::getUsername()]);

        App::setFlash('success', 'Секретный вопрос и ответ успешно изменены!');
        App::redirect("/account");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

############################################################################################
##                                     Изменение пароля                                   ##
############################################################################################
case 'editpass':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $newpass = (isset($_POST['newpass'])) ? check($_POST['newpass']) : '';
    $newpass2 = (isset($_POST['newpass2'])) ? check($_POST['newpass2']) : '';
    $oldpass = (isset($_POST['oldpass'])) ? check($_POST['oldpass']) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('bool', password_verify($oldpass, App::user('password')), 'Введенный пароль не совпадает с данными в профиле!')
        -> addRule('equal', [$newpass, $newpass2], 'Новые пароли не одинаковые!')
        -> addRule('string', $newpass, 'Слишком длинный или короткий новый пароль!', true, 6, 20)
        -> addRule('regex', [$newpass, '|^[a-z0-9\-]+$|i'], 'Недопустимые символы в пароле, разрешены знаки латинского алфавита, цифры и дефис!', true)
        -> addRule('not_equal', [App::getUsername(), $newpass], 'Пароль и логин должны отличаться друг от друга!');

    if (ctype_digit($newpass)) {
        $validation -> addError('Запрещен пароль состоящий только из цифр, используйте буквы!');
    }

    if ($validation->run()) {

        DB::run() -> query("UPDATE `users` SET `password`=? WHERE `login`=? LIMIT 1;", [password_hash($newpass, PASSWORD_BCRYPT), App::getUsername()]);

        if (! empty(App::user('email'))){
            sendMail(App::user('email'),
                'Изменение пароля на сайте '.App::setting('title'),
                nl2br("Здравствуйте, ".App::getUsername()." \nВами была произведена операция по изменению пароля \n\nВаш новый пароль: ".$newpass." \nСохраните его в надежном месте \n\nДанные инициализации: \nIP: ".App::getClientIp()." \nБраузер: ".App::getUserAgent()." \nВремя: ".date('j.m.y / H:i', SITETIME))
            );
        }

        unset($_SESSION['id'], $_SESSION['password']);

        App::setFlash('success', 'Пароль успешно изменен!');
        App::redirect("/login");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

############################################################################################
##                                     Генерация ключа                                    ##
############################################################################################
case 'apikey':
    $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

    if ($uid == $_SESSION['token']) {

        $key = str_random();

        DB::run() -> query("UPDATE `users` SET `apikey`=? WHERE `login`=?;", [md5(App::getUsername().$key), App::getUsername()]);

        App::setFlash('success', 'Новый ключ успешно сгенерирован!');
        App::redirect("/account");
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/account">Вернуться</a><br />';
break;

endswitch;

} else {
    show_login('Вы не авторизованы, чтобы изменять свои данные, необходимо');
}

App::view(App::setting('themes').'/foot');
