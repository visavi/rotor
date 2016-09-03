<?php

$domain = check_string($config['home']);

switch ($act):
############################################################################################
##                                       Авторизация                                      ##
############################################################################################
case 'index':

    $cooklog = (isset($_COOKIE['cooklog'])) ? check($_COOKIE['cooklog']): '';

    if (Request::isMethod('post')) {

        $login      = check(utf_lower(Request::input('login')));
        $pass       = md5(md5(trim(Request::input('pass'))));
        $cookietrue = Request::input('cookietrue');

        if (!empty($login) && !empty($pass)) {

            $user = DB::run() -> queryFetch("SELECT `users_login`, `users_pass` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", [$login, $login]);

            if (!empty($user)) {
                if ($pass == $user['users_pass']) {

                    if (!empty($cookietrue)) {
                        setcookie('cooklog', $user['users_login'], time() + 3600 * 24 * 365, '/', $domain);
                        setcookie('cookpar', md5($pass.$config['keypass']), time() + 3600 * 24 * 365, '/', $domain, null, true);
                    }

                    $_SESSION['log'] = $user['users_login'];
                    $_SESSION['par'] = md5($config['keypass'].$pass);
                    $_SESSION['my_ip'] = App::getClientIp();

                    DB::run() -> query("UPDATE `users` SET `users_visits`=`users_visits`+1, `users_timelastlogin`=? WHERE `users_login`=?", [SITETIME, $user['users_login']]);

                    $authorization = DB::run() -> querySingle("SELECT `login_id` FROM `login` WHERE `login_user`=? AND `login_time`>? LIMIT 1;", [$user['users_login'], SITETIME-30]);

                    if (empty($authorization)) {
                        DB::run() -> query("INSERT INTO `login` (`login_user`, `login_ip`, `login_brow`, `login_time`, `login_type`) VALUES (?, ?, ?, ?, ?);", [$user['users_login'], App::getClientIp(), App::getUserAgent(), SITETIME, 1]);
                        DB::run() -> query("DELETE FROM `login` WHERE `login_user`=? AND `login_time` < (SELECT MIN(`login_time`) FROM (SELECT `login_time` FROM `login` WHERE `login_user`=? ORDER BY `login_time` DESC LIMIT 50) AS del);", [$user['users_login'], $user['users_login']]);
                    }

                    App::setFlash('success', 'Вы успешно авторизованы!');
                    App::redirect('/');
                }
            }
        }
        App::setFlash('danger', 'Ошибка авторизации. Неправильный логин или пароль!');
    }

    App::view('pages/login', compact('cooklog'));
break;
############################################################################################
##                                           Выход                                        ##
############################################################################################
case 'logout':

    $_SESSION = [];
    setcookie('cookpar', '', time() - 3600, '/', $domain, null, true);
    setcookie(session_name(), '', time() - 3600, '/', '');
    session_destroy();

    App::redirect('/');
break;

endswitch;
