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

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}

if (is_admin(array(101, 102))) {
	show_title('Управление аватарами');

	switch ($act):
	############################################################################################
	##                                    Бесплатные аватары                                  ##
	############################################################################################
		case 'index':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `avatars`;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryavatars = DB::run() -> query("SELECT * FROM `avatars` ORDER BY `avatars_id` ASC LIMIT ".$start.", ".$config['avlist'].";");

				echo '<form action="avatars.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				while ($data = $queryavatars -> fetch()) {
					echo '<input type="checkbox" name="del[]" value="'.$data['avatars_id'].'" /> <img src="/images/avatars/'.$data['avatars_name'].'" alt="" /> — <b>'.$data['avatars_name'].'</b><br />';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('avatars.php?', $config['avlist'], $start, $total);

				echo 'Всего аватаров: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('В данной категории аватаров нет!');
			}

			echo '<img src="/images/img/download.gif" alt="image" /> <a href="avatars.php?act=add&amp;start='.$start.'">Загрузить</a><br />';
		break;

		############################################################################################
		##                                  Форма загрузки аватара                                ##
		############################################################################################
		case 'add':

			$config['newtitle'] = 'Добавление аватара';

			echo '<div class="form">';
			echo '<form action="avatars.php?act=load&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

			echo 'Прикрепить аватар:<br /><input type="file" name="avatar" /><br />';

			echo '<input type="submit" value="Загрузить" /></form></div><br />';

			echo 'Разрешается добавлять аватары с расширением gif и png<br />';
			echo 'Весом не более '.formatsize($config['avatarweight']).' и размером '.$config['avatarsize'].' px<br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="avatars.php?start='.$start.'">Вернуться</a><br />';
			break;

		############################################################################################
		##                                   Загрузка аватара                                     ##
		############################################################################################
		case 'load':

			$config['newtitle'] = 'Результат добавления';

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {
					$avatarname = check(strtolower($_FILES['avatar']['name']));
					$avatarsize = getimagesize($_FILES['avatar']['tmp_name']);
					$ext = strrchr($avatarname, '.');

					if (preg_match('|^[a-z0-9_\.\-]+$|i', $avatarname)) {
						if (strlen($avatarname) >= 5 && strlen($avatarname) <= 20) {
							$checkname = DB::run() -> querySingle("SELECT `avatars_id` FROM `avatars` WHERE `avatars_name`=? LIMIT 1;", array($avatarname));
							if (empty($checkname)) {
								if ($ext == '.gif' || $ext == '.png') {
									if ($_FILES['avatar']['size'] > 0 && $_FILES['avatar']['size'] <= $config['avatarweight']) {
										if ($avatarsize[0] == $config['avatarsize'] && $avatarsize[1] == $config['avatarsize']) {
											DB::run() -> query("INSERT INTO `avatars` (`avatars_cats`, `avatars_name`) VALUES (?, ?);", array(1, $avatarname));


											move_uploaded_file($_FILES['avatar']['tmp_name'], BASEDIR.'/images/avatars/'.$avatarname);
											@chmod(BASEDIR.'/images/avatars/'.$avatarname, 0666);

											$_SESSION['note'] = 'Аватар успешно загружен!';
											redirect("avatars.php?start=$start");
										} else {
											show_error('Ошибка! Размер изображения должен быть '.$config['avatarsize'].' px!');
										}
									} else {
										show_error('Ошибка! Вес изображения должен быть не более '.formatsize($config['avatarweight']).'!');
									}
								} else {
									show_error('Ошибка! Недопустимое расширение (Разрешено gif и png)!');
								}
							} else {
								show_error('Ошибка! Аватар с данным названием уже имеется на сайте!');
							}
						} else {
							show_error('Слишком длинное или короткое имя аватара (Необходимо от 2 до 20 символов)!');
						}
					} else {
						show_error('Ошибка! В названии изображения присутствуют недопустимые символы!');
					}
				} else {
					show_error('Ошибка! Не удалось загрузить изображение!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="avatars.php?act=add&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление аватаров                                    ##
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

					$querydel = DB::run() -> query("SELECT `avatars_name` FROM `avatars` WHERE `avatars_id` IN (".$del.");");
					$arr_avatars = $querydel -> fetchAll();

					DB::run() -> query("DELETE FROM `avatars` WHERE `avatars_id` IN (".$del.");");

					foreach ($arr_avatars as $delfile) {
						if (!empty($delfile['avatars_name']) && file_exists(BASEDIR.'/images/avatars/'.$delfile['avatars_name'])) {
							unlink(BASEDIR.'/images/avatars/'.$delfile['avatars_name']);
						}
					}

					$_SESSION['note'] = 'Выбранные аватары успешно удалены!';
					redirect("avatars.php?start=$start");
				} else {
					show_error('Ошибка! Отсутствуют выбранные аватары!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="avatars.php?start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("avatars.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
