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

$config['listinvite'] = 20;
$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$used = (!empty($_GET['used'])) ? 1  : 0;

if (is_admin(array(101, 102, 103))) {
	show_title('Приглашения');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	if (empty($config['invite'])) {
		echo '<img src="/images/img/warning.gif" alt="image" /> <span style="color:#ff0000"><b>Внимание! Регистрация по приглашения выключена!</b></span><br /><br />';
	}

	if (empty($used)){
		echo '<b>Неиспользованные</b> / <a href="invitations.php?used=1">Использованные</a><hr />';
	} else {
		echo '<a href="invitations.php">Неиспользованные</a> / <b>Использованные</b><hr />';
	}

	$total = DB::run() -> querySingle("SELECT COUNT(*) FROM `invite` WHERE `used`=?;", array($used));

	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$invitations = DB::run() -> query("SELECT * FROM `invite` WHERE `used`=? ORDER BY `time` DESC LIMIT ".$start.", ".$config['listinvite'].";", array($used));

		echo '<form action="invitations.php?act=del&amp;used='.$used.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

		while ($data = $invitations -> fetch()) {

			echo '<div class="b"><input type="checkbox" name="del[]" value="'.$data['id'].'" /> <b>'.$data['key'].'</b></div>';
			echo '<div>Владелец: '.profile($data['user']).'<br />';

			if (!empty($data['invited'])) {
				echo 'Приглашенный: '.profile($data['invited']).'<br />';
			}
			echo 'Создан: '.date_fixed($data['time']).'<br />';
			echo '</div>';
		}

		echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

		page_strnavigation('invitations.php?used='.$used.'&amp;', $config['listinvite'], $start, $total);

		echo 'Всего ключей: <b>'.$total.'</b><br /><br />';

	} else {
		show_error('Приглашений еще нет!');
	}

	echo '<img src="/images/img/open.gif" alt="image" /> <a href="invitations.php?act=new">Создать ключи</a><br />';
	echo '<img src="/images/img/keys.gif" alt="image" /> <a href="invitations.php?act=list">Список ключей</a><br />';
break;

############################################################################################
##                                     Создание ключей                                    ##
############################################################################################
case 'new':

	echo '<b><big>Генерация новых ключей:</big></b><br />';
	echo '<div class="form">';
	echo '<form action="invitations.php?act=generate&amp;uid='.$_SESSION['token'].'" method="post">';
	echo '<select name="keys">';
	echo '<option value="1">1 ключ</option>';
	echo '<option value="2">2 ключа</option>';
	echo '<option value="3">3 ключа</option>';
	echo '<option value="5">5 ключей</option>';
	echo '<option value="10">10 ключей</option>';
	echo '<option value="20">20 ключей</option>';
	echo '<option value="50">50 ключей</option>';
	echo '</select>	';
	echo '<input type="submit" value="Генерировать" /></form></div><br />';

	echo '<b><big>Отправить ключ пользователю:</big></b><br />';
	echo '<div class="form">';
	echo '<form action="invitations.php?act=send&amp;uid='.$_SESSION['token'].'" method="post">';
	echo 'Логин пользователя:<br />';
	echo '<input type="text" name="user" /><br />';
	echo '<select name="keys">';
	echo '<option value="1">1 ключ</option>';
	echo '<option value="2">2 ключа</option>';
	echo '<option value="3">3 ключа</option>';
	echo '<option value="4">4 ключа</option>';
	echo '<option value="5">5 ключей</option>';
	echo '</select><br />';
	echo '<input type="submit" value="Отправить" /></form></div><br />';

	if (is_admin(array(101))){
		echo '<b><big>Рассылка ключей:</big></b><br />';
		echo '<div class="form">';
		echo 'Разослать ключи активным пользователям:<br />';
		echo '<form action="invitations.php?act=mailing&amp;uid='.$_SESSION['token'].'" method="post">';
		echo '<input type="submit" value="Разослать" /></form></div><br />';
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php">Вернуться</a><br />';
break;

############################################################################################
##                                       Список ключей                                    ##
############################################################################################
case 'list':
	$invitations = DB::run() -> query("SELECT `key` FROM `invite` WHERE `user`=? AND `used`=? ORDER BY `time` DESC;", array($log, 0));
	$invite = $invitations -> fetchAll(PDO::FETCH_COLUMN);
	$total = count($invite);

	if ($total > 0){
		echo 'Всего ваших ключей: '.$total.'<br />';
		echo '<textarea cols="25" rows="10">'.implode(', ', $invite).'</textarea><br /><br />';
	} else {
		show_error('Ошибка! Нет ваших пригласительных ключей!');
	}
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php">Вернуться</a><br />';
break;

############################################################################################
##                                Отправка ключей в приват                                ##
############################################################################################
case 'send':

	$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
	$keys = (isset($_POST['keys'])) ? abs(intval($_POST['keys'])) : 1;
	$user = (isset($_REQUEST['user'])) ? check($_REQUEST['user']) : '';

	if ($uid == $_SESSION['token']) {
		if (check_user($user)) {


			$dbr = DB::run() -> prepare("INSERT INTO `invite` (`key`, `user`, `time`) VALUES (?, ?, ?);");

			$listkeys = array();

			for($i = 0; $i < $keys; $i++) {
				$key = generate_password(rand(12, 15));
				$dbr -> execute($key, $user, SITETIME);
				$listkeys[] = $key;
			}

			$text = 'Вы получили пригласительные ключи в количестве '.count($listkeys).'шт.<br />Список ключей: '.implode(', ', $listkeys);
			send_private($user, $log, $text);

			notice('Ключи успешно отправлены!');
			redirect("invitations.php");


		} else {
			show_error('Ошибка! Не найден пользователь с заданным логином!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php?act=new">Вернуться</a><br />';
break;

############################################################################################
##                                Отправка ключей в приват                                ##
############################################################################################
case 'mailing':

	$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

	if ($uid == $_SESSION['token']) {
		if (is_admin(array(101))){

			$query = DB::run()->query("SELECT `users_login` FROM `users` WHERE `users_timelastlogin`>?;", array(SITETIME - (86400 * 7)));
			$users = $query->fetchAll(PDO::FETCH_COLUMN);

			$users = array_diff($users, array($log));
			$total = count($users);

			// Рассылка сообщений с подготовкой запросов
			if ($total>0){

				$text = 'Поздравляем! Вы получили пригласительный ключ <br />Ваш ключ: %s';

				$updateusers = DB::run() -> prepare("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=? LIMIT 1;");
				$insertprivat = DB::run() -> prepare("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);");
				$dbr = DB::run() -> prepare("INSERT INTO `invite` (`key`, `user`, `time`) VALUES (?, ?, ?);");

				foreach ($users as $user){
					$key = generate_password(rand(12, 15));
					$updateusers -> execute($user);
					$insertprivat -> execute($user, $log, sprintf($text, $key), SITETIME);
					$dbr -> execute($key, $user, SITETIME);
				}

				notice('Ключи успешно разосланы! (Отправлено: '.$total.')');
				redirect("invitations.php");

			} else {
				show_error('Ошибка! Отсутствуют получатели ключей!');
			}
		} else {
			show_error('Ошибка! Рассылать ключи может только администрация!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php?act=new">Вернуться</a><br />';
break;
############################################################################################
##                                    Генерация ключей                                    ##
############################################################################################
case 'generate':

	$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
	$keys = (isset($_POST['keys'])) ? abs(intval($_POST['keys'])) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($keys)) {

			$dbr = DB::run() -> prepare("INSERT INTO `invite` (`key`, `user`, `time`) VALUES (?, ?, ?);");

			for($i = 0; $i < $keys; $i++) {
				$key = generate_password(rand(12, 15));
				$dbr -> execute($key, $log, SITETIME);
			}

			notice('Ключи успешно сгенерированы!');
			redirect("invitations.php");

		} else {
			show_error('Ошибка! Не указано число генерируемых ключей!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php?act=new">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление ключей                                     ##
############################################################################################
case 'del':

	$uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
	$del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($del)) {

			$del = implode(',', $del);

			DB::run() -> query("DELETE FROM `invite` WHERE `id` IN (".$del.");");

			notice('Выбранные ключи успешно удалены!');
			redirect("invitations.php?used=$used&start=$start");

		} else {
			show_error('Ошибка! Отсутствуют выбранные ключи!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="invitations.php">Вернуться</a><br />';
break;

default:
	redirect("invitations.php");
endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
