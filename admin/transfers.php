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

$config['listtransfers'] = 10;
$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

if (is_admin(array(101, 102, 103))) {
	show_title('Денежные операции');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `transfers`;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$querytrans = DB::run() -> query("SELECT * FROM `transfers` ORDER BY `trans_time` DESC LIMIT ".$start.", ".$config['listtransfers'].";");

				while ($data = $querytrans -> fetch()) {
					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['trans_user']).'</div>';
					echo '<b>'.profile($data['trans_user']).'</b> '.user_online($data['trans_user']).' ';

					echo '<small>('.date_fixed($data['trans_time']).')</small><br />';

					echo '<a href="transfers.php?act=view&amp;uz='.$data['trans_user'].'">Все переводы</a></div>';

					echo '<div>';
					echo 'Кому: '.profile($data['trans_login']).'<br />';
					echo 'Сумма: '.moneys($data['trans_summ']).'<br />';
					echo 'Комментарий: '.$data['trans_text'].'<br />';
					echo '</div>';
				}

				page_strnavigation('transfers.php?', $config['listtransfers'], $start, $total);

				echo '<div class="form">';
				echo '<b>Поиск по пользователю:</b><br />';
				echo '<form action="transfers.php?act=view" method="get">';
				echo '<input type="hidden" name="act" value="view" />';
				echo '<input type="text" name="uz" />';
				echo '<input type="submit" value="Искать" /></form></div><br />';

				echo 'Всего операций: <b>'.$total.'</b><br /><br />';

			} else {
				show_error('Истории операций еще нет!');
			}
		break;

		############################################################################################
		##                                Просмотр по пользователям                               ##
		############################################################################################
		case 'view':

			$uz = (isset($_GET['uz'])) ? check($_GET['uz']) : '';

			if (user($uz)) {

				$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `transfers` WHERE `trans_user`=?;", array($uz));

				if ($total > 0) {
					if ($start >= $total) {
						$start = 0;
					}

					$queryhist = DB::run() -> query("SELECT * FROM `transfers` WHERE `trans_user`=? ORDER BY `trans_time` DESC LIMIT ".$start.", ".$config['listtransfers'].";", array($uz));

					while ($data = $queryhist -> fetch()) {
						echo '<div class="b">';
						echo '<div class="img">'.user_avatars($data['trans_user']).'</div>';
						echo '<b>'.profile($data['trans_user']).'</b> '.user_online($data['trans_user']).' ';

						echo '<small>('.date_fixed($data['trans_time']).')</small>';
						echo '</div>';

						echo '<div>';
						echo 'Кому: '.profile($data['trans_login']).'<br />';
						echo 'Сумма: '.moneys($data['trans_summ']).'<br />';
						echo 'Комментарий: '.$data['trans_text'].'<br />';
						echo '</div>';
					}

					page_strnavigation('transfers.php?act=view&amp;uz='.$uz.'&amp;', $config['listtransfers'], $start, $total);

					echo 'Всего операций: <b>'.$total.'</b><br /><br />';

				} else {
					show_error('Истории операций еще нет!');
				}
			} else {
				show_error('Ошибка! Данный пользователь не найден!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="transfers.php">Вернуться</a><br />';
		break;

	default:
		redirect("transfers.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
