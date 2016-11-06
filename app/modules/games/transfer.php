<?php
App::view($config['themes'].'/index');

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

show_title('Перевод денег');

if (is_user()) {

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo 'В наличии: '.moneys($udata['money']).'<br /><br />';

            if ($udata['point'] >= $config['sendmoneypoint']) {
                if (empty($uz)) {
                    echo '<div class="form">';
                    echo '<form action="/transfer?act=send&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Логин юзера:<br />';
                    echo '<input type="text" name="uz" maxlength="20" /><br />';
                    echo 'Кол-во денег:<br />';
                    echo '<input type="text" name="money" /><br />';
                    echo 'Примечание:<br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Перевести" /></form></div><br />';
                } else {
                    echo '<div class="form">';
                    echo 'Перевод для <b>'.$uz.'</b>:<br /><br />';
                    echo '<form action="/transfer?act=send&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Кол-во денег:<br />';
                    echo '<input type="text" name="money" /><br />';
                    echo 'Примечание:<br />';
                    echo '<textarea cols="25" rows="5" name="msg"></textarea><br />';
                    echo '<input type="submit" value="Перевести" /></form></div><br />';
                }
            } else {
                show_error('Ошибка! Для перевода денег вам необходимо набрать '.points($config['sendmoneypoint']).'!');
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
                    if ($udata['point'] >= $config['sendmoneypoint']) {
                        if ($money <= $udata['money']) {
                            if ($uz != $log) {
                                if ($msg <= 1000) {
                                    $queryuser = DB::run() -> querySingle("SELECT `id` FROM `users` WHERE `login`=? LIMIT 1;", array($uz));
                                    if (!empty($queryuser)) {
                                        $ignorstr = DB::run() -> querySingle("SELECT `id` FROM `ignore` WHERE `user`=? AND `name`=? LIMIT 1;", array($uz, $log));
                                        if (empty($ignorstr)) {
                                            DB::run() -> query("UPDATE `users` SET `money`=`money`-? WHERE `login`=?;", array($money, $log));
                                            DB::run() -> query("UPDATE `users` SET `money`=`money`+?, `newprivat`=`newprivat`+1 WHERE `login`=?;", array($money, $uz));

                                            $comment = (!empty($msg)) ? $msg : 'Не указано';
                                            // ------------------------Уведомление по привату------------------------//
                                            $textpriv = 'Пользователь [b]'.nickname($log).'[/b] перечислил вам '.moneys($money).''.PHP_EOL.'Примечание: '.$comment;

                                            DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", array($uz, $log, $textpriv, SITETIME));
                                            // ------------------------ Запись логов ------------------------//
                                            DB::run() -> query("INSERT INTO `transfers` (`user`, `login`, `text`, `summ`, `time`) VALUES (?, ?, ?, ?, ?);", array($log, $uz, $comment, $money, SITETIME));

                                            DB::run() -> query("DELETE FROM `transfers` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `transfers` ORDER BY `time` DESC LIMIT 1000) AS del);");

                                            notice('Перевод успешно завершен! Пользователь уведомлен о переводе');
                                            redirect("/transfer");

                                        } else {
                                            show_error('Ошибка! Вы внесены в игнор-лист получателя!');
                                        }
                                    } else {
                                        show_error('Ошибка! Данного адресата не существует!');
                                    }
                                } else {
                                    show_error('Ошибка! Текст комментария не должен быть длиннее 1000 символов!');
                                }
                            } else {
                                show_error('Ошибка! Запещено переводить деньги самому себе!');
                            }
                        } else {
                            show_error('Ошибка! Недостаточно средств для перевода такого количества денег!');
                        }
                    } else {
                        show_error('Ошибка! Для перевода денег вам необходимо набрать '.points($config['sendmoneypoint']).'!');
                    }
                } else {
                    show_error('Ошибка! Перевод невозможен указана неверная сумма!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/transfer">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы совершать операции, необходимо');
}

App::view($config['themes'].'/foot');
