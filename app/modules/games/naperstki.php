<?php
App::view(Setting::get('themes').'/index');

$rand = mt_rand(100, 999);

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Наперстки');

if (is_user()) {
    switch ($action):
    # ###########################################################################################
    # #                                    Главная страница                                    ##
    # ###########################################################################################
    case "index":

        echo '<img src="/assets/img/naperstki/1.gif" alt="image"><br><br>';
        echo '<b><a href="/games/naperstki?act=choice">Играть</a></b><br><br>';
        echo 'В наличии: ' . moneys(App::user('money')) . '<br><br>';
        echo '<i class="fa fa-question-circle"></i> <a href="/games/naperstki?act=faq">Правила</a><br>';
        break;
    # ###########################################################################################
    # #                                     Выбор наперстка                                    ##
    # ###########################################################################################
    case "choice":

        if (isset($_SESSION['naperstki'])) {
            unset($_SESSION['naperstki']);
        }

        echo '<a href="/games/naperstki?act=go&amp;thimble=1&amp;rand=' . $rand . '"><img src="/assets/img/naperstki/2.gif" alt="image"></a> ';
        echo '<a href="/games/naperstki?act=go&amp;thimble=2&amp;rand=' . $rand . '"><img src="/assets/img/naperstki/2.gif" alt="image"></a> ';
        echo '<a href="/games/naperstki?act=go&amp;thimble=3&amp;rand=' . $rand . '"><img src="/assets/img/naperstki/2.gif" alt="image"></a><br><br>';

        echo 'Выберите наперсток в котором может находится шарик<br>';

        echo 'В наличии: ' . moneys(App::user('money')) . '<br><br>';

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/naperstki">Вернуться</a><br>';
        break;
    # ###########################################################################################
    # #                                        Результат                                       ##
    # ###########################################################################################
    case "go":

        $thimble = intval($_GET['thimble']);
        if (!isset($_SESSION['naperstki'])) {
            $_SESSION['naperstki'] = 0;
        }
        if (App::user('money') >= 50) {
            if ($_SESSION['naperstki'] < 3) {
                $_SESSION['naperstki']++;

                $rand_thimble = mt_rand(1, 3);

                if ($rand_thimble == 1) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image"> ';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image"> ';
                }

                if ($rand_thimble == 2) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image"> ';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image"> ';
                }

                if ($rand_thimble == 3) {
                    echo '<img src="/assets/img/naperstki/3.gif" alt="image">';
                } else {
                    echo '<img src="/assets/img/naperstki/2.gif" alt="image">';
                }
                // ------------------------------ Выигрыш ----------------------------//
                if ($thimble == $rand_thimble) {
                    DB::run()->query("UPDATE users SET money=money+100 WHERE login=?", [App::getUsername()]);

                    echo '<br><b>Вы выиграли!</b><br>';
                    // ------------------------------ Проигрыш ----------------------------//
                } else {
                    DB::run()->query("UPDATE users SET money=money-50 WHERE login=?", [App::getUsername()]);

                    echo '<br><b>Вы проиграли!</b><br>';
                }
            } else {
                show_error('Необходимо выбрать один из наперстков');
            }

            echo '<br><b><a href="/games/naperstki?act=choice&amp;rand=' . $rand . '">К выбору</a></b><br><br>';

            $allmoney = DB::run()->querySingle("SELECT money FROM users WHERE login=?;", [App::getUsername()]);
            echo 'У вас в наличии: ' . moneys($allmoney) . '<br><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/naperstki">Вернуться</a><br>';
        } else {
            show_error('Вы не можете играть, т.к. на вашем счету недостаточно средств');
        }
        break;
    # ###########################################################################################
    # #                                     Описание игры                                      ##
    # ###########################################################################################
    case "faq":

        echo 'Для участия в игре нажмите "Играть"<br>';
        echo 'За каждый проигрыш у вас будут списывать по ' . moneys(50) . '<br>';
        echo 'За каждый выигрыш вы получите ' . moneys(100) . '<br>';
        echo 'Шанс банкира на выигрыш немного больше, чем у вас<br>';
        echo 'Итак дерзайте!<br><br>';

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/naperstki">Вернуться</a><br>';
        break;

        endswitch;
    } else {
    show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br>';

App::view(Setting::get('themes').'/foot');
