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

show_title('Исправительная');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":

			echo 'Если вы не злостный нарушитель, но по какой-то причине получили строгое нарушение и хотите от него избавиться - тогда вы попали по адресу.<br />';
			echo 'Здесь самое лучшее место, чтобы встать на путь исправления<br /><br />';
			echo 'Снять нарушение можно раз в месяц при условии, что с вашего последнего бана вы не нарушали правил и были добросовестным участником сайта<br />';
			echo 'Также вы должны будете выплатить банку штраф в размере '.moneys(100000).'<br />';
			echo 'Если с момента вашего последнего бана прошло менее месяца или у вас нет на руках суммы для штрафа, тогда строгое нарушение снять не удастся<br /><br />';
			echo 'Общее число строгих нарушений: <b>'.$udata['users_totalban'].'</b><br />';

			$daytime = round(((SITETIME - $udata['users_timelastban']) / 3600) / 24);

			if ($udata['users_timelastban'] > 0 && $udata['users_totalban'] > 0) {
				echo 'Суток прошедших с момента последнего нарушения: <b>'.$daytime.'</b><br />';
			} else {
				echo 'Дата последнего нарушения не указана<br />';
			}

			echo 'Денег на руках: <b>'.moneys($udata['users_money']).'</b><br /><br />';

			if ($udata['users_totalban'] > 0 && $daytime >= 30 && $udata['users_money'] >= 100000) {
				echo '<img src="/images/img/open.gif" alt="image" /> <b><a href="razban.php?act=go">Снять нарушение</a></b><br />';
				echo 'У вас имеется возможность снять нарушение<br /><br />';
			} else {
				echo '<b>Вы не можете снять нарушение</b><br />';
				echo 'Возможно у вас нет нарушений, не прошло еще 30 суток или недостаточная сумма на счете<br /><br />';
			}
		break;

		############################################################################################
		##                                   Снятие нарушений                                     ##
		############################################################################################
		case "go":

			$daytime = round(((SITETIME - $udata['users_timelastban']) / 3600) / 24);
			if ($udata['users_totalban'] > 0 && $daytime >= 30 && $udata['users_money'] >= 100000) {
				DB::run() -> query("UPDATE users SET users_timelastban=?, users_totalban=users_totalban-1, users_money=users_money-? WHERE users_login=?", array(SITETIME, 100000, $log));

				echo 'Нарушение успешно списано, с вашего счета списано <b>'.moneys(100000).'</b><br />';
				echo 'Следующее нарушение вы сможете снять не ранее чем через 30 суток<br /><br />';
			} else {
				echo '<b>Вы не можете снять нарушение</b><br />';
				echo 'Возможно у вас нет нарушений, не прошло еще 30 суток или недостаточная сумма на счете<br /><br />';
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="razban.php">Вернуться</a><br />';
		break;

	default:
		redirect("razban.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, чтобы снять нарушение, необходимо');
}

include_once ('../themes/footer.php');
?>
