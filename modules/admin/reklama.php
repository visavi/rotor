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
	show_title('Админская реклама');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo 'Каждый админ или модер сможет добавить 1 рекламную ссылку которая будет в случайном порядке выводится на главную страницу вместе с другими ссылками старших сайта<br /><br />';
			// ---------------------- Обзор ссылок --------------------------------//
			if (is_admin(array(101))) {
				echo '<big><b>Список всех ссылок</b></big><br /><br />';

				$queryadv = DB::run() -> query("SELECT * FROM `advert`;");
				$arradv = $queryadv -> fetchAll();
				$total = count($arradv);

				if ($total > 0) {
					echo '<div class="form">';
					echo '<form action="reklama.php?act=delstr&amp;uid='.$_SESSION['token'].'" method="post">';

					foreach ($arradv as $val) {
						if (!empty($val['adv_color'])) {
							$val['adv_title'] = '<span style="color:'.$val['adv_color'].'">'.$val['adv_title'].'</span>';
						}

						echo '<input type="checkbox" name="del[]" value="'.$val['adv_id'].'" /> ';
						echo '<img src="/images/img/edit.gif" alt="image" /> <b><a href="'.$val['adv_url'].'">'.$val['adv_title'].'</a></b> ('.nickname($val['adv_user']).')<br />';
					}

					echo '<input type="submit" value="Удалить выбранное" /></form></div><br />';

					echo 'Всего ссылок: <b>'.$total.'</b><br /><br />';
				} else {
					show_error('Ссылок еще нет!');
				}
			}
			// -------------------------------------------------------------------//
			$advert = DB::run() -> queryFetch("SELECT * FROM `advert` WHERE `adv_user`=? LIMIT 1;", array($log));

			if (!empty($advert)) {
				// --------------------------- Изменение -------------------------------//
				echo '<big><b>Изменение ссылки</b></big><br /><br />';

				echo '<div class="form">';
				echo '<form action="reklama.php?act=edit&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Ссылка:<br />';
				echo '<input type="text" name="url" maxlength="50" value="'.$advert['adv_url'].'" /><br />';
				echo 'Название:<br />';
				echo '<input type="text" name="title" maxlength="40" value="'.$advert['adv_title'].'" /><br />';
				echo 'Код цвета:<br />';
				echo '<input type="text" name="color" maxlength="7" value="'.$advert['adv_color'].'" /><br />';
				echo '<input type="submit" value="Изменить" /></form></div><br />';

				echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="reklama.php?act=del&amp;uid='.$_SESSION['token'].'">Удалить ссылку</a></b><br />';
			} else {
				// --------------------------- Добавление -------------------------------//
				echo '<big><b>Добавление ссылки</b></big><br /><br />';

				echo '<div class="form">';
				echo '<form action="reklama.php?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Ссылка:<br />';
				echo '<input type="text" name="url" maxlength="50" value="http://" /><br />';
				echo 'Название:<br />';
				echo '<input type="text" name="title" maxlength="40" /><br />';
				echo 'Код цвета:<br />';
				echo '<input type="text" name="color" maxlength="7" /><br />';
				echo '<input type="submit" value="Добавить" /></div><br />';
			}

			echo 'Вы можете добавить ссылку если вы этого еще не сделали, изменить или удалить ее если она уже имеется<br /><br />';
		break;

		############################################################################################
		##                                   Добавление ссылки                                    ##
		############################################################################################
		case 'add':

			$uid = check($_GET['uid']);
			$url = check($_POST['url']);
			$title = check($_POST['title']);
			$color = check($_POST['color']);

			if ($uid == $_SESSION['token']) {
				if (strlen($url) <= 50) {
					if (utf_strlen($title) >= 10 && utf_strlen($title) <= 40) {
						if (preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=])+)+$#iu', $url)) {
							if (preg_match('|^#+[A-f0-9]{6}$|', $color) || empty($color)) {
								$queryadv = DB::run() -> querySingle("SELECT `adv_id` FROM `advert` WHERE `adv_user`=? LIMIT 1;", array($log));
								if (empty($queryadv)) {
									DB::run() -> query("INSERT INTO `advert` (`adv_url`, `adv_title`, `adv_color`, `adv_user`) VALUES (?, ?, ?, ?);", array($url, $title, $color, $log));
									save_advertadmin();

									$_SESSION['note'] = 'Рекламная ссылка успешно добавлена!';
									redirect("reklama.php");
								} else {
									show_error('Ошибка! Ваша ссылка уже добавлена!!');
								}
							} else {
								show_error('Ошибка! Недопустимый формат цвета ссылки! (пример #ff0000)');
							}
						} else {
							show_error('Ошибка! Недопустимый адрес сайта! (http://sitename.domen)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название! (от 10 до 40 символов)!');
					}
				} else {
					show_error('Ошибка! Слишком длинный адрес ссылки (до 50 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="reklama.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Изменение ссылки                                    ##
		############################################################################################
		case 'edit':

			$uid = check($_GET['uid']);
			$url = check($_POST['url']);
			$title = check($_POST['title']);
			$color = check($_POST['color']);

			if ($uid == $_SESSION['token']) {
				if (strlen($url) <= 50) {
					if (utf_strlen($title) >= 10 && utf_strlen($title) <= 40) {
						if (preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/\-?_=])+)+$#iu', $url)) {
							if (preg_match('|^#+[A-f0-9]{6}$|', $color) || empty($color)) {
								$queryadv = DB::run() -> querySingle("SELECT `adv_id` FROM `advert` WHERE `adv_user`=? LIMIT 1;", array($log));
								if (!empty($queryadv)) {
									DB::run() -> query("UPDATE `advert` SET `adv_url`=?, `adv_title`=?, `adv_color`=? WHERE `adv_user`=?;", array($url, $title, $color, $log));
									save_advertadmin();

									$_SESSION['note'] = 'Рекламная ссылка успешно изменена!';
									redirect("reklama.php");
								} else {
									show_error('Ошибка! Вашей ссылки нет в списке!');
								}
							} else {
								show_error('Ошибка! Недопустимый формат цвета ссылки! (пример #ff0000)');
							}
						} else {
							show_error('Ошибка! Недопустимый адрес сайта! (http://sitename.domen)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название! (от 10 до 40 символов)!');
					}
				} else {
					show_error('Ошибка! Слишком длинный адрес ссылки (до 50 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="reklama.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление ссылки                                     ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				$queryadv = DB::run() -> querySingle("SELECT `adv_id` FROM `advert` WHERE `adv_user`=? LIMIT 1;", array($log));
				if (!empty($queryadv)) {
					DB::run() -> query("DELETE FROM `advert` WHERE `adv_user`=?;", array($log));
					save_advertadmin();

					$_SESSION['note'] = 'Рекламная ссылка успешно удалена!';
					redirect("reklama.php");
				} else {
					show_error('Ошибка! Вашей ссылки нет в списке!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="reklama.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Админское удаление                                   ##
		############################################################################################
		case 'delstr':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (is_admin(array(101))) {
					if (!empty($del)) {
						$del = implode(',', $del);

						DB::run() -> query("DELETE FROM `advert` WHERE `adv_id` IN (".$del.");");
						save_advertadmin();

						$_SESSION['note'] = 'Выбранные ссылки успешно удалены!';
						redirect("reklama.php");
					} else {
						show_error('Ошибка! Не выбраны ссылки для удаления!');
					}
				} else {
					show_error('Ошибка! Удалять ссылки могут только суперадмины!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<i class="fa fa-arrow-circle-left"></i> <a href="reklama.php">Вернуться</a><br />';
		break;

	default:
		redirect("reklama.php");
	endswitch;

	echo '<i class="fa fa-wrench"></i> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
