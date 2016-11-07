<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

show_title('Подтверждение регистрации');

if (is_user()) {
    if (!empty($config['regkeys'])) {
        if (!empty($udata['confirmreg'])) {
            if ($udata['confirmreg'] == 1) {
                switch ($act):
                ############################################################################################
                ##                                    Главная страница                                    ##
                ############################################################################################
                    case "index":

                        echo 'Добро пожаловать, <b>'.check($log).'!</b><br />';
                        echo 'Для подтверждения регистрации вам необходимо ввести мастер-ключ, который был отправлен вам на E-mail<br /><br />';

                        echo '<div class="form">';
                        echo 'Мастер-код:<br />';
                        echo '<form method="post" action="/key?act=inkey">';
                        echo '<input name="key" maxlength="30" />';
                        echo '<input value="Подтвердить" type="submit" /></form></div><br />';

                        echo 'Пока вы не подтвердите регистрацию вы не сможете войти на сайт<br />';
                        echo 'Ваш профиль будет ждать активации в течении 24 часов, после чего автоматически удален<br /><br />';

                        echo '<i class="fa fa-times"></i> <a href="/logout">Выход</a><br />';
                    break;

                    ############################################################################################
                    ##                                   Проверка мастер-ключа                                ##
                    ############################################################################################
                    case "inkey":

                        if (isset($_GET['key'])) {
                            $key = check(trim($_GET['key']));
                        } else {
                            $key = check(trim($_POST['key']));
                        }

                        if (!empty($key)) {
                            if ($key == $udata['confirmregkey']) {
                                DB::run() -> query("UPDATE users SET confirmreg=?, confirmregkey=? WHERE login=?;", [0, '', $log]);

                                echo 'Мастер-код подтвержден, теперь вы можете войти на сайт!<br /><br />';
                                echo '<i class="fa fa-check"></i> <b><a href="/">Вход на сайт!</a></b><br /><br />';
                            } else {
                                show_error('Ошибка! Мастер-код не совпадает с данными, проверьте правильность ввода!');
                            }
                        } else {
                            show_error('Ошибка! Вы не ввели мастер-код, пожалуйста повторите!');
                        }

                        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/key">Вернуться</a><br />';
                    break;

                endswitch;
            } else {
                echo 'Добро пожаловать, <b>'.check($log).'!</b><br />';
                echo 'Ваш аккаунт еще не прошел проверку администрацией<br />';
                echo 'Если после авторизации вы видите эту страницу, значит ваш профиль еще не активирован!<br /><br />';
                echo '<i class="fa fa-times"></i> <a href="/logout">Выход</a><br />';
            }
        } else {
            show_error('Ошибка! Вашему профилю не требуется подтверждение регистрации!');
        }
    } else {
        show_error('Ошибка! Подтверждение регистрации выключено на сайте!');
    }
} else {
    show_error('Ошибка! Для подтверждение регистрации  необходимо быть авторизованным!');
}

App::view($config['themes'].'/foot');
