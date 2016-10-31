<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Выдача кредитов');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo 'В наличии: '.moneys($udata['users_money']).'<br />';
            echo 'В банке: '.moneys(user_bankmoney($log)).'<br /><br />';
            // --------------------- Вычисление если долг ---------------------------//
            if ($udata['users_sumcredit'] > 0) {
                echo '<b><span style="color:#ff0000">Сумма долга составляет: '.moneys($udata['users_sumcredit']).'</span></b><br />';

                if (SITETIME < $udata['users_timecredit']) {
                    echo 'До истечения срока кредита осталось <b>'.formattime($udata['users_timecredit'] - SITETIME).'</b><br /><br />';
                } else {
                    if ($udata['users_point'] >= 10) {
                        $delpoint = 10;
                    } else {
                        $delpoint = $udata['users_point'];
                    }

                    echo '<b><span style="color:#ff0000">Внимание! Время погашения кредита просрочено!</span></b><br />';
                    echo 'Начислен штраф в сумме 1%, у вас списано '.points($delpoint).'<br /><br />';

                    DB::run() -> query("UPDATE `users` SET `users_point`=`users_point`-?, `users_timecredit`=?, `users_sumcredit`=round(`users_sumcredit`*1.01) WHERE `users_login`=? LIMIT 1;", array($delpoint, SITETIME + 86400, $log));
                }
            }

            echo '<div class="form">';
            echo '<b>Операция:</b><br />';
            echo '<form action="credit?act=operacia" method="post">';
            echo '<input type="text" name="gold" /><br />';
            echo '<select name="oper">';
            echo '<option value="1">Взять кредит</option><option value="2">Погасить кредит</option>';
            echo '</select><br />';
            echo '<input type="submit" value="Продолжить" /></form></div><br />';

            echo'Минимальная сумма кредита '.moneys($config['minkredit']).'<br />';
            echo'Максимальная сумма кредита равна '.moneys($config['maxkredit']).'<br /><br />';

            echo '<b>Условия кредита</b><br />Независимо от суммы кредита банк берет '.(int)$config['percentkredit'].'% за операцию, кредит выдается на 5 дней<br />';
            echo 'Каждый просроченный день увеличивает сумму на 1% и у вас списывается '.points(10).'<br />';
            echo 'Кредит выдается пользователям у которых не менее '.points($config['creditpoint']).'<br /><br />';
        break;

        ############################################################################################
        ##                                     Операции                                           ##
        ############################################################################################
        case 'operacia':

            $gold = (int)$_POST['gold'];
            $oper = (int)$_POST['oper'];

            if ($oper == 1 || $oper == 2) {
                if ($gold >= $config['minkredit']) {
                    // -------------------------- Выдача кредитов -----------------------------//
                    if ($oper == 1) {
                        echo '<b>Получение кредита</b><br />';

                        if ($gold <= $config['maxkredit']) {
                            if ($udata['users_point'] >= $config['creditpoint']) {
                                if (empty($udata['users_sumcredit'])) {
                                    $sumcredit = $gold + (($gold * $config['percentkredit']) / 100);

                                    DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`+?, `users_sumcredit`=?, `users_timecredit`=? WHERE `users_login`=? LIMIT 1;", array($gold, $sumcredit, SITETIME + 432000, $log));

                                    $allmoney = DB::run() -> querySingle("SELECT `users_money` FROM `users` WHERE `users_login`=? LIMIT 1;", array($log));

                                    echo 'Cредства успешно перечислены вам в карман!<br />';
                                    echo 'Количество денег на руках: <b>'.moneys($allmoney).'</b><br /><br />';
                                } else {
                                    show_error('Ошибка! Вы не сможете получить кредит, возможно за вами еще числится долг!');
                                }
                            } else {
                                show_error('Ошибка! Ваш статус не позволяет вам получать кредит!');
                            }
                        } else {
                            show_error('Ошибка! Операции более чем с '.moneys($config['maxkredit']).' не проводятся!');
                        }
                    }
                    // -------------------------- Погашение кредитов -----------------------------//
                    if ($oper == 2) {
                        echo '<b>Погашение кредита</b><br />';

                        if ($udata['users_sumcredit'] > 0) {
                            if ($udata['users_sumcredit'] == $gold) {
                                if ($gold <= $udata['users_money']) {
                                    DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`-?, `users_sumcredit`=?, `users_timecredit`=? WHERE `users_login`=? LIMIT 1;", array($gold, 0, 0, $log));

                                    $allmoney = DB::run() -> querySingle("SELECT `users_money` FROM `users` WHERE `users_login`=? LIMIT 1;", array($log));

                                    echo 'Поздравляем! Кредит успешно погашен, благодорим за сотрудничество!<br />';
                                    echo 'Остаток денег на руках: <b>'.moneys($allmoney).'</b><br /><br />';
                                } else {
                                    show_error('Ошибка! у вас нехватает денег для погашения кредита!');
                                }
                            } else {
                                show_error('Ошибка! Необходимо внести точную сумму вашей задолженности!');
                            }
                        } else {
                            show_error('Ошибка! У вас нет задолженности перед банком, погашать кредит не нужно!');
                        }
                    }
                } else {
                    show_error('Операции менее чем с '.moneys($config['minkredit']).' не проводятся!');
                }
            } else {
                show_error('Ошибка! Не выбрана операция!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/credit">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы совершать операции, необходимо');
}

echo '<img src="/assets/img/images/money.gif" alt="image" /> <a href="/games/bank">Банк</a><br />';
echo '<img src="/assets/img/images/games.gif" alt="image" /> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
