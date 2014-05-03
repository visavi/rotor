<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['uz'])) {
	$uz = check($_GET['uz']);
} elseif (isset($_POST['uz'])) {
	$uz = check($_POST['uz']);
} else {
	$uz = "";
}

show_title('Список пользователей');

switch ($act):
############################################################################################
##                                    Вывод пользователей                                 ##
############################################################################################
	case 'index':

		$total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");

		if ($total > 0) {
			if ($start >= $total) {
				$start = 0;
			}

			$queryusers = DB::run() -> query("SELECT * FROM `users` ORDER BY `users_point` DESC, `users_login` ASC LIMIT ".$start.", ".$config['userlist'].";");

			$i = 0;
			while ($data = $queryusers -> fetch()) {
				++$i;

				echo '<div class="b"> ';
				echo '<div class="img">'.user_avatars($data['users_login']).'</div>';

				if ($uz == $data['users_login']) {
					echo ($start + $i).'. <b><big>'.profile($data['users_login'], '#ff0000').'</big></b> ';
				} else {
					echo ($start + $i).'. <b>'.profile($data['users_login']).'</b> ';
				}
				echo '('.points($data['users_point']).')<br />';
				echo user_title($data['users_login']).' '.user_online($data['users_login']);
				echo '</div>';

				echo '<div>';
				echo 'Форум: '.$data['users_allforum'].' | Гостевая: '.$data['users_allguest'].' | Коммент: '.$data['users_allcomments'].'<br />';
				echo 'Посещений: '.$data['users_visits'].'<br />';
				echo 'Деньги: '.user_money($data['users_login']).'<br />';
				echo 'Дата регистрации: '.date_fixed($data['users_joined'], 'j F Y').'</div>';
			}

			page_strnavigation('userlist.php?', $config['userlist'], $start, $total);

			echo '<div class="form">';
			echo '<b>Поиск пользователя:</b><br />';
			echo '<form action="userlist.php?act=search&amp;start='.$start.'" method="post">';
			echo '<input type="text" name="uz" value="'.$log.'" />';
			echo '<input type="submit" value="Искать" /></form></div><br />';

			echo 'Всего пользователей: <b>'.$total.'</b><br /><br />';
		} else {
			show_error('Пользователей еще нет!');
		}
	break;

	############################################################################################
	##                                  Поиск пользователя                                    ##
	############################################################################################
	case 'search':

		if (!empty($uz)) {
			$queryuser = DB::run() -> querySingle("SELECT `users_login` FROM `users` WHERE LOWER(`users_login`)=? OR LOWER(`users_nickname`)=? LIMIT 1;", array(strtolower($uz), utf_lower($uz)));

			if (!empty($queryuser)) {
				$queryrating = DB::run() -> query("SELECT `users_login` FROM `users` ORDER BY `users_point` DESC, `users_login` ASC;");
				$ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

				foreach ($ratusers as $key => $ratval) {
					if ($queryuser == $ratval) {
						$rat = $key + 1;
					}
				}

				if (!empty($rat)) {
					$page = floor(($rat - 1) / $config['userlist']) * $config['userlist'];

					$_SESSION['note'] = 'Позиция в рейтинге: '.$rat;
					redirect("userlist.php?start=$page&uz=$queryuser");
				} else {
					show_error('Пользователь с данным логином не найден!');
				}
			} else {
				show_error('Пользователь с данным логином не зарегистрирован!');
			}
		} else {
			show_error('Ошибка! Вы не ввели логин или ник пользователя');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="userlist.php?start='.$start.'">Вернуться</a><br />';
	break;

default:
	redirect("userlist.php");
endswitch;

echo '<img src="/images/img/users.gif" alt="image" /> <a href="onlinewho.php">Новички</a><br />';

include_once ('../themes/footer.php');
?>
