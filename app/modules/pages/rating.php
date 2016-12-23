<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['uz'])) {
    $uz = check(strval($_GET['uz']));
} else {
    $uz = '';
}

show_title('Изменение репутации');

if (is_user()) {
    if ($log != $uz) {
        if ($udata['point'] >= $config['editratingpoint']) {
            $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
            if (!empty($queryuser)) {
                $querytime = DB::run() -> querySingle("SELECT MAX(`time`) FROM `rating` WHERE `user`=? LIMIT 1;", [$log]);
                if ($querytime + 10800 < SITETIME) {
                    $queryrat = DB::run() -> querySingle("SELECT `id` FROM `rating` WHERE `user`=? AND `login`=? AND `time`>? LIMIT 1;", [$log, $uz, SITETIME-86400 * 30]);
                    if (empty($queryrat)) {

                        switch ($act):
                        ############################################################################################
                        ##                                    Главная страница                                    ##
                        ############################################################################################
                            case 'index':
                                $vote = (empty($_GET['vote'])) ? 0 : 1;

                                echo '<div class="b">'.user_avatars($uz).' <b>'.nickname($uz).' </b> '.user_visit($uz).'</div>';

                                echo '<div class="form">';
                                echo '<form action="/rating?act=change&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';

                                echo 'Рейтинг:<br />';
                                echo '<select name="vote">';
                                $selected = ($vote == 1) ? ' selected="selected"' : '';
                                echo '<option value="1"'.$selected.'>Плюс</option>';
                                $selected = ($vote == 0) ? ' selected="selected"' : '';
                                echo '<option value="0"'.$selected.'>Минус</option>';
                                echo '</select><br />';

                                echo 'Комментарий: <br /><textarea cols="25" rows="5" name="text"></textarea><br />';

                                echo '<input type="submit" value="Продолжить" /></form></div><br />';
                            break;

                            ############################################################################################
                            ##                                  Изменение репутации                                   ##
                            ############################################################################################
                            case 'change':
                                $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
                                $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
                                $vote = (empty($_POST['vote'])) ? 0 : 1;

                                if ($uid == $_SESSION['token']) {
                                    if (utf_strlen($text) >= 3 && utf_strlen($text) <= 250) {
                                        ############################################################################################
                                        ##                                Увеличение репутации                                    ##
                                        ############################################################################################
                                        if ($vote == 1) {

                                            $text = antimat($text);

                                            DB::run() -> query("INSERT INTO `rating` (`user`, `login`, `text`, `vote`, `time`) VALUES (?, ?, ?, ?, ?);", [$log, $uz, $text, 1, SITETIME]);
                                            DB::run() -> query("DELETE FROM `rating` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `rating` WHERE `user`=? ORDER BY `time` DESC LIMIT 20) AS del);", [$log, $log]);

                                            DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1, `rating`=CAST(`posrating`AS SIGNED)-CAST(`negrating`AS SIGNED)+1, `posrating`=`posrating`+1 WHERE `login`=? LIMIT 1;", [$uz]);

                                            $uzdata = DB::run() -> queryFetch("SELECT `rating`, `posrating`, `negrating` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                                            // ------------------------------Уведомление по привату------------------------//
                                            $textpriv = 'Пользователь [b]'.nickname($log).'[/b] поставил вам плюс! (Ваш рейтинг: '.$uzdata['rating'].')'.PHP_EOL.'Комментарий: '.$text;

                                            DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, $log, $textpriv, SITETIME]);

                                            echo '<i class="fa fa-thumbs-up"></i> Ваш положительный голос за пользователя <b>'.nickname($uz).'</b> успешно оставлен!<br />';
                                            echo 'В данный момент его репутация: '.$uzdata['rating'].'<br />';
                                            echo 'Всего положительных голосов: '.$uzdata['posrating'].'<br />';
                                            echo 'Всего отрицательных голосов: '.$uzdata['negrating'].'<br /><br />';

                                            echo 'От общего числа положительных и отрицательных голосов строится рейтинг пользователей<br />';
                                            echo 'Внимание, следующий голос вы сможете оставить не менее чем через 3 часа!<br /><br />';

                                            $error = 0;
                                        }
                                        ############################################################################################
                                        ##                                Уменьшение репутации                                    ##
                                        ############################################################################################
                                        if ($vote == 0) {
                                            if ($udata['rating'] >= 10) {

                                                /* Запрещаем ставить обратный минус */
                                                $revertRating = DB::run() -> querySingle("SELECT `id` FROM `rating` WHERE `user`=? AND `login`=? AND `vote`=? ORDER BY `time` DESC LIMIT 1;", [$uz, $log, 0]);
                                                if (empty($revertRating)) {

                                                    $text = antimat($text);

                                                    DB::run() -> query("INSERT INTO `rating` (`user`, `login`, `text`, `vote`, `time`) VALUES (?, ?, ?, ?, ?);", [$log, $uz, $text, 0, SITETIME]);
                                                    DB::run() -> query("DELETE FROM `rating` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `rating` WHERE `user`=? ORDER BY `time` DESC LIMIT 20) AS del);", [$log, $log]);

                                                    DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1, `rating`=CAST(`posrating`AS SIGNED)-CAST(`negrating`AS SIGNED)-1, `negrating`=`negrating`+1 WHERE `login`=? LIMIT 1;", [$uz]);

                                                    $uzdata = DB::run() -> queryFetch("SELECT `rating`, `posrating`, `negrating` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                                                    // ------------------------------Уведомление по привату------------------------//
                                                    $textpriv = 'Пользователь [b]'.nickname($log).'[/b] поставил вам минус! (Ваш рейтинг: '.$uzdata['rating'].')'.PHP_EOL.'Комментарий: '.$text;

                                                    DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, $log, $textpriv, SITETIME]);

                                                    echo '<i class="fa fa-thumbs-down"></i> Ваш отрицательный голос за пользователя <b>'.nickname($uz).'</b> успешно оставлен!<br />';
                                                    echo 'В данный момент его репутация: '.$uzdata['rating'].'<br />';
                                                    echo 'Всего положительных голосов: '.$uzdata['posrating'].'<br />';
                                                    echo 'Всего отрицательных голосов: '.$uzdata['negrating'].'<br /><br />';

                                                    echo 'От общего числа положительных и отрицательных голосов строится рейтинг пользователей<br />';
                                                    echo 'Внимание, следующий голос вы сможете оставить не менее чем через 3 часа!<br /><br />';

                                                    $error = 0;

                                                } else {
                                                    show_error('Ошибка! Запрещено ставить обратный минус пользователю!');
                                                }
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
                                    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/rating?uz='.$uz.'&amp;vote='.$vote.'">Вернуться</a><br />';
                                }
                            break;

                        endswitch;
                    } else {
                        show_error('Ошибка! Вы уже изменяли репутацию этому пользователю!');
                    }
                } else {
                    show_error('Ошибка! Разрешается изменять репутацию раз в 3 часа!');
                }
            } else {
                show_error('Ошибка! Данного пользователя не существует!');
            }
        } else {
            show_error('Ошибка! Для изменения репутации вам необходимо набрать '.points($config['editratingpoint']).'!');
        }
    } else {
        show_error('Ошибка! Нельзя изменять репутацию самому себе!');
    }
} else {
    show_login('Вы не авторизованы, чтобы изменять репутацию, необходимо');
}

echo '<i class="fa fa-briefcase"></i> <a href="/rathist?uz='.$uz.'">История</a><br />';
echo '<i class="fa fa-arrow-circle-up"></i> <a href="/user/'.$uz.'">В анкету</a><br />';

App::view($config['themes'].'/foot');
