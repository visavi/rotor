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

if (is_admin()) {
	show_title('Управление галереей');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo '<a href="#down"><img src="/images/img/downs.gif" alt="image" /></a> ';
			echo '<a href="gallery.php?start='.$start.'&amp;rand='.mt_rand(100, 999).'">Обновить</a> / ';
			echo '<a href="/gallery/index.php?act=addphoto">Добавить фото</a> / ';
			echo '<a href="/gallery/index.php?start='.$start.'">Обзор</a><hr />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `photo`;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				echo '<form action="gallery.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				$queryphoto = DB::run() -> query("SELECT * FROM `photo` ORDER BY `photo_time` DESC LIMIT ".$start.", ".$config['fotolist'].";");

				while ($data = $queryphoto -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/gallery.gif" alt="image" /> ';
					echo '<b><a href="/gallery/index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.$data['photo_title'].'</a></b> ('.read_file(BASEDIR.'/upload/pictures/'.$data['photo_link']).')<br />';
					echo '<input type="checkbox" name="del[]" value="'.$data['photo_id'].'" /> <a href="gallery.php?act=edit&amp;start='.$start.'&amp;gid='.$data['photo_id'].'">Редактировать</a>';
					echo '</div>';

					echo '<div><a href="/gallery/index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.resize_image('upload/pictures/', $data['photo_link'], $config['previewsize'], $data['photo_title']).'</a><br />';

					if (!empty($data['photo_text'])){
						echo bb_code($data['photo_text']).'<br />';
					}

					echo 'Добавлено: '.profile($data['photo_user']).' ('.date_fixed($data['photo_time']).')<br />';
					echo '<a href="/gallery/index.php?act=comments&amp;gid='.$data['photo_id'].'">Комментарии</a> ('.$data['photo_comments'].') ';
					echo '<a href="/gallery/index.php?act=end&amp;gid='.$data['photo_id'].'">&raquo;</a>';
					echo '</div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('gallery.php?', $config['fotolist'], $start, $total);

				echo 'Всего фотографий: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('Фотографий еще нет!');
			}

			if (is_admin(array(101))) {
				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="gallery.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
			}
		break;

		############################################################################################
		##                                    Редактирование                                      ##
		############################################################################################
		case 'edit':

			$gid = abs(intval($_GET['gid']));

			$photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

			if (!empty($photo)) {

				$photo['photo_text'] = yes_br(nosmiles($photo['photo_text']));

				echo '<div class="form">';
				echo '<form action="gallery.php?act=change&amp;gid='.$gid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Название: <br /><input type="text" name="title" value="'.$photo['photo_title'].'" /><br />';
				echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text">'.$photo['photo_text'].'</textarea><br />';

				echo 'Закрыть комментарии: ';
				$checked = ($photo['photo_closed'] == 1) ? ' checked="checked"' : '';
				echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

				echo '<input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Данной фотографии не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="gallery.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Изменение сообщения                                    ##
		############################################################################################
		case 'change':

			$uid = check($_GET['uid']);
			$gid = abs(intval($_GET['gid']));
			$title = check($_POST['title']);
			$text = check($_POST['text']);
			$closed = (empty($_POST['closed'])) ? 0 : 1;

			if ($uid == $_SESSION['token']) {
				$photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($gid));

				if (!empty($photo)) {
					if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
						if (utf_strlen($text) <= 1000) {
							$text = no_br($text);
							$text = antimat($text);
							$text = smiles($text);

							DB::run() -> query("UPDATE `photo` SET `photo_title`=?, `photo_text`=?, `photo_closed`=? WHERE `photo_id`=?;", array($title, $text, $closed, $gid));

							$_SESSION['note'] = 'Фотография успешно отредактирована!';
							redirect("gallery.php?start=$start");

						} else {
							show_error('Ошибка! Слишком длинное описание (Необходимо до 1000 символов)!');
						}
					} else {
						show_error('Ошибка! Слишком длинное или короткое название!');
					}
				} else {
					show_error('Ошибка! Данной фотографии не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="gallery.php?act=edit&amp;gid='.$gid.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="gallery.php">Галерея</a><br />';
		break;

		############################################################################################
		##                                 Удаление изображений                                   ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);

			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} elseif (isset($_GET['del'])) {
				$del = array(abs(intval($_GET['del'])));
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);

					if (is_writeable(BASEDIR.'/upload/pictures')) {
						$querydel = DB::run() -> query("SELECT `photo_id`, `photo_link` FROM `photo` WHERE `photo_id` IN (".$del.");");
						$arr_photo = $querydel -> fetchAll();

						if (count($arr_photo) > 0) {
							foreach ($arr_photo as $delete) {
								DB::run() -> query("DELETE FROM `photo` WHERE `photo_id`=? LIMIT 1;", array($delete['photo_id']));
								DB::run() -> query("DELETE FROM `commphoto` WHERE `commphoto_gid`=?;", array($delete['photo_id']));

								unlink_image('upload/pictures/', $delete['photo_link']);
							}

							$_SESSION['note'] = 'Выбранные фотографии успешно удалены!';
							redirect("gallery.php?start=$start");

						} else {
							show_error('Ошибка! Данных фотографий не существует!');
						}
					} else {
						show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');
					}
				} else {
					show_error('Ошибка! Отсутствуют выбранные фотографии!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="gallery.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Пересчет комментариев                                 ##
		############################################################################################
		case 'restatement':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					restatement('gallery');

					$_SESSION['note'] = 'Комментарии успешно пересчитаны!';
					redirect("gallery.php");

				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Пересчитывать комментарии могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="gallery.php">Вернуться</a><br />';
		break;

	default:
		redirect("gallery.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
