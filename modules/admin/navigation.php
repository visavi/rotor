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

if (is_admin(array(101, 102))) {
	show_title('site.png', 'Управление навигацией');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$querynav = DB::run() -> query("SELECT * FROM navigation ORDER BY nav_order ASC;");
			$arrnav = $querynav -> fetchAll();
			$total = count($arrnav);

			if ($total > 0) {
				echo '<div class="form">';
				echo '<form action="navigation.php?act=del&amp;uid='.$_SESSION['token'].'" method="post">';

				foreach ($arrnav as $val) {
					echo '<input type="checkbox" name="del[]" value="'.$val['nav_id'].'" /> <img src="/images/img/edit.gif" alt="image" /> <b>'.$val['nav_order'].'</b>. <b><a href="navigation.php?act=edit&amp;id='.$val['nav_id'].'">'.$val['nav_title'].'</a></b> ('.$val['nav_url'].')<br />';
				}

				echo '<input type="submit" value="Удалить выбранное" /></form></div><br />';

				echo 'Всего ссылок: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('Ссылок еще нет!');
			}

			echo '<b><big>Добавление ссылки</big></b><br /><br />';

			echo '<div class="form">';
			echo '<form action="navigation.php?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
			echo 'Ссылка:<br />';
			echo '<input type="text" name="url" /><br />';
			echo 'Название:<br />';
			echo '<input type="text" name="title" /><br />';
			echo 'Положение:<br />';
			echo '<input type="text" name="order" maxlength="2" /><br />';
			echo '<input type="submit" value="Добавить" /></form></div><br />';
		break;

		############################################################################################
		##                               Подготовка к редактированию                              ##
		############################################################################################
		case 'edit':

			$id = abs(intval($_GET['id']));

			$data = DB::run() -> queryFetch("SELECT * FROM navigation WHERE nav_id=? LIMIT 1;", array($id));

			if (!empty($data)) {
				echo '<b><big>Редактирование ссылки</big></b><br /><br />';

				echo '<div class="form">';
				echo '<form action="navigation.php?id='.$id.'&amp;act=change&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Ссылка:<br />';
				echo '<input type="text" name="url" value="'.$data['nav_url'].'" /><br />';
				echo 'Название:<br />';
				echo '<input type="text" name="title" value="'.$data['nav_title'].'" /><br />';
				echo 'Положение:<br />';
				echo '<input type="text" name="order" maxlength="2" value="'.$data['nav_order'].'" /><br />';
				echo '<input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Данной ссылки не существует!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="navigation.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                     Редактирование                                     ##
		############################################################################################
		case 'change':

			$uid = check($_GET['uid']);
			$url = check($_POST['url']);
			$title = check($_POST['title']);
			$order = abs(intval($_POST['order']));
			$id = abs(intval($_GET['id']));

			if ($uid == $_SESSION['token']) {
				if (!empty($url)) {
					if (utf_strlen($title) >= 3 && utf_strlen($title) <= 35) {
						$querynav = DB::run() -> querySingle("SELECT nav_id FROM navigation WHERE nav_id=? LIMIT 1;", array($id));
						if (!empty($querynav)) {
							DB::run() -> query("UPDATE navigation SET nav_url=?, nav_title=?, nav_order=? WHERE nav_id=?", array($url, $title, $order, $id));

							save_navigation();

							$_SESSION['note'] = 'Ссылка успешно изменена!';
							redirect("navigation.php");
						} else {
							show_error('Ошибка! Редактируемой ссылки не существует!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название! (от 3 до 35 символов)!');
					}
				} else {
					show_error('Ошибка! Не указана ссылка для навигации!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-up"></i> <a href="navigation.php?act=edit&amp;id='.$id.'">Вернуться</a><br />';
			echo '<i class="fa fa-arrow-circle-left"></i> <a href="navigation.php">К списку</a><br />';
		break;

		############################################################################################
		##                                         Добавление                                     ##
		############################################################################################
		case 'add':

			$uid = check($_GET['uid']);
			$url = check($_POST['url']);
			$title = check($_POST['title']);
			$order = abs(intval($_POST['order']));

			if ($uid == $_SESSION['token']) {
				if (!empty($url)) {
					if (utf_strlen($title) >= 5 && utf_strlen($title) <= 35) {
						DB::run() -> query("INSERT INTO navigation (nav_url, nav_title, nav_order) VALUES (?, ?, ?);", array($url, $title, $order));

						save_navigation();

						$_SESSION['note'] = 'Ссылка успешно добавлена!';
						redirect("navigation.php");
					} else {
						show_error('Ошибка! Слишком длинное или короткое название! (от 5 до 35 символов)!');
					}
				} else {
					show_error('Ошибка! Не указана ссылка для навигации!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="navigation.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление ссылок                                     ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM navigation WHERE nav_id IN (".$del.");");

					save_navigation();

					$_SESSION['note'] = 'Выбранные ссылки успешно удалены!';
					redirect("navigation.php");
				} else {
					show_error('Ошибка! Не выбраны ссылки для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="navigation.php">Вернуться</a><br />';
		break;

	default:
		redirect("navigation.php");
	endswitch;

	echo '<i class="fa fa-wrench"></i> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
