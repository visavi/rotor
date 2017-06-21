<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Восстановление пароля');

if (! is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $cooklog = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): '';

    echo 'Инструкция по восстановлению будет выслана на электронный адрес указанный в профиле<br />';
    echo 'Восстанавливать пароль можно не чаще чем раз в 12 часов<br /><br />';

    echo '<div class="form">';
    echo '<form method="post" action="/lostpassword?act=remind">';
    echo 'Логин или email:<br />';
    echo '<input name="user" type="text" maxlength="50" value="'.$cooklog.'" /><br />';
    echo '<input value="Продолжить" type="submit" /></form></div><br />';

    echo 'Если у вас установлен секретный вопрос, вам будет предложено на него ответить<br /><br />';

break;

############################################################################################
##                                  Восстановление пароля                                 ##
############################################################################################
case 'remind':

    $login = check(Request::input('user'));

    if ($login) {

        $user = User::where('login', $login)->orWhere('email', $login)->first();

        if ($user) {

            $email = ($login == $user['email']) ? $user['email'] : '';

            echo '<div class="b">'.user_gender($user).' <b>'.profile($user).'</b> '.user_visit($user).'</div>';

            echo '<b>Восстановление на email:</b><br />';
            echo '<div class="form">';
            echo '<form method="post" action="/lostpassword?act=send">';
            echo 'Введите email:<br />';
            echo '<input name="email" type="text" value="'.$email.'" maxlength="50" /><br />';
            echo 'Проверочный код:<br /> ';
            echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;" alt="" /><br />';
            echo '<input name="protect" size="6" maxlength="6" /><br />';
            echo '<input name="user" type="hidden" value="'.$user['login'].'" />';
            echo '<br /><input value="Восстановить" type="submit" /></form></div><br />';

            // -------------------------------------------------------------//
            if ($user['secquest']) {
                echo '<b>Ответ на секретный вопрос:</b><br />';
                echo '<div class="form">';
                echo '<form method="post" action="/lostpassword?act=answer">';

                echo $user['secquest'].'<br />';
                echo '<input name="answer" type="text" maxlength="30" /><br />';

                echo 'Проверочный код:<br /> ';
                echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;" alt="" /><br />';
                echo '<input name="provkod" size="6" maxlength="6" /><br />';
                echo '<input name="user" type="hidden" value="'.$user['login'].'" />';
                echo '<br /><input value="Восстановить" type="submit" /></form></div><br />';
            }

        } else {
            show_error('Ошибка! Пользователь с данным логином или email не найден!');
        }
    } else {
        show_error('Ошибка! Вы не ввели логин или email пользователя для восстановления!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/lostpassword">Вернуться</a><br />';
break;

############################################################################################
##                            Подтверждение восстановления                                ##
############################################################################################
case 'send':

    $login = check(Request::input('user'));
    $email = check(Request::input('email'));
    $protect = check(Request::input('protect'));

    $user = User::where('login', $login)->first();
    if ($user) {

        $validation = new Validation();

        $validation -> addRule('equal', [$protect, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
            -> addRule('not_empty', $email, 'Не введен адрес почтового ящика для восстановления!')
            -> addRule('not_empty', $user['email'], 'У данного пользователя не установлен email!')
            -> addRule('equal', [$email, $user['email']], 'Введенный адрес email не совпадает с адресом в профиле!')
            -> addRule('min', [$user['timepasswd'], SITETIME], 'Восстанавливать пароль можно не чаще чем раз в 12 часов!');

        if ($validation->run()) {

            $sitelink = starts_with(App::setting('home'), '//') ? 'http:'. App::setting('home') : App::setting('home');
            $resetKey = str_random();
            $resetLink = $sitelink.'/lostpassword?act=restore&user='.$user['login'].'&key='.$resetKey;

            DB::run() -> query("UPDATE `users` SET `keypasswd`=?, `timepasswd`=? WHERE `id`=?;", [$resetKey, SITETIME + 43200, $user->id]);
            // ---------------- Инструкция по восстановлению пароля на email --------------------------//

            $subject = 'Восстановление пароля на сайте '.App::setting('title');
            $message = 'Здравствуйте, '.$user['login'].'<br />Вами была произведена операция по восстановлению пароля на сайте <a href="' . App::setting('home') . '">' . App::setting('title') . '</a><br /><br />Данные отправителя:<br />Ip: '.App::getClientIp().'<br />Браузер: '.App::getUserAgent().'<br />Отправлено: '.date('j.m.Y / H:i', SITETIME).'<br /><br />Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br />Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

            $body = App::view('mailer.recovery', compact('subject', 'message', 'resetLink'), true);
            App::sendMail($user['email'], $subject, $body);

            echo '<i class="fa fa-check"></i> <b>Восстановление пароля инициализировано!</b><br /><br />';
            echo 'Письмо с инструкцией по восстановлению пароля успешно выслано на email указанный в профиле<br />';
            echo 'Внимательно прочтите письмо и выполните все необходимые действия для восстановления пароля<br />';
            echo 'Восстанавливать пароль можно не чаще чем раз в 12 часов<br /><br />';

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }

break;

############################################################################################
##                                Восстановление пароля                                   ##
############################################################################################
case 'restore':

    $uz = check(Request::input('uz'));
    $key = check(Request::input('key'));

    $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
    if (!empty($user)) {

        $validation = new Validation();

        $validation -> addRule('not_empty', $key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
            -> addRule('not_empty', $user['keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
            -> addRule('equal', [$key, $user['keypasswd']], 'Секретный код в ссылке не совпадает с данными в профиле!')
            -> addRule('max', [$user['timepasswd'], SITETIME], 'Секретный ключ для восстановления уже устарел!');

        if ($validation->run()) {

            $newpass = str_random();
            $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);;

            DB::run() -> query("UPDATE `users` SET `password`=?, `keypasswd`=?, `timepasswd`=? WHERE `login`=?;", [$hashnewpas, '', 0, $uz]);

            echo '<b>Пароль успешно восстановлен!</b><br />';
            echo 'Ваши новые данные для входа на сайт<br /><br />';

            echo 'Логин: <b>'.$user['login'].'</b><br />';
            echo 'Пароль: <b>'.$newpass.'</b><br /><br />';

            echo 'Запомните и постарайтесь больше не забывать данные<br /><br />';

            echo 'Пароль вы сможете поменять в своем профиле<br /><br />';

            // --------------------------- Восстановлению пароля на email --------------------------//
            sendMail($user['email'],
                'Восстановление пароля на сайте '.App::setting('title'),
                nl2br("Здравствуйте, ".$user['login']." \nВаши новые данные для входа на на сайт ".App::setting('home')." \nЛогин: ".$user['login']." \nПароль: ".$newpass." \n\nЗапомните и постарайтесь больше не забывать данные \nПароль вы сможете поменять в своем профиле \nВсего наилучшего!")
            );

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/lostpassword">Вернуться</a><br />';
break;

############################################################################################
##                            Ответ на секретный вопрос                                   ##
############################################################################################
case 'answer':

    $uz = check(Request::input('uz'));
    $answer = check(Request::input('answer'));
    $provkod = check(Request::input('provkod'));

    $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
    if (!empty($user)) {

        $validation = new Validation();

        $validation -> addRule('equal', [$provkod, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
            -> addRule('not_empty', $answer, 'Не введен ответ на секретный вопрос для восстановления!')
            -> addRule('not_empty', $user['secquest'], 'У данного пользователя не установлен секретный вопрос!')
            -> addRule('equal', [md5(md5($answer)), $user['secanswer']], 'Ответ на секретный вопрос не совпадает с данными в профиле!');

        if ($validation->run()) {

            $newpass = str_random();
            $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);

            DB::run() -> query("UPDATE `users` SET `password`=?, `keypasswd`=?, `timepasswd`=? WHERE `login`=?;", [$hashnewpas, '', 0, $uz]);

            echo '<b>Пароль успешно восстановлен!</b><br />';
            echo 'Ваши новые данные для входа на сайт<br /><br />';

            echo 'Логин: <b>'.$user['login'].'</b><br />';
            echo 'Пароль: <b>'.$newpass.'</b><br /><br />';

            echo 'Запомните и постарайтесь больше не забывать данные<br /><br />';

            echo 'Пароль вы сможете поменять в своем профиле<br /><br />';

        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Пользователь с данным логином не найден!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/lostpassword?act=remind&amp;uz='.$uz.'">Вернуться</a><br />';
break;

endswitch;

} else {
    show_error('Ошибка! Вы авторизованы, восстановление пароля невозможно!');
}

App::view(App::setting('themes').'/foot');
