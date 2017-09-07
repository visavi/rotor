<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} elseif (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} else {
    $uz = "";
}

//show_title('Перевод денег');

if (isUser()) {

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo 'В наличии: '.plural(user('money'), setting('moneyname')).'<br><br>';

            if (user('point') >= setting('sendmoneypoint')) {
                if (empty($uz)) {
                    echo '<div class="form">';
                    echo '<form action="/games/transfer?act=send&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Логин юзера:<br>';
                    echo '<input type="text" name="uz" maxlength="20"><br>';
                    echo 'Кол-во денег:<br>';
                    echo '<input type="text" name="money"><br>';
                    echo 'Примечание:<br>';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';
                    echo '<input type="submit" value="Перевести"></form></div><br>';
                } else {
                    echo '<div class="form">';
                    echo 'Перевод для <b>'.$uz.'</b>:<br><br>';
                    echo '<form action="/games/transfer?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Кол-во денег:<br>';
                    echo '<input type="text" name="money"><br>';
                    echo 'Примечание:<br>';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br>';
                    echo '<input type="submit" value="Перевести"></form></div><br>';
                }
            } else {
                showError('Ошибка! Для перевода денег вам необходимо набрать '.plural(setting('sendmoneypoint'), , setting('scorename')).'!');
            }
        break;

        ############################################################################################
        ##                                       Перевод                                          ##
        ############################################################################################
        case 'send':

            $money = abs(intval($_POST['money']));
            $msg = check($_POST['msg']);
            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if ($money > 0) {
                    if (user('point') >= setting('sendmoneypoint')) {
                        if ($money <= user('money')) {
                            if ($uz != getUsername()) {
                                if ($msg <= 1000) {
                                    $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);
                                    if (!empty($queryuser)) {
                                        $ignorstr = DB::run() -> querySingle("SELECT `id` FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$uz, getUsername()]);
                                        if (empty($ignorstr)) {
                                            DB::update("UPDATE `users` SET `money`=`money`-? WHERE `login`=?;", [$money, getUsername()]);
                                            DB::update("UPDATE `users` SET `money`=`money`+?, `newprivat`=`newprivat`+1 WHERE `login`=?;", [$money, $uz]);

                                            $comment = (!empty($msg)) ? $msg : 'Не указано';
                                            // ------------------------Уведомление по привату------------------------//
                                            $textpriv = 'Пользователь [b]'.getUsername().'[/b] перечислил вам '.plural($money, setting('moneyname')).''.PHP_EOL.'Примечание: '.$comment;

                                            DB::insert("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$uz, getUsername(), $textpriv, SITETIME]);
                                            // ------------------------ Запись логов ------------------------//
                                            DB::insert("INSERT INTO `transfers` (`user`, `login`, `text`, `summ`, `time`) VALUES (?, ?, ?, ?, ?);", [getUsername(), $uz, $comment, $money, SITETIME]);

                                            DB::delete("DELETE FROM `transfers` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `transfers` ORDER BY `time` DESC LIMIT 1000) AS del);");

                                            setFlash('success', 'Перевод успешно завершен! Пользователь уведомлен о переводе');
                                            redirect("/games/transfer");

                                        } else {
                                            showError('Ошибка! Вы внесены в игнор-лист получателя!');
                                        }
                                    } else {
                                        showError('Ошибка! Данного адресата не существует!');
                                    }
                                } else {
                                    showError('Ошибка! Текст комментария не должен быть длиннее 1000 символов!');
                                }
                            } else {
                                showError('Ошибка! Запещено переводить деньги самому себе!');
                            }
                        } else {
                            showError('Ошибка! Недостаточно средств для перевода такого количества денег!');
                        }
                    } else {
                        showError('Ошибка! Для перевода денег вам необходимо набрать '.plural(setting('sendmoneypoint'), setting('scorename')).'!');
                    }
                } else {
                    showError('Ошибка! Перевод невозможен указана неверная сумма!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/transfer">Вернуться</a><br>';
        break;

    endswitch;

} else {
    showError('Для совершения операций необходимо авторизоваться');
}

echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br>';

view(setting('themes').'/foot');
