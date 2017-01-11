<?php

if (is_user()) {
    App::abort('403', 'Вы уже регистрировались, нельзя регистрироваться несколько раз!');
}

if (! $config['openreg']) {
    App::abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже');
}

if (Request::isMethod('post')) {
    if (Request::has('logs') && Request::has('pars')) {
        $logs = check(Request::input('logs'));
        $pars = trim(Request::input('pars'));
        $pars2 = trim(Request::input('pars2'));
        $protect = check(strtolower(Request::input('protect')));
        $invite = (!empty($config['invite'])) ? check(Request::input('invite')) : '';
        $meil = (!empty($config['regmail'])) ? strtolower(check(Request::input('meil'))) : '';
        $domain = (!empty($config['regmail'])) ? utf_substr(strrchr($meil, '@'), 1) : '';
        $gender = Request::input('gender') == 1 ? 1 : 2;
        $registration_key = '';

        $validation = new Validation();
        $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
            ->addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
            ->addRule('email', $meil, ['meil' => 'Вы ввели неверный адрес e-mail, необходим формат name@site.domen!'], $config['regmail'])
            ->addRule('string', $invite, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], $config['invite'], 15, 20)
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
            // Проверка логина или ника на существование
            $reglogin = DB::run()->querySingle("SELECT `id` FROM `users` WHERE LOWER(`login`)=? OR LOWER(`nickname`)=? LIMIT 1;", [strtolower($logs), strtolower($logs)]);
            $validation->addRule('empty', $reglogin, ['logs' => 'Пользователь с данным логином или ником уже зарегистрирован!']);

            // Проверка логина в черном списке
            $blacklogin = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($logs)]);
            $validation->addRule('empty', $blacklogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
        }

        if (!empty($config['regmail']) && !empty($meil)) {
            // Проверка email на существование
            $regmail = DB::run()->querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$meil]);
            $validation->addRule('empty', $regmail, ['meil' => 'Указанный вами адрес e-mail уже используется в системе!']);

            // Проверка домена от email в черном списке
            $blackdomain = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [3, $domain]);
            $validation->addRule('empty', $blackdomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

            // Проверка email в черном списке
            $blackmail = DB::run()->querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $meil]);
            $validation->addRule('empty', $blackmail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);
        }

        // Проверка пригласительного ключа
        if (!empty($config['invite'])) {
            $invitation = DB::run()->querySingle("SELECT `id` FROM `invite` WHERE `key`=? AND `used`=? LIMIT 1;", [$invite, 0]);
            $validation->addRule('not_empty', $invitation, ['invite' => 'Ключ приглашения недействителен!']);
        }

        // Регистрация аккаунта
        if ($validation->run()) {

            if ($config['regkeys'] == 1 && empty($config['regmail'])) {
                $config['regkeys'] = 0;
            }

            // ------------------------- Уведомление о регистрации на E-mail --------------------------//
            $regmessage = "Добро пожаловать, " . $logs . " \nТеперь вы зарегистрированный пользователь сайта " . $config['home'] . " , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. \nВаши данные для входа на сайт \nЛогин: " . $logs . " \nПароль: " . $pars . " \n\nСсылка для входа на сайт: \n" . $config['home'] . "/login \nНадеемся вам понравится на нашем портале! \nС уважением администрация сайта \nЕсли это письмо попало к вам по ошибке, то просто проигнорируйте его \n\n";

            if ($config['regkeys'] == 1) {
                $registration_key = str_random();

                echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br />';
                echo 'Мастер-ключ был выслан вам на почтовый ящик: ' . $meil . '</span></b><br /><br />';

                $regmessage .= "Внимание! \nДля подтверждения регистрации необходимо в течение 24 часов ввести мастер-ключ! \nВаш мастер-ключ: " . $registration_key . " \nВведите его после авторизации на сайте \nИли перейдите по прямой ссылке: \n\n" . $config['home'] . "/key?act=inkey&key=" . $registration_key . " \n\nЕсли в течение 24 часов вы не подтвердите регистрацию, ваш профиль будет автоматически удален";
            }

            if ($config['regkeys'] == 2) {
                echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br /><br />';

                $regmessage .= "Внимание! \nВаш аккаунт будет активирован только после проверки администрацией! \nПроверить статус активации вы сможете после авторизации на сайте";
            }

            // Активация пригласительного ключа
            if (!empty($config['invite'])) {
                DB::run()->query("UPDATE `invite` SET `used`=?, `invited`=? WHERE `key`=? LIMIT 1;", [1, $logs, $invite]);
            }

            $registration = DBM::run()->insert('users', [
                'login' => $logs,
                'password' => password_hash($pars, PASSWORD_BCRYPT),
                'email' => $meil,
                'joined' => SITETIME,
                'level' => 107,
                'gender' => $gender,
                'themes' => 0,
                'point' => 0,
                'money' => $config['registermoney'],
                'timelastlogin' => SITETIME,
                'confirmreg' => $config['regkeys'],
                'confirmregkey' => $registration_key,
                'subscribe' => str_random(32),
            ]);

            // ------------------------------ Уведомление в приват ----------------------------------//
            $textpriv = text_private(1, ['%USERNAME%' => $logs, '%SITENAME%' => $config['home']]);
            send_private($logs, $config['nickname'], $textpriv);

            if (!empty($config['regmail'])) {
                sendMail($meil, 'Регистрация на сайте ' . $config['title'], nl2br($regmessage));
            }
            // ----------------------------------------------------------------------------------------//

            $user = App::login($logs, $pars);

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

