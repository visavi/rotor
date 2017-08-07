<?php

if (is_user()) {
    App::abort('403', 'Вы уже регистрировались, запрещено создавать несколько аккаунтов!');
}

if (! Setting::get('openreg')) {
    App::abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже!');
}

if (Request::isMethod('post')) {
    if (Request::has('logs') && Request::has('pars')) {
        $logs = check(Request::input('logs'));
        $pars = trim(Request::input('pars'));
        $pars2 = trim(Request::input('pars2'));
        $protect = check(strtolower(Request::input('protect')));
        $invite = (!empty(Setting::get('invite'))) ? check(Request::input('invite')) : '';
        $meil = strtolower(check(Request::input('meil')));
        $domain = utf_substr(strrchr($meil, '@'), 1);
        $gender = Request::input('gender') == 1 ? 1 : 2;
        $activateKey = '';

        $validation = new Validation();
        $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
            ->addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
            ->addRule('email', $meil, ['meil' => 'Вы ввели неверный адрес email, необходим формат name@site.domen!'], true)
            ->addRule('string', $invite, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], Setting::get('invite'), 12, 15)
            ->addRule('string', $logs, ['logs' => 'Слишком длинный или короткий логин!'], true, 3, 20)
            ->addRule('string', $pars, ['pars' => 'Слишком длинный или короткий пароль!'], true, 6, 20)
            ->addRule('equal', [$pars, $pars2], ['pars2' => 'Ошибка! Введенные пароли отличаются друг от друга!']);

        if (ctype_digit($pars)) {
            $validation->addError(['pars' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
        }

        if (substr_count($logs, '-') > 2) {
            $validation->addError(['logs' => 'Запрещено использовать в логине слишком много дефисов!']);
        }

        if (!empty($logs)) {
            // Проверка логина на существование
            $reglogin = DB::run()->querySingle("SELECT `id` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($logs)]);
            $validation->addRule('empty', $reglogin, ['logs' => 'Пользователь с данным логином уже зарегистрирован!']);

            // Проверка логина в черном списке
            $blacklogin = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($logs)]);
            $validation->addRule('empty', $blacklogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
        }

        // Проверка email на существование
        $regmail = DB::run()->querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$meil]);
        $validation->addRule('empty', $regmail, ['meil' => 'Указанный вами адрес email уже используется в системе!']);

        // Проверка домена от email в черном списке
        $blackdomain = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [3, $domain]);
        $validation->addRule('empty', $blackdomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

        // Проверка email в черном списке
        $blackmail = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $meil]);
        $validation->addRule('empty', $blackmail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);

        // Проверка пригласительного ключа
        if (!empty(Setting::get('invite'))) {
            $invitation = DB::run()->querySingle("SELECT `id` FROM `invite` WHERE `hash`=? AND `used`=? LIMIT 1;", [$invite, 0]);
            $validation->addRule('not_empty', $invitation, ['invite' => 'Ключ приглашения недействителен!']);
        }

        // Регистрация аккаунта
        if ($validation->run()) {

            // --- Уведомление о регистрации на email ---//
            $message = 'Добро пожаловать, ' . $logs . '<br />Теперь вы зарегистрированный пользователь сайта <a href="' . Setting::get('home') . '">' . Setting::get('title') . '</a> , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. <br />Ваши данные для входа на сайт <br /><b>Логин: ' . $logs . '</b><br /><b>Пароль: ' . $pars . '</b><br /><br />';

            if (Setting::get('regkeys') == 1) {
                $siteLink = starts_with(Setting::get('home'), '//') ? 'http:'. Setting::get('home') : Setting::get('home');
                $activateKey = str_random();
                $activateLink = $siteLink.'/key?code=' . $activateKey;

                echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br />';
                echo 'Мастер-ключ был выслан вам на почтовый ящик: ' . $meil . '</span></b><br /><br />';
            }

            if (Setting::get('regkeys') == 2) {
                echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br /><br />';
            }

            // Активация пригласительного ключа
            if (!empty(Setting::get('invite'))) {
                DB::run()->query("UPDATE `invite` SET `used`=?, `invited`=? WHERE `key`=? LIMIT 1;", [1, $logs, $invite]);
            }

            $user = User::create([
                'login' => $logs,
                'password' => password_hash($pars, PASSWORD_BCRYPT),
                'email' => $meil,
                'joined' => SITETIME,
                'level' => 107,
                'gender' => $gender,
                'themes' => 0,
                'point' => 0,
                'money' => Setting::get('registermoney'),
                'timelastlogin' => SITETIME,
                'confirmreg' => Setting::get('regkeys'),
                'confirmregkey' => $activateKey,
                'subscribe' => str_random(32),
            ]);

            // ----- Уведомление в приват ----//
            $textpriv = text_private(1, ['%USERNAME%' => $logs, '%SITENAME%' => Setting::get('home')]);
            send_private($user->id, 0, $textpriv);

            $subject = 'Регистрация на сайте ' . Setting::get('title');
            $body = App::view('mailer.register', compact('subject', 'message', 'activateKey', 'activateLink'), true);
            App::sendMail($meil, $subject, $body);

            App::login($logs, $pars);

            App::setFlash('success', 'Добро пожаловать, ' . $logs . '!');
            App::redirect('/');

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    if (Request::has('token')) {
        App::socialLogin(Request::input('token'));
    }
}

App::view('pages/registration');

