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

if (is_admin(array(101, 102))) {
	show_title('Правила сайта');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$rules = DB::run() -> queryFetch("SELECT * FROM `rules`;");

			if (!empty($rules)) {
				$rules['rules_text'] = str_replace(array('%SITENAME%', '%MAXBAN%'), array($config['title'], round($config['maxbantime'] / 1440)), $rules['rules_text']);

				echo bb_code($rules['rules_text']).'<hr />';

				echo 'Последнее изменение: '.date_fixed($rules['rules_time']).'<br /><br />';
			} else {
				show_error('Правила сайта еще не установлены!');
			}

			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="rules.php?act=edit">Редактировать</a><br />';
		break;

		############################################################################################
		##                                   Редактирование                                       ##
		############################################################################################
		case 'edit':

			$rules = DB::run() -> queryFetch("SELECT * FROM `rules`;");

			echo '<div class="form">';
			echo '<form action="rules.php?act=change&amp;uid='.$_SESSION['token'].'" method="post">';

			$rules['rules_text'] = yes_br(nosmiles($rules['rules_text']));

			echo '<textarea id="markItUp" cols="35" rows="20" name="msg">'.$rules['rules_text'].'</textarea><br />';
			echo '<input type="submit" value="Изменить" /></form></div><br />';

			echo '<b>Внутренние переменные:</b><br />';
			echo '%SITENAME% - Название сайта<br />';
			echo '%MAXBAN% - Максимальное время бана<br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="rules.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Изменение                                          ##
		############################################################################################
		case 'change':

			$uid = check($_GET['uid']);
			$msg = check($_POST['msg']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($msg) > 0) {
					$msg = no_br($msg);
					$msg = str_replace('&#37;', '%', $msg);

					DB::run() -> query("REPLACE INTO `rules` (`rules_id`, `rules_text`, `rules_time`) VALUES (?,?,?);", array(1, $msg, SITETIME));

					$_SESSION['note'] = 'Правила успешно изменены!';
					redirect("rules.php");
				} else {
					show_error('Ошибка! Вы не ввели текст с правилами сайта!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="rules.php?act=edit">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="rules.php">К правилам</a><br />';
		break;

	default:
		redirect("rules.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
