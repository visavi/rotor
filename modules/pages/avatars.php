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

show_title('Галерея аватаров');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo '<b>Выбрать</b> или <a href="avatars.php?act=load">Загрузить</a><br /><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `avatars`;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryav = DB::run() -> query("SELECT * FROM `avatars` ORDER BY `avatars_id` ASC LIMIT ".$start.", ".$config['avlist'].";");

				while ($data = $queryav -> fetch()) {
					echo '<img src="/images/avatars/'.$data['avatars_name'].'" alt="" />
<a href="avatars.php?act=select&amp;id='.$data['avatars_id'].'&amp;uid='.$_SESSION['token'].'">Выбрать</a><br />';
				}

				page_strnavigation('avatars.php?', $config['avlist'], $start, $total);

				echo 'Выберите понравившийся вам аватар<br />';
				echo 'Cейчас ваш аватар: '.user_avatars($log).'<br /><br />';
				echo 'Всего аваторов: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('В данной категории аватаров нет!');
			}
		break;

		############################################################################################
		##                                    Выбор аватара                                       ##
		############################################################################################
		case 'select':

			$uid = check($_GET['uid']);
			$id = abs(intval($_GET['id']));

			if ($uid == $_SESSION['token']) {
				$queryav = DB::run() -> querySingle("SELECT `avatars_name` FROM `avatars` WHERE `avatars_id`=?  LIMIT 1;", array($id));
				if (!empty($queryav)) {
					if ($udata['users_avatar'] != 'images/avatars/'.$queryav) {
						DB::run() -> query("UPDATE `users` SET `users_avatar`=? WHERE `users_login`=?;", array('images/avatars/'.$queryav, $log));

						save_avatar();

						unlink_image('upload/avatars/', $log.'.gif');

						echo '<img src="/images/img/open.gif" alt="image" /> <b>Аватар успешно выбран!</b><br /><br />';
					} else {
						show_error('Ошибка! Вы уже выбрали это аватар!');
					}
				} else {
					show_error('Ошибка! Такого аватара не существует!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="avatars.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                              Подготовка к загрузке аватара                             ##
		############################################################################################
		case 'load':

			echo '<a href="avatars.php">Выбрать</a> или <b>Загрузить</b><br /><br />';

			echo 'В наличии: '.moneys($udata['users_money']).'<br /><br />';

			if ($udata['users_point'] >= $config['avatarpoints']) {
				if ($udata['users_money'] >= $config['avatarupload']) {
					echo '<div class="form">';
					echo '<form action="avatars.php?act=addload&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
					echo 'Прикрепить аватар:<br />';
					echo '<input type="file" name="avatar" /><br />';
					echo '<input type="submit" value="Загрузить" /></form></div><br />';
				} else {
					show_error('Недостаточное количество денег на счету для загрузки аватара!');
				}
			} else {
				show_error('Недостаточное количество актива, необходимо набрать более '.points($config['avatarpoints']).'!');
			}

			echo 'Cейчас ваш аватар: '.user_avatars($log).'<br />';
			echo 'Стоимость загрузки аватара составляет '.moneys($config['avatarupload']).'<br />';
			echo 'Внимание! На загрузку аватаров установлены строгие ограничения<br />';
			echo 'Загружать аватары могут только пользователи у которых более '.points($config['avatarpoints']).'<br />';
			echo 'Размер аватара должен быть '.$config['avatarsize'].'*'.$config['avatarsize'].' px, вес не более чем '.formatsize($config['avatarweight']).'<br />';
			echo 'Расширение аватаров в формате .gif (в нижнем регистре)<br /><br />';
		break;

		############################################################################################
		##                                    Загрузка аватара                                    ##
		############################################################################################
		case 'addload':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if (is_uploaded_file($_FILES['avatar']['tmp_name'])) {
					$avatarname = check(strtolower($_FILES['avatar']['name']));
					$avatarsize = getimagesize($_FILES['avatar']['tmp_name']);

					if (strrchr($avatarname, '.') == '.gif') {
						if ($_FILES['avatar']['size'] > 0 && $_FILES['avatar']['size'] <= $config['avatarweight']) {
							if ($avatarsize[0] == $config['avatarsize'] && $avatarsize[1] == $config['avatarsize']) {
								if ($udata['users_point'] >= $config['avatarpoints']) {
									if ($udata['users_money'] >= $config['avatarupload']) {
										move_uploaded_file($_FILES['avatar']['tmp_name'], BASEDIR.'/upload/avatars/'.$log.'.gif');
										chmod(BASEDIR.'/upload/avatars/'.$log.'.gif', 0666);

										DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`-?, `users_avatar`=? WHERE `users_login`=?;", array($config['avatarupload'], 'upload/avatars/'.$log.'.gif', $log));

										save_avatar();

										echo '<img src="/images/img/open.gif" alt="image" /> <b>Аватар успешно загружен!</b><br />';
										echo 'C вашего счета списано '.moneys($config['avatarupload']).'<br /><br />';
									} else {
										show_error('Ошибка! На вашем счете недостаточно средств!');
									}
								} else {
									show_error('Ошибка! Недостаточное количество актива, необходимо набрать более '.points($config['avatarpoints']).'!');
								}
							} else {
								show_error('Ошибка! Размер аватара должен быть '.$config['avatarsize'].'*'.$config['avatarsize'].' px');
							}
						} else {
							show_error('Ошибка! Вес аватара должен быть не более чем '.formatsize($config['avatarweight']).'!');
						}
					} else {
						show_error('Ошибка! Разрешается загружать аватары только в формате .gif');
					}
				} else {
					show_error('Ошибка! Не удалось загрузить аватар!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="avatars.php?act=load">Вернуться</a><br />';
		break;

	default:
		redirect("avatars.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, чтобы изменить аватар, необходимо');
}

include_once ('../themes/footer.php');
?>
