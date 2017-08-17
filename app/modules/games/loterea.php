<?php
App::view(Setting::get('themes').'/index');
$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Лотерея');

$rand = mt_rand(1, 100);
$newtime = date("d", SITETIME);

if (is_user()) {
    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $datalot = DB::run() -> queryFetch("SELECT * FROM `lotinfo` WHERE `id`=?;", [1]);

            if ($newtime != $datalot['date']) {
                $querywin = DB::run() -> query("SELECT `user` FROM `lotusers` WHERE `num`=?;", [$datalot['newnum']]);
                $arrwinners = $querywin -> fetchAll(PDO::FETCH_COLUMN);

                $winusers = '';
                $jackpot = (empty($datalot['sum'])) ? Setting::get('jackpot') : $datalot['sum'];
                $oldnum = (empty($datalot['newnum'])) ? 0 : $datalot['newnum'];
                $wincount = count($arrwinners);

                if ($wincount > 0) {
                    $winmoneys = round($datalot['sum'] / $wincount);

                    foreach ($arrwinners as $winuz) {
                        if (check_user($winuz)) {
                            $textpriv = 'Поздравляем! Вы сорвали Джек-пот в лотерее и выиграли '.moneys($winmoneys);
                            DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$winuz, Setting::get('nickname'), $textpriv, SITETIME]);

                            DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1, `money`=`money`+? WHERE `login`=?", [$winmoneys, $winuz]);
                        }
                    }

                    $winusers = implode(',', $arrwinners);
                    $jackpot = Setting::get('jackpot');
                }

                DB::run() -> query("REPLACE INTO `lotinfo` (`id`, `date`, `sum`, `newnum`, `oldnum`, `winners`) VALUES (?, ?, ?, ?, ?, ?);", [1, $newtime, $jackpot, $rand, $oldnum, $winusers]);
                DB::run() -> query("TRUNCATE `lotusers`;");
            }

            $total = DB::run() -> querySingle("SELECT count(*) FROM `lotusers`;");
            $datalot = DB::run() -> queryFetch("SELECT * FROM `lotinfo` WHERE `id`=?;", [1]);

            echo 'Участвуй в лотерее! С каждым разом джек-пот растет<br>';
            echo 'Стань счастливым обладателем заветной суммы!<br><br>';

            echo 'Джек-пот составляет <b><span style="color:#ff0000">'.moneys($datalot['sum']).'</span></b><br><br>';

            if (!empty($datalot['oldnum'])) {
                echo 'Выигрышное число прошлого тура: <b>'.$datalot['oldnum'].'</b><br>';

                if (!empty($datalot['winners'])) {
                    $winners = explode (',', $datalot['winners']);
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

                echo '<br><br>';
            }

            echo 'Введите число от 1 до 100 включительно';

            echo '<div class="form">';
            echo '<form action="/games/loterea?act=bilet" method="post">';
            echo '<input type="text" name="bilet">';
            echo '<input type="submit" value="Купить билет"></form></div><br>';

            echo 'В этом туре участвуют: '.$total.'<br>';
            echo 'Cтоимость билета '.moneys(50).'<br>';
            echo 'В наличии: '.moneys(App::user('money')).'<br><br>';

            echo '<i class="fa fa-users"></i> <a href="/games/loterea?act=show">Участники</a><br>';
        break;

        ############################################################################################
        ##                                    Покупка билета                                      ##
        ############################################################################################
        case "bilet":

            $bilet = abs(intval($_POST['bilet']));

            if ($bilet > 0 && $bilet <= 100) {
                if (App::user('money') >= 50) {
                    $querysum = DB::run() -> querySingle("SELECT `id` FROM `lotusers` WHERE `user`=? LIMIT 1;", [App::getUsername()]);
                    if (empty($querysum)) {
                        DB::run() -> query("UPDATE `lotinfo` SET `sum`=`sum`+50 WHERE `id`=?;", [1]);
                        DB::run() -> query("INSERT INTO `lotusers` (`user`, `num`, `time`) VALUES (?, ?, ?);", [App::getUsername(), $bilet, SITETIME]);
                        DB::run() -> query("UPDATE users SET `money`=`money`-50 WHERE `login`=?", [App::getUsername()]);

                        echo '<b>Билет успешно приобретен!</b><br>';
                        echo 'Результат розыгрыша станет известным после полуночи!<br><br>';
                    } else {
                        show_error('Вы уже купили билет! Нельзя покупать дважды!');
                    }
                } else {
                    show_error('Вы не можете купить билет, т.к. на вашем счету недостаточно средств!');
                }
            } else {
                show_error('Неверный ввод данных! Введите число от 1 до 100 включительно!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/loterea">Вернуться</a><br>';
            echo '<i class="fa fa-users"></i> <a href="/games/loterea?act=show">Участники</a><br>';
        break;

        ############################################################################################
        ##                                   Просмотр участников                                  ##
        ############################################################################################
        case "show":
            //show_title('Список участников купивших билеты');

            $queryusers = DB::run() -> query("SELECT * FROM `lotusers` ORDER BY `time` DESC;");
            $lotusers = $queryusers -> fetchAll();

            $total = count($lotusers);

            if ($total > 0) {
                foreach ($lotusers as $key => $data) {
                    echo ($key + 1).'. ';
                    echo '<b>'.user_gender($data['user']).' '.profile($data['user']).'</b> ';
                    echo '(Ставка: <b>'.$data['num'].'</b>) ('.date_fixed($data['time']).')<br>';
                }

                echo '<br>Всего участников: <b>'.$total.'</b><br><br>';
            } else {
                show_error('Еще нет ни одного участника!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/loterea">Вернуться</a><br>';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы учавствовать в лотерее, необходимо');
}

echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br>';

App::view(Setting::get('themes').'/foot');
