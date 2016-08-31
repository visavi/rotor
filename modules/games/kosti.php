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

show_title('Кости');

if (is_user()) {
  switch ($act):
  # ###########################################################################################
  # #                                    Главная страница                                    ##
  # ###########################################################################################
  case "index":

    echo '<img src="/images/kosti/6.gif" alt="image" />  и <img src="/images/kosti/6.gif" alt="image" />.<br /><br />';

    echo '<b><a href="/games/kosti.php?act=go&amp;rand=' . $rand . '">Играть</a></b><br /><br />';

    echo 'У вас в наличии: ' . moneys($udata['users_money']) . '<br /><br />';

    echo '<img src="/images/img/faq.gif" alt="image" /> <a href="/games/kosti.php?act=faq">Правила</a><br />';
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
      echo '<b><a href="/games/kosti.php?act=go&amp;rand=' . $rand . '">Играть</a></b><br /><br />';

      $allmoney = DB::run()->querySingle("SELECT users_money FROM users WHERE users_login=?;", array($log));

      echo 'У вас в наличии: ' . moneys($allmoney) . '<br /><br />';
    } else {
      show_error('Вы не можете играть т.к. на вашем счету недостаточно средств!');
    }

    echo '<img src="/images/img/faq.gif" alt="image" /> <a href="/games/kosti.php?act=faq">Правила</a><br />';
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

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/kosti.php">Вернуться</a><br />';
    break;

  default:
    header("location: /games/kosti.php");
    exit;
    endswitch;
  } else {
  show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games/">Развлечения</a><br />';

include_once ('../themes/footer.php');

?>
