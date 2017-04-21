<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Выдача кредитов');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo 'В наличии: '.moneys(App::user('money')).'<br />';
            echo 'В банке: '.moneys(user_bankmoney(App::getUsername())).'<br /><br />';
            // --------------------- Вычисление если долг ---------------------------//
            if (App::user('sumcredit') > 0) {
                echo '<b><span style="color:#ff0000">Сумма долга составляет: '.moneys(App::user('sumcredit')).'</span></b><br />';

                if (SITETIME < App::user('timecredit')) {
                    echo 'До истечения срока кредита осталось <b>'.formattime(App::user('timecredit') - SITETIME).'</b><br /><br />';
                } else {
                    if (App::user('point') >= 10) {
                        $delpoint = 10;
                    } else {
                        $delpoint = App::user('point');
                    }

                    echo '<b><span style="color:#ff0000">Внимание! Время погашения кредита просрочено!</span></b><br />';
                    echo 'Начислен штраф в сумме 1%, у вас списано '.points($delpoint).'<br /><br />';

                    DB::run() -> query("UPDATE `users` SET `point`=`point`-?, `timecredit`=?, `sumcredit`=round(`sumcredit`*1.01) WHERE `login`=? LIMIT 1;", [$delpoint, SITETIME + 86400, App::getUsername()]);
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

            echo'Минимальная сумма кредита '.moneys(App::setting('minkredit')).'<br />';
            echo'Максимальная сумма кредита равна '.moneys(App::setting('maxkredit')).'<br /><br />';

            echo '<b>Условия кредита</b><br />Независимо от суммы кредита банк берет '.(int)App::setting('percentkredit').'% за операцию, кредит выдается на 5 дней<br />';
            echo 'Каждый просроченный день увеличивает сумму на 1% и у вас списывается '.points(10).'<br />';
            echo 'Кредит выдается пользователям у которых не менее '.points(App::setting('creditpoint')).'<br /><br />';
        break;

        ############################################################################################
        ##                                     Операции                                           ##
        ############################################################################################
        case 'operacia':

            $gold = (int)$_POST['gold'];
            $oper = (int)$_POST['oper'];

            if ($oper == 1 || $oper == 2) {
                if ($gold >= App::setting('minkredit')) {
                    // -------------------------- Выдача кредитов -----------------------------//
                    if ($oper == 1) {
                        echo '<b>Получение кредита</b><br />';

                        if ($gold <= App::setting('maxkredit')) {
                            if (App::user('point') >= App::setting('creditpoint')) {
                                if (empty(App::user('sumcredit'))) {
                                    $sumcredit = $gold + (($gold * App::setting('percentkredit')) / 100);

                                    DB::run() -> query("UPDATE `users` SET `money`=`money`+?, `sumcredit`=?, `timecredit`=? WHERE `login`=? LIMIT 1;", [$gold, $sumcredit, SITETIME + 432000, App::getUsername()]);

                                    $allmoney = DB::run() -> querySingle("SELECT `money` FROM `users` WHERE `login`=? LIMIT 1;", [App::getUsername()]);

                                    echo 'Cредства успешно перечислены вам в карман!<br />';
                                    echo 'Количество денег на руках: <b>'.moneys($allmoney).'</b><br /><br />';
                                } else {
                                    show_error('Ошибка! Вы не сможете получить кредит, возможно за вами еще числится долг!');
                                }
                            } else {
                                show_error('Ошибка! Ваш статус не позволяет вам получать кредит!');
                            }
                        } else {
                            show_error('Ошибка! Операции более чем с '.moneys(App::setting('maxkredit')).' не проводятся!');
                        }
                    }
                    // -------------------------- Погашение кредитов -----------------------------//
                    if ($oper == 2) {
                        echo '<b>Погашение кредита</b><br />';

                        if (App::user('sumcredit') > 0) {
                            if (App::user('sumcredit') == $gold) {
                                if ($gold <= App::user('money')) {
                                    DB::run() -> query("UPDATE `users` SET `money`=`money`-?, `sumcredit`=?, `timecredit`=? WHERE `login`=? LIMIT 1;", [$gold, 0, 0, App::getUsername()]);

                                    $allmoney = DB::run() -> querySingle("SELECT `money` FROM `users` WHERE `login`=? LIMIT 1;", [App::getUsername()]);

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
                    show_error('Операции менее чем с '.moneys(App::setting('minkredit')).' не проводятся!');
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

echo '<i class="fa fa-money"></i> <a href="/games/bank">Банк</a><br />';
echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br />';

App::view(App::setting('themes').'/foot');
