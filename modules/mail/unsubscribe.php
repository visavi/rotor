<?php
App::view($config['themes'].'/index');

$key = isset($_GET['key']) ? check($_GET['key']) : '';

show_title('Отписка от рассылки');
############################################################################################
##                                    Главная страница                                    ##
############################################################################################

if (! empty($key)) {

    $user = DBM::run()->queryFirst("SELECT * FROM `users` WHERE BINARY `users_subscribe`=:key LIMIT 1;", compact('key'));
    if ($user) {

        $user = DBM::run()->update('users', array(
            'users_subscribe' => '',
        ), array(
            'users_login' => $user['users_login']
        ));

        echo '<img src="/images/img/open.gif" alt="image" /> <b>Вы успешно отписались от рассылки!</b><br />';

    } else {
        show_error('Ошибка! Ключ для отписки от рассылки устарел!');
    }
} else {
    show_error('Ошибка! Отсутствует ключ для отписки от рассылки!');
}

App::view($config['themes'].'/foot');
