<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Вклады');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $databank = DB::run() -> queryFetch("SELECT * FROM `bank` WHERE `user`=? LIMIT 1;", [$log]);
            if (!empty($databank)) {
                echo '<b>Выписка по счету</b><br />';
                echo 'На руках: '.moneys($udata['money']).'<br />';
                echo 'В банке: '.moneys($databank['sum']).'<br /><br />';

                if ($databank['sum'] > 0) {
                    if ($databank['sum'] <= $config['maxsumbank']) {
                        if ($databank['time'] >= SITETIME) {
                            echo '<b>До получения процентов осталось '.formattime($databank['time'] - SITETIME).'</b><br />';
                            echo 'Будет получено с процентов: '.moneys(percent_bank($databank['sum'])).'<br /><br />';
                        } else {
                            echo '<b>Получение процентов</b> ('.moneys(percent_bank($databank['sum'])).')<br />';
                            echo '<div class="form">';
                            echo '<form action="/games/bank?act=prolong&amp;uid='.$_SESSION['token'].'" method="post">';

                            echo '<select name="oper">';
                            echo '<option value="0">Получить на руки</option><option value="1">Положить в банк</option>';
                            echo '</select><br />';

                            echo 'Проверочный код:<br /> ';
                            echo '<img src="/captcha" alt="" /><br />';
                            echo '<input name="provkod" size="6" maxlength="6" /><br />';

                            echo '<input value="Получить" type="submit" /></form></div><br />';
                        }
                    } else {
                        echo '<b><span style="color:#ff0000">Внимание у вас слишком большой вклад</span></b><br />';
                        echo 'Превышена максимальная сумма вклада для получения процентов на '.moneys($databank['sum'] - $config['maxsumbank']).'<br /><br />';
                    }
                } else {
                    echo 'Для получения процентов на счете должны быть средства, но не более '.moneys($config['maxsumbank']).'<br /><br />';
                }
            } else {
                echo 'Вы новый клиент нашего банка. Мы рады, что вы доверяеете нам свои деньги<br />';
                echo 'Сейчас ваш счет не открыт, вложите свои средства, чтобы получать проценты с вклада<br /><br />';
            }

            echo '<b>Операция</b><br />';

            echo '<div class="form">';
            echo '<form action="/games/bank?act=operacia" method="post">';
            echo '<input type="text" name="gold" /><br />';
            echo '<select name="oper">';
            echo '<option value="2">Положить деньги</option><option value="1">Снять деньги</option>';
            echo '</select><br />';
            echo '<input type="submit" value="Выполнить" /></form></div><br />';

            echo 'Максимальная сумма вклада: '.moneys($config['maxsumbank']).'<br /><br />';
            echo 'Процентная ставка зависит от суммы вклада<br />';
            echo 'Вклад до 100 тыс. - ставка 10%<br />';
            echo 'Вклад более 100 тыс. - ставка 6%<br />';
            echo 'Вклад более 250 тыс. - ставка 3%<br />';
            echo 'Вклад более 500 тыс. - ставка 2%<br />';
            echo 'Вклад более 1 млн. - ставка 1%<br />';
            echo 'Вклад более 5 млн. - ставка 0.5%<br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `bank`;");

            echo 'Всего вкладчиков: <b>'.$total.'</b><br /><br />';
        break;

        ############################################################################################
        ##                                 Получене процентов                                     ##
        ############################################################################################
        case 'prolong':

            $uid = check($_GET['uid']);
            $oper = (empty($_POST['oper'])) ? 0 : 1;
            $provkod = check(strtolower($_POST['provkod']));

            if ($uid == $_SESSION['token']) {
                if ($provkod == $_SESSION['protect']) {
                    $databank = DB::run() -> queryFetch("SELECT * FROM `bank` WHERE `user`=? LIMIT 1;", [$log]);
                    if (!empty($databank)) {
                        if ($databank['sum'] > 0 && $databank['sum'] <= $config['maxsumbank']) {
                            if ($databank['time'] < SITETIME) {
                                $percent = percent_bank($databank['sum']);

                                if (empty($oper)) {
                                    DB::run() -> query("UPDATE `users` SET `money`=`money`+? WHERE `login`=?", [$percent, $log]);
                                    DB::run() -> query("UPDATE `bank` SET `oper`=`oper`+1, `time`=? WHERE `user`=?", [SITETIME + 43200, $log]);
                                } else {
                                    DB::run() -> query("UPDATE `bank` SET `sum`=`sum`+?, `oper`=`oper`+1, `time`=? WHERE `user`=?", [$percent, SITETIME + 43200, $log]);
                                }
                                echo '<b>Продление счета успешно завершено, получено c процентов: '.moneys($percent).'</b><br /><br />';
                            } else {
                                show_error('Ошибка! Время получения процентов еще не наступило!');
                            }
                        } else {
                            show_error('Ошибка! У вас нет денег в банке или вклад слишком большой!');
                        }
                    } else {
                        show_error('Ошибка! У вас не открыт счет в банке!');
                    }
                } else {
                    show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/bank">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                        Операции                                        ##
        ############################################################################################
        case 'operacia':

            $gold = (int)$_POST['gold'];
            $oper = (int)$_POST['oper'];
            // ----------------------- Снятие со счета ----------------------------//
            if ($oper == 1) {
                show_title('Снятие со счета');

                if ($gold > 0) {
                    $querysum = DB::run() -> querySingle("SELECT `sum` FROM `bank` WHERE `user`=? LIMIT 1;", [$log]);
                    if (!empty($querysum)) {
                        if ($gold <= $querysum) {
                            DB::run() -> query("UPDATE `users` SET `money`=`money`+? WHERE `login`=?", [$gold, $log]);
                            DB::run() -> query("UPDATE `bank` SET `sum`=`sum`-?, `time`=? WHERE `user`=?", [$gold, SITETIME + 43200, $log]);

                            echo 'Сумма в размере <b>'.moneys($gold).'</b> успешно списана с вашего счета<br /><br />';
                        } else {
                            show_error('Ошибка! Вы не можете снять денег больше чем у вас на счете!');
                        }
                    } else {
                        show_error('Ошибка! Вы не можете снимать деньги, так как у вас не открыт счет!');
                    }
                } else {
                    show_error('Ошибка! Необходимо ввести сумму для снятия денег!');
                }
            }
            // -------------------------- Пополение счета --------------------------------//
            if ($oper == 2) {
                show_title('Пополнение счета');

                if ($gold > 0) {
                    if ($gold <= $udata['money']) {
                        DB::run() -> query("UPDATE `users` SET `money`=`money`-? WHERE `login`=?", [$gold, $log]);

                        $querybank = DB::run() -> querySingle("SELECT `id` FROM `bank` WHERE `user`=? LIMIT 1;", [$log]);
                        if (!empty($querybank)) {
                            DB::run() -> query("UPDATE `bank` SET `sum`=`sum`+?, `time`=? WHERE `user`=?", [$gold, SITETIME + 43200, $log]);
                        } else {
                            DB::run() -> query("INSERT INTO `bank` (`user`, `sum`, `time`) VALUES (?, ?, ?);", [$log, $gold, SITETIME + 43200]);
                        }

                        echo 'Сумма в размере <b>'.moneys($gold).'</b> успешно зачислена на ваш счет<br />';
                        echo 'Получить проценты с вклада вы сможете не ранее чем через 12 часов<br /><br />';
                    } else {
                        show_error('Недостаточное количество денег, у вас нет данной суммы на руках');
                    }
                } else {
                    show_error('Ошибка! Необходимо ввести сумму для пополнения счета!');
                }
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/bank">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы совершать операции, необходимо');
}

echo '<i class="fa fa-bar-chart"></i> <a href="/games/livebank">Статистика вкладов</a><br />';
echo '<i class="fa fa-money"></i> <a href="/games/credit">Выдача кредитов</a><br />';
echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
