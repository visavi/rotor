<?php
App::view(App::setting('themes').'/index');

$randgame = mt_rand(100, 999);

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('21 (Очко)');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $ochkostavka = App::setting('ochkostavka');

            echo 'В наличии: '.moneys(App::user('money')).'<br /><br />';

            if (empty($_SESSION['stavka'])) {
                if (App::user('money') > 0) {
                    if (App::user('money') < App::setting('ochkostavka')) {
                        $ochkostavka = App::user('money');
                    }

                    echo '<div class="form">';
                    echo 'Ваша ставка (1 - '.App::setting('ochkostavka').'):<br />';
                    echo'<form action="21?act=ini&amp;rand='.$randgame.'" method="post">';
                    echo'<input name="mn" />';
                    echo'<input type="submit" value="Играть" /></form></div><br />';
                } else {
                    show_error('У вас нет денег для игры!');
                }

                echo 'Mаксимальная ставка - '.moneys($ochkostavka).'<br /><br />';
            } else {
                echo 'Cтавки сделаны, на кону: '.moneys($_SESSION['stavka'] * 2).'<br /><br />';
                echo '<b><a href="/games/21?act=game&amp;case=go&amp;rand='.$randgame.'">Вернитесь в игру</a></b><br /><br />';
            }

            echo '<i class="fa fa-question-circle"></i> <a href="/games/21?act=rules">Правила игры</a><br />';
        break;

        ############################################################################################
        ##                                    Проверка данных                                     ##
        ############################################################################################
        case 'ini':

            if (isset($_POST['mn'])) {
                $mn = (int)$_POST['mn'];
            } else {
                $mn = (int)$_GET['mn'];
            }

            if ($mn > 0) {
                if ($mn <= App::setting('ochkostavka')) {
                    if (App::user('money') >= $mn) {
                        if (empty($_SESSION['stavka'])) {
                            $_SESSION['stavka'] = $mn;

                            DB::run() -> query("UPDATE `users` SET `money`=`money`-? WHERE `login`=? LIMIT 1;", [$mn, App::getUsername()]);
                            save_money(60);

                            App::redirect("21?act=game&rand=$randgame");

                        } else {
                            show_error('Вы уже сделали ставку, вернитесь в игру!');
                        }
                    } else {
                        show_error('У вас недостаточно денег для подобной ставки!');
                    }
                } else {
                    show_error('Запрещено ставить больше чем максимальная ставка '.moneys(App::setting('ochkostavka')).'!');
                }
            } else {
                show_error('Вы не указали ставку, необходимо поставить от 1 до '.App::setting('ochkostavka').'!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/21">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                        Игра                                            ##
        ############################################################################################
        case 'game':

            if (isset($_GET['case'])) {
                $case = check($_GET['case']);
            } else {
                $case = "";
            }

            if (isset($_SESSION['stavka'])) {
                if (empty($case)) {
                    if (empty($_SESSION['arrcard'])) {
                        $_SESSION['arrcard'] = array_combine(range(1, 36), range(1, 36));
                    }
                    if (empty($_SESSION['cards'])) {
                        $_SESSION['cards'] = [];
                    }
                    if (empty($_SESSION['bankircards'])) {
                        $_SESSION['bankircards'] = [];
                    }
                    if (empty($_SESSION['uscore'])) {
                        $_SESSION['uscore'] = 0;
                        $_SESSION['bscore'] = 0;
                    }

                    $randcard = array_rand($_SESSION['arrcard']);
                    $_SESSION['cards'][] = $randcard;
                    $_SESSION['uscore'] += cards_score($randcard);
                    unset($_SESSION['arrcard'][$randcard]);

                    if ($_SESSION['bscore'] < 17) {
                        $randcard2 = array_rand($_SESSION['arrcard']);
                        $_SESSION['bankircards'][] = $randcard2;
                        $_SESSION['bscore'] += cards_score($randcard2);
                        unset($_SESSION['arrcard'][$randcard2]);
                    }
                }

                echo 'В наличии: '.moneys(App::user('money')).'<br />';

                echo '<br /><b>Ваши карты:</b><br />';

                foreach($_SESSION['cards'] as $value) {
                    echo '<img src="/assets/img/cards/'.$value.'.gif" alt="" /> ';
                }

                echo '<br />'.cards_points($_SESSION['uscore']).'<br /><br />';

                if ($case == 'end') {
                    while ($_SESSION['bscore'] < 17) {
                        $randcard3 = array_rand($_SESSION['arrcard']);
                        $_SESSION['bankircards'][] = $randcard3;
                        $_SESSION['bscore'] += cards_score($randcard3);
                        unset($_SESSION['arrcard'][$randcard3]);
                    }

                    if ($_SESSION['uscore'] > $_SESSION['bscore']) {
                        $win = 1;
                    }
                    if ($_SESSION['uscore'] < $_SESSION['bscore']) {
                        $win = 2;
                    }
                    if ($_SESSION['uscore'] == $_SESSION['bscore']) {
                        $win = 0;
                    }
                    if ($_SESSION['bscore'] > 21) {
                        $win = 1;
                    }
                }

                if ($_SESSION['uscore'] > 21 && count($_SESSION['cards']) != 2) {
                    echo '<b><span style="color:#ff0000">У вас перебор!</span></b> - ';
                    $win = 2;
                    $bust = 1;
                }

                if (empty($bust)) {
                    if ($_SESSION['uscore'] == 22 && count($_SESSION['cards']) == 2) {
                        echo '<b><span style="color:#00cc00">У вас 2 туза!</span></b> - ';
                        $win = 1;
                    }
                    if ($_SESSION['bscore'] == 22 && count($_SESSION['bankircards']) == 2) {
                        echo '<b><span style="color:#ff0000">У банкира 2 туза!</span></b> - ';
                        $win = 2;
                    }
                    if ($_SESSION['uscore'] == 21) {
                        echo '<b><span style="color:#00cc00">У вас очко!</span></b> - ';
                        $win = 1;
                    }
                    if ($_SESSION['bscore'] == 21) {
                        echo '<b><span style="color:#ff0000">У банкира очко!</span></b> - ';
                        $win = 2;
                    }
                    if (($_SESSION['uscore'] == 21 && $_SESSION['bscore'] == 21) || ($_SESSION['uscore'] == 22 && $_SESSION['bscore'] == 22)) {
                        $win = 0;
                    }
                }

                if (isset($win)) {
                    if (empty($win)) {
                        DB::run() -> query("UPDATE `users` SET `money`=`money`+? WHERE `login`=? LIMIT 1;", [$_SESSION['stavka'], App::getUsername()]);
                        save_money(60);
                        echo '<b><span style="color:#ffa500">Ничья</span></b><br />';
                        echo 'Ставка в размере '.moneys($_SESSION['stavka']).' возвращена вам на счет<br /><br />';
                    } elseif ($win == 1) {
                        DB::run() -> query("UPDATE `users` SET `money`=`money`+? WHERE `login`=? LIMIT 1;", [$_SESSION['stavka'] * 2, App::getUsername()]);
                        save_money(60);

                        echo '<b><span style="color:#00cc00">Вы выиграли</span></b><br />';
                        echo 'Ставка в размере '.moneys($_SESSION['stavka'] * 2).' отправлена вам на счет<br /><br />';
                    } else {
                        echo '<b><span style="color:#ff0000">Вы проиграли</span></b><br />';
                        echo 'Ставка в размере '.moneys($_SESSION['stavka'] * 2).' отправлена в банк<br /><br />';
                    }

                    if (empty($bust)) {
                        echo '<b>Карты банкира:</b><br />';

                        foreach($_SESSION['bankircards'] as $bvalue) {
                            echo '<img src="/assets/img/cards/'.$bvalue.'.gif" alt="" /> ';
                        }

                        echo '<br />'.cards_points($_SESSION['bscore']).'<br /><br />';
                    }

                    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/games/21?act=ini&amp;rand='.$randgame.'&amp;mn='.$_SESSION['stavka'].'">Повторить ставку</a><br />';

                    unset($_SESSION['arrcard']);
                    unset($_SESSION['cards']);
                    unset($_SESSION['bankircards']);
                    unset($_SESSION['stavka']);
                    unset($_SESSION['uscore']);
                    unset($_SESSION['bscore']);
                } else {
                    echo 'На кону: '.moneys($_SESSION['stavka'] * 2).'<br /><br />';
                    echo '<b><a href="/games/21?act=game&amp;rand='.$randgame.'">Взять карту</a></b> или ';
                    echo '<b><a href="/games/21?act=game&amp;case=end&amp;rand='.$randgame.'">Открыться</a></b><br /><br />';
                }
            } else {
                show_error('Вы не установили размер ставки, необходимо сделать ставку!');
            }

            if (empty($_SESSION['stavka'])) {
                echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/21">Новая ставка</a><br />';
            }
            break;
        // -------------------------- Правила игры -------------------------------------//
        case 'rules':

            echo 'Для участия в игре сделайте ставку и нажмите <b>Играть</b><br />';
            echo 'Ваша ставка будет получена Банкиром и он начнет сдавать Вам карты.<br />';
            echo 'В игре участвуют двое - Вы и Банкир, на кону - двойная ставка (Ваша ставка и ставка Банкира). Взяв карты, Вы подсчитываете суммарное количество их очков.<br /><br />';

            echo '<b>Очки считаются следующим образом:</b><br />';
            echo '<img src="/assets/img/cards/2.gif" alt="" /> шестерка - 6 очков<br />';
            echo '<img src="/assets/img/cards/6.gif" alt="" /> семерка - 7 очков<br />';
            echo '<img src="/assets/img/cards/10.gif" alt="" /> восьмерка - 8 очков<br />';
            echo '<img src="/assets/img/cards/14.gif" alt="" /> девятка - 9 очков<br />';
            echo '<img src="/assets/img/cards/18.gif" alt="" /> десятка - 10 очков<br />';
            echo '<img src="/assets/img/cards/22.gif" alt="" /> валет - 2 очков<br />';
            echo '<img src="/assets/img/cards/26.gif" alt="" /> дама - 3 очков<br />';
            echo '<img src="/assets/img/cards/30.gif" alt="" /> король - 4 очков<br />';
            echo '<img src="/assets/img/cards/34.gif" alt="" /> туз - 11 очков.<br /><br />';

            echo 'Сумма очков не зависит от масти карт.<br />';
            echo 'Для взятия очередной карты нужно нажать кнопку <b>Взять карту</b>.<br />';
            echo 'Если сумма Ваших очков больше 21, то Вы проиграли - перебор, исключение - 2 туза(22 очка).<br />';
            echo 'Очко(21) главнее чем 2 туза(22)!<br /><br />';

            echo 'Взяв необходимое количество карт, Вы нажимаете кнопку <b>Открыться</b>, и Банкир открывает свои карты (если Вы набираете 20, 21 или 22 (2 туза) очка то Банкир открывается автоматически). Выигрывает тот, у кого больше очков. Победитель забирает кон размером в 2 ставки. При равном количестве очков выигрывает банкир!<br /><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/21">В игру</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br />';

App::view(App::setting('themes').'/foot');
