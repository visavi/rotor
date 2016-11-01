<?php
App::view($config['themes'].'/index');

$rand = mt_rand(100, 999);

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Кости');

if (is_user()) {
    switch ($act):
        # ###########################################################################################
        # #                                    Главная страница                                    ##
        # ###########################################################################################
        case "index":

            echo '<img src="/images/kosti/6.gif" alt="image" />  и <img src="/images/kosti/6.gif" alt="image" />.<br /><br />';

            echo '<b><a href="/games/kosti?act=go&amp;rand=' . $rand . '">Играть</a></b><br /><br />';

            echo 'У вас в наличии: ' . moneys($udata['users_money']) . '<br /><br />';

            echo '<i class="fa fa-question-circle"></i> <a href="/games/kosti?act=faq">Правила</a><br />';
            break;
        # ###########################################################################################
        # #                                       Результат                                        ##
        # ###########################################################################################
        case "go":

            if ($udata['users_money'] >= 5) {
                $num1 = mt_rand(2, 6);
                $num2 = mt_rand(1, 6);
                $num3 = mt_rand(1, 6);
                $num4 = mt_rand(1, 5);

                echo 'Ваши кости:<br />';
                echo '<img src="/images/kosti/' . $num3 . '.gif" alt="image" />  и <img src="/images/kosti/' . $num4 . '.gif" alt="image" />.<br /><br />';

                echo 'У банкира выпало:<br />';
                echo '<img src="/images/kosti/' . $num1 . '.gif" alt="image" />  и <img src="/images/kosti/' . $num2 . '.gif" alt="image" />.<br /><br />';

                $num_bank = $num1 + $num2;
                $num_user = $num3 + $num4;
                // ------------------------------ Выигрыш банкира ----------------------------//
                if ($num_bank > $num_user) {
                    DB::run()->query("UPDATE users SET users_money=users_money-5 WHERE users_login=?", array($log));

                    echo '<b>Банкир выиграл!</b>';
                }
                // ------------------------------ Выигрыш пользователя ----------------------------//
                if ($num_bank < $num_user) {
                    DB::run()->query("UPDATE users SET users_money=users_money+10 WHERE users_login=?", array($log));

                    echo '<b>Вы выиграли!</b>';
                }

                if ($num_bank == $num_user) {
                    echo '<b>Ничья!</b>';
                }

                echo '<br /><br />';
                echo '<b><a href="/games/kosti?act=go&amp;rand=' . $rand . '">Играть</a></b><br /><br />';

                $allmoney = DB::run()->querySingle("SELECT users_money FROM users WHERE users_login=?;", array($log));

                echo 'У вас в наличии: ' . moneys($allmoney) . '<br /><br />';
            } else {
                show_error('Вы не можете играть т.к. на вашем счету недостаточно средств!');
            }

            echo '<i class="fa fa-question-circle"></i> <a href="/games/kosti?act=faq">Правила</a><br />';
            break;
        # ###########################################################################################
        # #                                    Правила игры                                        ##
        # ###########################################################################################
        case "faq":

            echo 'Для участия в игре нажмите "Играть"<br />';
            echo 'За каждый проигрыш у вас будут списывать по ' . moneys(5) . '<br />';
            echo 'За каждый выигрыш вы получите ' . moneys(10) . '<br />';
            echo 'Шанс банкира на выигрыш немного больше, чем у вас<br />';
            echo 'Итак дерзайте!<br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/kosti">Вернуться</a><br />';
            break;

    endswitch;
} else {
    show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<i class="fa fa-money"></i> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
