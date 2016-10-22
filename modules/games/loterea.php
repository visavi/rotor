<?php
App::view($config['themes'].'/index');
$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Лотерея');

$rand = mt_rand(1, 100);
$newtime = date("d", SITETIME);

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $datalot = DB::run() -> queryFetch("SELECT * FROM `lotinfo` WHERE `lot_id`=?;", array(1));

            if ($newtime != $datalot['lot_date']) {
                $querywin = DB::run() -> query("SELECT `lot_user` FROM `lotusers` WHERE `lot_num`=?;", array($datalot['lot_newnum']));
                $arrwinners = $querywin -> fetchAll(PDO::FETCH_COLUMN);

                $winusers = '';
                $jackpot = (empty($datalot['lot_sum'])) ? $config['jackpot'] : $datalot['lot_sum'];
                $oldnum = (empty($datalot['lot_newnum'])) ? 0 : $datalot['lot_newnum'];
                $wincount = count($arrwinners);

                if ($wincount > 0) {
                    $winmoneys = round($datalot['lot_sum'] / $wincount);

                    foreach ($arrwinners as $winuz) {
                        if (check_user($winuz)) {
                            $textpriv = 'Поздравляем! Вы сорвали Джек-пот в лотерее и выиграли '.moneys($winmoneys);
                            DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($winuz, $config['nickname'], $textpriv, SITETIME));

                            DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1, `users_money`=`users_money`+? WHERE `users_login`=?", array($winmoneys, $winuz));
                        }
                    }

                    $winusers = implode(',', $arrwinners);
                    $jackpot = $config['jackpot'];
                }

                DB::run() -> query("REPLACE INTO `lotinfo` (`lot_id`, `lot_date`, `lot_sum`, `lot_newnum`, `lot_oldnum`, `lot_winners`) VALUES (?, ?, ?, ?, ?, ?);", array(1, $newtime, $jackpot, $rand, $oldnum, $winusers));
                DB::run() -> query("TRUNCATE `lotusers`;");
            }

            $total = DB::run() -> querySingle("SELECT count(*) FROM `lotusers`;");
            $datalot = DB::run() -> queryFetch("SELECT * FROM `lotinfo` WHERE `lot_id`=?;", array(1));

            echo 'Участвуй в лотерее! С каждым разом джек-пот растет<br />';
            echo 'Стань счастливым обладателем заветной суммы!<br /><br />';

            echo 'Джек-пот составляет <b><span style="color:#ff0000">'.moneys($datalot['lot_sum']).'</span></b><br /><br />';

            if (!empty($datalot['lot_oldnum'])) {
                echo 'Выигрышное число прошлого тура: <b>'.$datalot['lot_oldnum'].'</b><br />';

                if (!empty($datalot['lot_winners'])) {
                    $winners = explode (',', $datalot['lot_winners']);
                    echo 'Победители: ';
                    foreach ($winners as $wkey => $wval) {
                        if ($wkey == 0) {
                            $comma = '';
                        } else {
                            $comma = ', ';
                        }
                        echo $comma.' '.profile($wval);
                    }
                } else {
                    echo 'Джек-пот не выиграл никто!';
                }

                echo '<br /><br />';
            }

            echo 'Введите число от 1 до 100 включительно';

            echo '<div class="form">';
            echo '<form action="/games/loterea?act=bilet" method="post">';
            echo '<input type="text" name="bilet" />';
            echo '<input type="submit" value="Купить билет" /></form></div><br />';

            echo 'В этом туре участвуют: '.$total.'<br />';
            echo 'Cтоимость билета '.moneys(50).'<br />';
            echo 'В наличии: '.moneys($udata['users_money']).'<br /><br />';

            echo '<img src="/images/img/users.gif" alt="image" /> <a href="/games/loterea?act=show">Участники</a><br />';
        break;

        ############################################################################################
        ##                                    Покупка билета                                      ##
        ############################################################################################
        case "bilet":

            $bilet = abs(intval($_POST['bilet']));

            if ($bilet > 0 && $bilet <= 100) {
                if ($udata['users_money'] >= 50) {
                    $querysum = DB::run() -> querySingle("SELECT `lot_id` FROM `lotusers` WHERE `lot_user`=? LIMIT 1;", array($log));
                    if (empty($querysum)) {
                        DB::run() -> query("UPDATE `lotinfo` SET `lot_sum`=`lot_sum`+50 WHERE `lot_id`=?;", array(1));
                        DB::run() -> query("INSERT INTO `lotusers` (`lot_user`, `lot_num`, `lot_time`) VALUES (?, ?, ?);", array($log, $bilet, SITETIME));
                        DB::run() -> query("UPDATE users SET `users_money`=`users_money`-50 WHERE `users_login`=?", array($log));

                        echo '<b>Билет успешно приобретен!</b><br />';
                        echo 'Результат розыгрыша станет известным после полуночи!<br /><br />';
                    } else {
                        show_error('Вы уже купили билет! Нельзя покупать дважды!');
                    }
                } else {
                    show_error('Вы не можете купить билет, т.к. на вашем счету недостаточно средств!');
                }
            } else {
                show_error('Неверный ввод данных! Введите число от 1 до 100 включительно!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/loterea">Вернуться</a><br />';
            echo '<img src="/images/img/users.gif" alt="image" /> <a href="/games/loterea?act=show">Участники</a><br />';
        break;

        ############################################################################################
        ##                                   Просмотр участников                                  ##
        ############################################################################################
        case "show":
            show_title('Список участников купивших билеты');

            $queryusers = DB::run() -> query("SELECT * FROM `lotusers` ORDER BY `lot_time` DESC;");
            $lotusers = $queryusers -> fetchAll();

            $total = count($lotusers);

            if ($total > 0) {
                foreach ($lotusers as $key => $data) {
                    echo ($key + 1).'. ';
                    echo '<b>'.user_gender($data['lot_user']).' '.profile($data['lot_user']).'</b> ';
                    echo '(Ставка: <b>'.$data['lot_num'].'</b>) ('.date_fixed($data['lot_time']).')<br />';
                }

                echo '<br />Всего участников: <b>'.$total.'</b><br /><br />';
            } else {
                show_error('Еще нет ни одного участника!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/loterea">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы учавствовать в лотерее, необходимо');
}

echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
