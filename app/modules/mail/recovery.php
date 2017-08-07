<?php

if (is_user()) {
    App::abort('default', 'Вы авторизованы, восстановление пароля невозможно!');
}

switch ($act):

############################################################################################
##                                  Восстановление пароля                                 ##
############################################################################################
case 'index':

    $cookieLogin = (isset($_COOKIE['login'])) ? check($_COOKIE['login']): '';

    if (Request::isMethod('post')) {
        $login = check(Request::input('user'));
        $protect = check(Request::input('protect'));

        $user = User::where('login', $login)->orWhere('email', $login)->first();
        if (! $user) {
            App::abort('default', 'Пользователь с данным логином или email не найден!');
        }

        $validation = new Validation();

        $validation -> addRule('equal', [$protect, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
            -> addRule('min', [$user['timepasswd'], SITETIME], 'Восстанавливать пароль можно не чаще чем раз в 12 часов!');

        if ($validation->run()) {

            $sitelink = starts_with(Setting::get('home'), '//') ? 'http:'. Setting::get('home') : Setting::get('home');
            $resetKey = str_random();
            $resetLink = $sitelink.'/recovery/restore?key='.$resetKey;

            DB::run() -> query("UPDATE `users` SET `keypasswd`=?, `timepasswd`=? WHERE `id`=?;", [$resetKey, SITETIME + 43200, $user->id]);

            //Инструкция по восстановлению пароля на email
            $subject = 'Восстановление пароля на сайте '.Setting::get('title');
            $message = 'Здравствуйте, '.$user['login'].'<br />Вами была произведена операция по восстановлению пароля на сайте <a href="' . Setting::get('home') . '">' . Setting::get('title') . '</a><br /><br />Данные отправителя:<br />Ip: '.App::getClientIp().'<br />Браузер: '.App::getUserAgent().'<br />Отправлено: '.date('j.m.Y / H:i', SITETIME).'<br /><br />Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br />Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

            $body = App::view('mailer.recovery', compact('subject', 'message', 'resetLink'), true);
            App::sendMail($user['email'], $subject, $body);

            App::setFlash('success', 'Восстановление пароля инициализировано!');
            App::redirect('/recovery');
        } else {
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('mail/recovery', compact('cookieLogin'));
break;

############################################################################################
##                                Восстановление пароля                                   ##
############################################################################################
case 'restore':

    $key = check(Request::input('key'));

    $user = User::where('keypasswd', $key)->first();
    if (! $user) {
        App::abort('default', 'Ключ для восстановления недействителен!');
    }

    $validation = new Validation();

    $validation -> addRule('not_empty', $key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
        -> addRule('not_empty', $user['keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
        -> addRule('max', [$user['timepasswd'], SITETIME], 'Секретный ключ для восстановления уже устарел!');

    if ($validation->run()) {

        $newpass = str_random();
        $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);

        DB::run() -> query("UPDATE `users` SET `password`=?, `keypasswd`=?, `timepasswd`=? WHERE `id`=?;", [$hashnewpas, '', 0, $user->id]);

        // Восстановление пароля на email
        $subject = 'Восстановление пароля на сайте '.Setting::get('title');
        $message = 'Здравствуйте, '.$user['login'].'<br />Ваши новые данные для входа на на сайт <a href="' . Setting::get('home') . '">' . Setting::get('title') . '</a><br /><b>Логин: '.$user['login'].'</b><br /><b>Пароль: '.$newpass.'</b><br /><br />Запомните и постарайтесь больше не забывать данные <br />Пароль вы сможете поменять в своем профиле<br />Всего наилучшего!';

        $body = App::view('mailer.default', compact('subject', 'message'), true);
        App::sendMail($user['email'], $subject, $body);

        App::view('mail/restore', ['login' => $user['login'], 'password' => $newpass]);
    } else {
        App::setFlash('danger', current($validation->getErrors()));
        App::redirect('/');
    }
break;

endswitch;
