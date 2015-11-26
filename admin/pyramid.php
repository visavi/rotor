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

if (is_admin()) {
	show_title('Управление пирамидой');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$queryadv = DB::run() -> query("SELECT * FROM `pyramid` ORDER BY `pyramid_id` DESC;");
			$arradv = $queryadv -> fetchAll();
			$total = count($arradv);

			if ($total > 0) {
				echo '<div class="form">';
				echo '<form action="pyramid.php?act=delstr&amp;uid='.$_SESSION['token'].'" method="post">';

				foreach ($arradv as $val) {
					echo '<input type="checkbox" name="del[]" value="'.$val['pyramid_id'].'" /> ';
					echo '<img src="/images/img/edit.gif" alt="image" /> <b><a href="'.$val['pyramid_link'].'">'.$val['pyramid_name'].'</a></b> ('.profile($val['pyramid_user']).') — <a href="pyramid.php?act=edit&amp;id='.$val['pyramid_id'].'&amp;uid='.$_SESSION['token'].'">Изменить</a><br />';
				}

				echo '<input type="submit" value="Удалить выбранное" /></form></div><br />';

				echo 'Всего ссылок: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('Ссылок еще нет!');
			}
		break;

		############################################################################################
		##                                  Редактирование ссылки                                 ##
		############################################################################################
		case 'edit':

			if (isset($_GET['id'])) {
				$id = abs(intval($_GET['id']));
			} else {
				$id = 0;
			}

			$pyramid = DB::run() -> queryFetch("SELECT * FROM `pyramid` WHERE `pyramid_id`=? LIMIT 1;", array($id));

			if (!empty($pyramid)) {
				echo '<b><big>Редактирование ссылки</big></b><br /><br />';
				echo '<div class="form">';
				echo '<form action="pyramid.php?act=change&amp;id='.$pyramid['pyramid_id'].'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Ссылка:<br />';
				echo '<input type="text" name="link" value="'.$pyramid['pyramid_link'].'" maxlength="50" /><br />';
				echo 'Название:<br />';
				echo '<input type="text" name="name" value="'.$pyramid['pyramid_name'].'" maxlength="25" /><br />';
				echo '<input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Данной ссылки не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="pyramid.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Изменение ссылки                                    ##
		############################################################################################
		case 'change':

			if (isset($_GET['id'])) {
				$id = abs(intval($_GET['id']));
			} else {
				$id = 0;
			}

			$uid = check($_GET['uid']);
			$link = check(utf_lower($_POST['link']));
			$name = check($_POST['name']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($link) >= 10 && utf_strlen($link) <= 50) {
					if (utf_strlen($name) >= 5 && utf_strlen($name) <= 25) {
						if (preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $link)) {
							$querypyr = DB::run() -> querySingle("SELECT `pyramid_id` FROM `pyramid` WHERE `pyramid_id`=? LIMIT 1;", array($id));
							if (!empty($querypyr)) {
								DB::run() -> query("UPDATE `pyramid` SET `pyramid_link`=?, `pyramid_name`=? WHERE`pyramid_id`=?;", array($link, $name, $id));

								$_SESSION['note'] = 'Рекламная ссылка успешно изменена!';
								redirect("pyramid.php");
							} else {
								show_error('Ошибка! Данной ссылки нет в списке!');
							}
						} else {
							show_error('Ошибка! Недопустимый адрес сайта! (http://sitename.domen)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название. Не менее 5 и не более 25 символов!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий адрес ссылки. Не менее 10 и не более 50 символов!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="pyramid.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление                                   ##
		############################################################################################
		case 'delstr':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM `pyramid` WHERE `pyramid_id` IN (".$del.");");

					$_SESSION['note'] = 'Выбранные ссылки успешно удалены!';
					redirect("pyramid.php");
				} else {
					show_error('Ошибка! Не выбраны ссылки для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="pyramid.php">Вернуться</a><br />';
			break;

	default:
		redirect("pyramid.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
