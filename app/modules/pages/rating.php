<?php

$login = check(param('login'));

if (! is_user()) {
    App::abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
}

if (App::getUsername() == $login) {
    App::abort('default', 'Запрещено изменять репутацию самому себе!');
}

if (App::user('point') < App::setting('editratingpoint')) {
    App::abort('default', 'Для изменения репутации необходимо набрать '.points($config['editratingpoint']).'!');
}


if ($getUser = user($login)) {
    $getRating = DBM::run()->selectFirst('rating', ['user' => App::getUsername(), 'login' => $login]);
    if (! $getRating) {

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $text = check(Request::input('text'));
            $vote = Request::input('vote') ? 1 : 0;

            if ($token == $_SESSION['token']) {
                if (utf_strlen($text) >= 3 && utf_strlen($text) <= 250) {
                    ############################################################################################
                    ##                                Увеличение репутации                                    ##
                    ############################################################################################
                    if ($vote == 1) {

                        $text = antimat($text);

                        DBM::run()->insert('rating', [
                            'user' => App::getUsername(),
                            'login' => $login,
                            'text' => $text,
                            'vote' => 1,
                            'time' => SITETIME,
                        ]);

                        DBM::run()->delete('rating', [
                                'time' => ['<', SITETIME - 3600 * 24 * 365]]
                        );

                        $user = DBM::run()->update('users', [
                            'rating' => (abs($getUser['posrating']) - abs($getUser['negrating'])) + 1,
                            'posrating' => ['+', 1],
                        ], [
                            'login' => $login
                        ]);

                        $text = 'Пользователь [b]' . nickname(App::getUsername()) . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($getUser['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;
                        send_private($login, App::getUsername(), $text);

                        echo '<i class="fa fa-thumbs-up"></i> Ваш положительный голос за пользователя <b>' . nickname($login) . '</b> успешно оставлен!<br />';
                        echo 'В данный момент его репутация: ' . ($getUser['rating'] + 1) . '<br />';
                        echo 'Всего положительных голосов: ' . ($getUser['posrating'] + 1) . '<br />';
                        echo 'Всего отрицательных голосов: ' . $getUser['negrating'] . '<br /><br />';

                        echo 'От общего числа положительных и отрицательных голосов строится рейтинг пользователей<br /><br />';
                        $error = 0;
                    }
                    ############################################################################################
                    ##                                Уменьшение репутации                                    ##
                    ############################################################################################
                    if ($vote == 0) {
                        if (App::user('rating') >= 10) {

                            $text = antimat($text);

                            DBM::run()->insert('rating', [
                                'user' => App::getUsername(),
                                'login' => $login,
                                'text' => $text,
                                'vote' => 0,
                                'time' => SITETIME,
                            ]);

                            DBM::run()->delete('rating', [
                                    'time' => ['<', SITETIME - 3600 * 24 * 365]]
                            );

                            $user = DBM::run()->update('users', [
                                'rating' => (abs($getUser['posrating']) - abs($getUser['negrating'])) - 1,
                                'negrating' => ['+', 1],
                            ], [
                                'login' => $login
                            ]);

                            $text = 'Пользователь [b]' . nickname(App::getUsername()) . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($getUser['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;
                            send_private($login, App::getUsername(), $text);

                            echo '<i class="fa fa-thumbs-down"></i> Ваш отрицательный голос за пользователя <b>' . nickname($login) . '</b> успешно оставлен!<br />';
                            echo 'В данный момент его репутация: ' . ($getUser['rating'] - 1) . '<br />';
                            echo 'Всего положительных голосов: ' . $getUser['posrating'] . '<br />';
                            echo 'Всего отрицательных голосов: ' . ($getUser['negrating'] + 1) . '<br /><br />';

                            echo 'От общего числа положительных и отрицательных голосов строится рейтинг пользователей<br /><br />';
                            $error = 0;

                        } else {
                            show_error('Ошибка! Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
                        }
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий комментарий!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            if (!empty($error)) {
                echo '<i class="fa fa-arrow-circle-left"></i> <a href="/rating?uz=' . $login . '&amp;vote=' . $vote . '">Вернуться</a><br />';
            }
        }

        $vote = Request::input('vote') ? 1 : 0;

        App::view('pages/rating', compact('login', 'vote'));

    } else {
        show_error('Ошибка! Вы уже изменяли репутацию этому пользователю!');
    }
} else {
    show_error('Ошибка! Данного пользователя не существует!');
}



