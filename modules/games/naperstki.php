<?php
#-----------------------------------------------------#
#          ********* ROTORCMS *********               #
#              Made by  :  VANTUZ                     #
#               E-mail  :  visavi.net@mail.ru         #
#                 Site  :  http://pizdec.ru           #
#             WAP-Site  :  http://visavi.net          #
#                  ICQ  :  36-44-66                   #
#  Вы не имеете право вносить изменения в код скрипта #
#        для его дальнейшего распространения          #
#-----------------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

$rand = mt_rand(100, 999);

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Наперстки');

if (is_user()) {
    switch ($act):
    # ###########################################################################################
    # #                                    Главная страница                                    ##
    # ###########################################################################################
    case "index":

        echo '<img src="/images/naperstki/1.gif" alt="image" /><br /><br />';
        echo '<b><a href="/games/naperstki.php?act=choice">Играть</a></b><br /><br />';
        echo 'В наличии: ' . moneys($udata['users_money']) . '<br /><br />';
        echo '<img src="/images/img/faq.gif" alt="image" /> <a href="/games/naperstki.php?act=faq">Правила</a><br />';
        break;
    # ###########################################################################################
    # #                                     Выбор наперстка                                    ##
    # ###########################################################################################
    case "choice":

        if (isset($_SESSION['naperstki'])) {
            unset($_SESSION['naperstki']);
        }

        echo '<a href="/games/naperstki.php?act=go&amp;thimble=1&amp;rand=' . $rand . '"><img src="/images/naperstki/2.gif" alt="image" /></a> ';
        echo '<a href="/games/naperstki.php?act=go&amp;thimble=2&amp;rand=' . $rand . '"><img src="/images/naperstki/2.gif" alt="image" /></a> ';
        echo '<a href="/games/naperstki.php?act=go&amp;thimble=3&amp;rand=' . $rand . '"><img src="/images/naperstki/2.gif" alt="image" /></a><br /><br />';

        echo 'Выберите наперсток в котором может находится шарик<br />';

        echo 'В наличии: ' . moneys($udata['users_money']) . '<br /><br />';

        echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/naperstki.php">Вернуться</a><br />';
        break;
    # ###########################################################################################
    # #                                        Результат                                       ##
    # ###########################################################################################
    case "go":

        $thimble = intval($_GET['thimble']);
        if (!isset($_SESSION['naperstki'])) {
            $_SESSION['naperstki'] = 0;
        }
        if ($udata['users_money'] >= 50) {
            if ($_SESSION['naperstki'] < 3) {
                $_SESSION['naperstki']++;

                $rand_thimble = mt_rand(1, 3);

                if ($rand_thimble == 1) {
                    echo '<img src="/images/naperstki/3.gif" alt="image" /> ';
                } else {
                    echo '<img src="/images/naperstki/2.gif" alt="image" /> ';
                }

                if ($rand_thimble == 2) {
                    echo '<img src="/images/naperstki/3.gif" alt="image" /> ';
                } else {
                    echo '<img src="/images/naperstki/2.gif" alt="image" /> ';
                }

                if ($rand_thimble == 3) {
                    echo '<img src="/images/naperstki/3.gif" alt="image" />';
                } else {
                    echo '<img src="/images/naperstki/2.gif" alt="image" />';
                }
                // ------------------------------ Выигрыш ----------------------------//
                if ($thimble == $rand_thimble) {
                    DB::run()->query("UPDATE users SET users_money=users_money+100 WHERE users_login=?", array($log));

                    echo '<br /><b>Вы выиграли!</b><br />';
                    // ------------------------------ Проигрыш ----------------------------//
                } else {
                    DB::run()->query("UPDATE users SET users_money=users_money-50 WHERE users_login=?", array($log));

                    echo '<br /><b>Вы проиграли!</b><br />';
                }
            } else {
                show_error('Необходимо выбрать один из наперстков');
            }

            echo '<br /><b><a href="/games/naperstki.php?act=choice&amp;rand=' . $rand . '">К выбору</a></b><br /><br />';

            $allmoney = DB::run()->querySingle("SELECT users_money FROM users WHERE users_login=?;", array($log));
            echo 'У вас в наличии: ' . moneys($allmoney) . '<br /><br />';

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/naperstki.php">Вернуться</a><br />';
        } else {
            show_error('Вы не можете играть, т.к. на вашем счету недостаточно средств');
        }
        break;
    # ###########################################################################################
    # #                                     Описание игры                                      ##
    # ###########################################################################################
    case "faq":

        echo 'Для участия в игре нажмите "Играть"<br />';
        echo 'За каждый проигрыш у вас будут списывать по ' . moneys(50) . '<br />';
        echo 'За каждый выигрыш вы получите ' . moneys(100) . '<br />';
        echo 'Шанс банкира на выигрыш немного больше, чем у вас<br />';
        echo 'Итак дерзайте!<br /><br />';

        echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/naperstki.php">Вернуться</a><br />';
        break;

    default:
        header("location: /games/naperstki.php");
        exit;
        endswitch;
    } else {
    show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games/">Развлечения</a><br />';

include_once ('../themes/footer.php');
?>
