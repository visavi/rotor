<?php

if (is_user()) {
    App::abort('403', 'Вы уже регистрировались, нельзя регистрироваться несколько раз!');
}

if (! $config['openreg']) {
    App::abort('default', 'Регистрация временно приостановлена, пожалуйста зайдите позже');
}

if (Request::isMethod('post')) {

    $logs    = check(Request::input('logs'));
    $pars    = check(Request::input('pars'));
    $pars2   = check(Request::input('pars2'));
    $protect = check(strtolower(Request::input('protect')));
    $invite  = (!empty($config['invite'])) ? check(Request::input('invite')) : '';
    $meil    = (!empty($config['regmail'])) ? strtolower(check(Request::input('meil'))) : '';
    $domain  = (!empty($config['regmail'])) ? utf_substr(strrchr($meil, '@'), 1) : '';
    $gender  = Request::input('gender') == 1 ? 1 : 2;
    $registration_key = '';

    $validation = new Validation();
    $validation -> addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
        -> addRule('regex', [$logs, '|^[a-z0-9\-]+$|i'], ['logs' => 'Недопустимые символы в логине. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
        -> addRule('regex', [$pars, '|^[a-z0-9\-]+$|i'], ['pars' => 'Недопустимые символы в пароле. Разрешены знаки латинского алфавита, цифры и дефис!'], true)
        -> addRule('email', $meil, ['meil' => 'Вы ввели неверный адрес e-mail, необходим формат name@site.domen!'], $config['regmail'])
        -> addRule('string', $invite, ['invite' => 'Слишком длинный или короткий пригласительный ключ!'], $config['invite'], 15, 20)
        -> addRule('string', $logs, ['logs' => 'Слишком длинный или короткий логин!'], true, 3, 20)
        -> addRule('string', $pars, ['pars' => 'Слишком длинный или короткий пароль!'],  true, 6, 20)
        -> addRule('equal', [$pars, $pars2], ['pars2' => 'Ошибка! Введенные пароли отличаются друг от друга!']);

    if (ctype_digit($pars)) {
        $validation -> addError(['pars' => 'Запрещен пароль состоящий только из цифр, используйте буквы!']);
    }

    if (substr_count($logs, '-') > 2) {
        $validation -> addError(['logs' => 'Запрещено использовать в логине слишком много дефисов!']);
    }

    if (!empty($logs)){
        // Проверка логина или ника на существование
        $reglogin = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array(strtolower($logs), strtolower($logs)));
        $validation -> addRule('empty', $reglogin, ['logs' => 'Пользователь с данным логином или ником уже зарегистрирован!']);

        // Проверка логина в черном списке
        $blacklogin = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(2, strtolower($logs)));
        $validation -> addRule('empty', $blacklogin, ['logs' => 'Выбранный вами логин занесен в черный список!']);
    }

    if (!empty($config['regmail']) && !empty($meil)){
        // Проверка email на существование
        $regmail = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_email`=? LIMIT 1;", array($meil));
        $validation -> addRule('empty', $regmail, ['meil' => 'Указанный вами адрес e-mail уже используется в системе!']);

        // Проверка домена от email в черном списке
        $blackdomain = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(3, $domain));
        $validation -> addRule('empty', $blackdomain, ['meil' => 'Домен от вашего адреса email занесен в черный список!']);

        // Проверка email в черном списке
        $blackmail = DB::run() -> querySingle("SELECT `black_id` FROM `blacklist` WHERE `black_type`=? AND `black_value`=? LIMIT 1;", array(1, $meil));
        $validation -> addRule('empty', $blackmail, ['meil' => 'Указанный вами адрес email занесен в черный список!']);
    }

    // Проверка пригласительного ключа
    if (!empty($config['invite'])){
        $invitation = DB::run() -> querySingle("SELECT `id` FROM `invite` WHERE `key`=? AND `used`=? LIMIT 1;", array($invite, 0));
        $validation -> addRule('not_empty', $invitation, ['invite' => 'Ключ приглашения недействителен!']);
    }

    // Регистрация аккаунта
    if ($validation->run()){

        if ($config['regkeys'] == 1 && empty($config['regmail'])) {
            $config['regkeys'] = 0;
        }

        // ------------------------- Уведомление о регистрации на E-mail --------------------------//
        $regmessage = "Добро пожаловать, ".$logs." \nТеперь вы зарегистрированный пользователь сайта ".$config['home']." , сохраните ваш пароль и логин в надежном месте, они вам еще пригодятся. \nВаши данные для входа на сайт \nЛогин: ".$logs." \nПароль: ".$pars." \n\nСсылка для автоматического входа на сайт: \n".$config['home']."/input.php?login=".$logs."&pass=".$pars." \nНадеемся вам понравится на нашем портале! \nС уважением администрация сайта \nЕсли это письмо попало к вам по ошибке, то просто проигнорируйте его \n\n";

        if ($config['regkeys'] == 1) {
            $registration_key = generate_password();

            echo '<b><span style="color:#ff0000">Внимание! После входа на сайт, вам будет необходимо ввести мастер-ключ для подтверждения регистрации<br />';
            echo 'Мастер-ключ был выслан вам на почтовый ящик: '.$meil.'</span></b><br /><br />';

            $regmessage .= "Внимание! \nДля подтверждения регистрации необходимо в течении 24 часов ввести мастер-ключ! \nВаш мастер-ключ: ".$registration_key." \nВведите его после авторизации на сайте \nИли перейдите по прямой ссылке: \n\n".$config['home']."/pages/key.php?act=inkey&key=".$registration_key." \n\nЕсли в течении 24 часов вы не подтвердите регистрацию, ваш профиль будет автоматически удален";
        }

        if ($config['regkeys'] == 2) {
            echo '<b><span style="color:#ff0000">Внимание! Ваш аккаунт будет активирован только после проверки администрацией!</span></b><br /><br />';

            $regmessage .= "Внимание! \nВаш аккаунт будет активирован только после проверки администрацией! \nПроверить статус активации вы сможете после авторизации на сайте";
        }

        // Активация пригласительного ключа
        if (!empty($config['invite'])){
            DB::run() -> query("UPDATE `invite` SET `used`=?, `invited`=? WHERE `key`=? LIMIT 1;", array(1, $logs, $invite));
        }

        $registration = DBM::run()->insert('users', array(
            'users_login'         => $logs,
            'users_pass'          => md5(md5($pars)),
            'users_email'         => $meil,
            'users_joined'        => SITETIME,
            'users_level'         => 107,
            'users_gender'        => $gender,
            'users_themes'        => 0,
            'users_postguest'     => $config['bookpost'],
            'users_postnews'      => $config['postnews'],
            'users_postprivat'    => $config['privatpost'],
            'users_postforum'     => $config['forumpost'],
            'users_themesforum'   => $config['forumtem'],
            'users_postboard'     => $config['boardspost'],
            'users_point'         => 0,
            'users_money'         => $config['registermoney'],
            'users_timelastlogin' => SITETIME,
            'users_confirmreg'    => $config['regkeys'],
            'users_confirmregkey' => $registration_key,
            'users_navigation'    => $config['navigation'],
            'users_subscribe'     => generate_password(32),
        ));

        // ------------------------------ Уведомление в приват ----------------------------------//
        $textpriv = text_private(1, array('%USERNAME%'=>$logs, '%SITENAME%'=>$config['home']));
        send_private($logs, $config['nickname'], $textpriv);

        if (!empty($config['regmail'])) {
            sendMail($meil, 'Регистрация на сайте '.$config['title'], nl2br($regmessage));
        }
        // ----------------------------------------------------------------------------------------//

        $user = App::login($logs, md5(md5($pars)));

        App::setFlash('success', 'Вы успешно зарегистрированы!');
        App::redirect('/');

    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }
}

App::view('pages/registration');

