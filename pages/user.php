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
$uz = (empty($_GET['uz'])) ? check($log) : check(strval($_GET['uz']));

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	$data = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
	if (!empty($data)) {

		show_title(user_avatars($uz).nickname($uz), user_visit($uz));
		$config['newtitle'] = 'Анкета пользователя '.nickname($data['users_login']);

		if ($data['users_confirmreg'] == 1) {
			echo '<b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br />';
		}

		if ($data['users_ban'] == 1 && $data['users_timeban'] > SITETIME) {
			echo '<div class="form">';
			echo '<b><span style="color:#ff0000">Внимание, юзер находится в бане!</span></b><br />';
			echo 'До окончания бана осталось '.formattime($data['users_timeban'] - SITETIME).'<br />';
			echo 'Причина: '.bb_code($data['users_reasonban']).'</div>';
		}

		if ($data['users_level'] >= 101 && $data['users_level'] <= 105) {
			echo '<div class="info">Должность: <b>'.user_status($data['users_level']).'</b></div>';
		}

		if (!empty($data['users_picture']) && file_exists(BASEDIR.'/upload/photos/'.$data['users_picture'])) {
			echo '<div class="imgright"><a href="/upload/photos/'.$data['users_picture'].'">';
			echo resize_image('upload/photos/', $data['users_picture'], $config['previewsize'], nickname($data['users_login'])).'</a></div>';
		} else {
			echo '<div class="imgright"><img src="/images/img/photo.jpg" alt="Фото" /></div>';
		}

		echo 'Cтатус: <b><a href="statusfaq.php">'.user_title($data['users_login']).'</a></b><br />';

		echo user_gender($data['users_login']).'Пол: ';
		echo ($data['users_gender'] == 1) ? 'Мужской <br />' : 'Женский<br />';

		echo 'Логин: <b>'.$data['users_login'].'</b><br />';
		if (!empty($data['users_nickname'])) {
			echo 'Ник: <b>'.$data['users_nickname'].'</b><br />';
		}
		if (!empty($data['users_name'])) {
			echo 'Имя: <b>'.$data['users_name'].'<br /></b>';
		}
		if (!empty($data['users_country'])) {
			echo 'Страна: <b>'.$data['users_country'].'<br /></b>';
		}
		if (!empty($data['users_city'])) {
			echo 'Откуда: '.$data['users_city'].'<br />';
		}
		if (!empty($data['users_birthday'])) {
			echo 'Дата рождения: '.$data['users_birthday'].'<br />';
		}
		if (!empty($data['users_icq'])) {
			echo '<img src="http://web.icq.com/whitepages/online?icq='.$data['users_icq'].'&amp;img=27" alt="icq" /> ICQ: '.$data['users_icq'].' <br />';
		}
		if (!empty($data['users_skype'])) {
			echo '<img src="http://mystatus.skype.com/smallicon/'.$data['users_skype'].'" alt="skype" /> Skype: '.$data['users_skype'].' <br />';
		}
		if (!empty($data['users_jabber'])) {
			echo 'Jabber: '.$data['users_jabber'].' <br />';
		}

		echo 'Всего посeщений: '.$data['users_visits'].'<br />';
		echo 'Сообщений на форуме: '.$data['users_allforum'].'<br />';
		echo 'Сообщений в гостевой: '.$data['users_allguest'].'<br />';
		echo 'Комментариев: '.$data['users_allcomments'].'<br />';
		echo 'Актив: '.points($data['users_point']).' <br />';
		echo 'Денег: '.moneys($data['users_money']).'<br />';

		if (!empty($data['users_themes'])) {
			echo 'Используемый скин: '.$data['users_themes'].'<br />';
		}
		echo 'Дата регистрации: '.date_fixed($data['users_joined'], 'j F Y').'<br />';

		$invite = DB::run() -> queryFetch("SELECT * FROM `invite` WHERE `invited`=?;", array($uz));
		if (!empty($invite)){
			echo 'Зарегистрирован по приглашению: '.profile($invite['user']).'<br />';
		}

		echo 'Последняя авторизация: '.date_fixed($data['users_timelastlogin']).'<br />';

		echo '<a href="banhist.php?uz='.$uz.'">Строгих нарушений: '.$data['users_totalban'].'</a><br />';

		echo '<a href="rathist.php?uz='.$uz.'">Авторитет: <b>'.format_num($data['users_rating']).'</b> (+'.$data['users_posrating'].'/-'.$data['users_negrating'].')</a><br />';

		if (is_user() && $log != $uz) {
			echo '[ <a href="rating.php?uz='.$uz.'&amp;vote=1"><img src="/images/img/plus.gif" alt="Плюс" /><span style="color:#0099cc"> Плюс</span></a> / ';
			echo '<a href="rating.php?uz='.$uz.'&amp;vote=0"><span style="color:#ff0000">Минус</span> <img src="/images/img/minus.gif" alt="Минус" /></a> ]<br />';
		}

		echo '<b><a href="/forum/active.php?act=themes&amp;uz='.$uz.'">Форум</a></b> (<a href="/forum/active.php?act=posts&amp;uz='.$uz.'">Сообщ.</a>) / ';
		echo '<b><a href="/load/active.php?act=files&amp;uz='.$uz.'">Загрузки</a></b> (<a href="/load/active.php?act=comments&amp;uz='.$uz.'">комм.</a>) / ';
		echo '<b><a href="/blog/active.php?act=blogs&amp;uz='.$uz.'">Блоги</a></b> (<a href="/blog/active.php?act=comments&amp;uz='.$uz.'">комм.</a>) / ';
		echo '<b><a href="/gallery/album.php?act=photo&amp;uz='.$uz.'">Галерея</a></b> (<a href="/gallery/comments.php?act=comments&amp;uz='.$uz.'">комм.</a>)<br />';

		if (!empty($data['users_info'])) {
			echo '<div class="hide"><b>О себе</b>:<br />'.bb_code($data['users_info']).'</div>';
		}

		if (is_admin()) {
			$usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `note_user`=? LIMIT 1;", array($uz));

			echo '<div class="form">';
			echo '<img src="/images/img/pin.gif" alt="Заметка" /> <b>Заметка:</b> (<a href="user.php?act=note&amp;uz='.$uz.'">Изменить</a>)<br />';

			if (!empty($usernote['note_text'])) {
				echo bb_code($usernote['note_text']).'<br />';
				echo 'Изменено: '.profile($usernote['note_edit']).' ('.date_fixed($usernote['note_time']).')<br />';
			} else {
				echo'Записей еще нет!<br />';
			}

			echo '</div>';
		}

		echo '<div class="act">';
		echo '<img src="/images/img/wall.gif" alt="Стена" /> <a href="wall.php?uz='.$uz.'">Стена сообщений</a> ('.user_wall($uz).')<br />';

		if ($uz != $log) {
			echo '<img src="/images/img/users.gif" alt="Добавить" /> Добавить в ';
			echo '<a href="contact.php?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">контакт</a> / ';
			echo '<a href="ignore.php?act=add&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'">игнор</a><br />';
			echo '<img src="/images/img/mail.gif" alt="Отправить" /> <a href="private.php?act=submit&amp;uz='.$uz.'">Отправить сообщение</a><br />';

			echo '<img src="/images/img/money.gif" alt="Перечислить" /> <a href="/pages/perevod.php?uz='.$uz.'">Перечислить денег</a><br />';

			if (!empty($data['users_site'])) {
				echo '<img src="/images/img/homepage.gif" alt="Перейти" /> <a href="'.$data['users_site'].'">Перейти на сайт '.$uz.'</a><br />';
			}

			if (is_admin(array(101, 102, 103))) {
				if (!empty($config['invite'])) {
					echo '<img src="/images/img/error.gif" alt="Бан" /> <a href="/admin/invitations.php?act=send&amp;user='.$uz.'&amp;uid='.$_SESSION['token'].'">Отправить инвайт</a><br />';
				}
				echo '<img src="/images/img/error.gif" alt="Бан" /> <a href="/admin/zaban.php?act=edit&amp;uz='.$uz.'">Бан / Разбан</a><br />';
			}

			if (is_admin(array(101, 102))) {
				echo '<img src="/images/img/panel.gif" alt="Редактировать" /> <a href="/admin/users.php?act=edit&amp;uz='.$uz.'">Редактировать</a><br />';
			}
		} else {
			echo '<img src="/images/img/user.gif" alt="Профиль" /> <a href="profile.php">Мой профиль</a><br />';
			echo '<img src="/images/img/account.gif" alt="Данные" /> <a href="account.php">Мои данные</a><br />';
			echo '<img src="/images/img/panel.gif" alt="Настройки" /> <a href="setting.php">Настройки</a><br />';
		}

		echo '</div>';
	} else {
		show_title('Пользователь не найден');
		show_error('Ошибка! Пользователь с данным логином  не зарегистрирован!');
	}
break;

############################################################################################
##                                      Редактирование                                    ##
############################################################################################
case 'note':

	show_title('Заметка для пользователя '.nickname($uz));

	if (is_admin()) {
		if (check_user($uz)) {
			$usernote = DB::run() -> queryFetch("SELECT * FROM `note` WHERE `note_user`=? LIMIT 1;", array($uz));

			echo '<div class="form">';
			echo '<form action="user.php?act=editnote&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';
			$usernote['note_text'] = yes_br(nosmiles($usernote['note_text']));
			echo '<textarea id="markItUp" cols="25" rows="5" name="note">'.$usernote['note_text'].'</textarea><br />';
			echo '<input value="Сохранить" type="submit" /></form></div><br />';
		} else {
			show_error('Ошибка! Пользователя с данным логином не существует!');
		}
	} else {
		show_error('Ошибка! Данная страница доступна только администрации!');
	}

	echo '<img src="/images/img/back.gif" alt="Назад" /> <a href="user.php?uz='.$uz.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Изменене заметки                                    ##
############################################################################################
case 'editnote':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$note = (isset($_POST['note'])) ? check($_POST['note']) : '';

	if (is_admin()) {

		$validation = new Validation;
		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('equal', array(check_user($uz), true), 'Пользователя с данным логином не существует!')
			-> addRule('string', $note, 'Слишком большая заметка, не более 1000 символов!', true, 0, 1000);

		if ($validation->run()) {

				$note = smiles(no_br($note));

				DB::run() -> query("INSERT INTO `note` (`note_user`, `note_text`, `note_edit`, `note_time`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `note_text`=?, `note_edit`=?, `note_time`=?;", array($uz, $note, $log, SITETIME, $note, $log, SITETIME));

				notice('Заметка успешно сохранена!');
				redirect("user.php?uz=$uz");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_error('Ошибка! Данная страница доступна только администрации!');
	}

	echo '<img src="/images/img/back.gif" alt="Назад" /> <a href="user.php?act=note&amp;uz='.$uz.'">Вернуться</a><br />';
break;

default:
	redirect('user.php');
endswitch;

include_once ('../themes/footer.php');
?>
