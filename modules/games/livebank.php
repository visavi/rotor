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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$uz = (isset($_REQUEST['uz'])) ? check($_REQUEST['uz']) : '';

show_title('Статистика вкладов');

switch ($act):
############################################################################################
##                                      Вывод вкладов                                     ##
############################################################################################
	case 'index':

		$total = DB::run() -> querySingle("SELECT count(*) FROM `bank`;");

		if ($total > 0) {
			if ($start >= $total) {
				$start = 0;
			}

			$queryvklad = DB::run() -> query("SELECT * FROM `bank` ORDER BY `bank_sum` DESC, `bank_user` ASC LIMIT ".$start.", ".$config['vkladlist'].";");

			$i = 0;
			while ($data = $queryvklad -> fetch()) {
				++$i;

				echo '<div class="b">'.($start + $i).'. '.user_gender($data['bank_user']).' ';

				if ($uz == $data['bank_user']) {
					echo '<b><big>'.profile($data['bank_user'], '#ff0000').'</big></b> ('.moneys($data['bank_sum']).')</div>';
				} else {
					echo '<b>'.profile($data['bank_user']).'</b> ('.moneys($data['bank_sum']).')</div>';
				}

				echo '<div>Начислений: '.$data['bank_oper'].'<br />';
				echo 'Посл. операция: '.date_fixed($data['bank_time']).'</div>';
			}

			page_strnavigation('livebank.php?', $config['vkladlist'], $start, $total);

			echo '<div class="form">';
			echo '<b>Поиск пользователя:</b><br />';
			echo '<form action="livebank.php?act=search&amp;start='.$start.'" method="post">';
			echo '<input type="text" name="uz" value="'.$log.'" />';
			echo '<input type="submit" value="Искать" /></form></div><br />';

			echo 'Всего вкладчиков: <b>'.$total.'</b><br /><br />';
		} else {
			show_error('Вкладов еще нет!');
		}
	break;

	############################################################################################
	##                                  Поиск пользователя                                    ##
	############################################################################################
	case 'search':

		if (!empty($uz)) {
			$queryuser = DB::run() -> querySingle("SELECT `users_login` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array(strtolower($uz), utf_lower($uz)));

			if (!empty($queryuser)) {
				$queryrating = DB::run() -> query("SELECT `bank_user` FROM `bank` ORDER BY `bank_sum` DESC, `bank_user` ASC;");
				$ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

				foreach ($ratusers as $key => $ratval) {
					if ($queryuser == $ratval) {
						$rat = $key + 1;
					}
				}

				if (!empty($rat)) {
					$page = floor(($rat - 1) / $config['vkladlist']) * $config['vkladlist'];

					notice('Позиция в рейтинге: '.$rat);
					redirect("livebank.php?start=$page&uz=$queryuser");
				} else {
					show_error('Пользователь с данным логином не найден!');
				}
			} else {
				show_error('Пользователь с данным логином не зарегистрирован!');
			}
		} else {
			show_error('Ошибка! Вы не ввели логин или ник пользователя');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="/games/livebank.php?start='.$start.'">Вернуться</a><br />';
	break;

default:
	redirect("livebank.php");
endswitch;

echo '<img src="/images/img/money.gif" alt="image" /> <a href="/games/bank.php">В банк</a><br />';
echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games/">Развлечения</a><br />';

include_once ('../themes/footer.php');
?>
