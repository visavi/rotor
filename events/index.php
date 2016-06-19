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
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Интернет события');

render('events/menu', array('act' => $act, 'is_admin' => is_admin(), 'is_user' => is_user()));

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	$total = DB::run() -> querySingle("SELECT count(*) FROM `events`;");

	$page = floor(1 + $start / $config['postevents']);
	$config['description'] = 'Список событий (Стр. '.$page.')';

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['postevents']);
		}

		$queryevents = DB::run() -> query("SELECT * FROM `events` ORDER BY `event_time` DESC LIMIT ".$start.", ".$config['postevents'].";");
		$events = $queryevents->fetchAll();

		render('events/index', array('events' => $events));

		page_strnavigation('index.php?', $config['postevents'], $start, $total);
	} else {
		show_error('Событий еще нет!');
	}
break;

############################################################################################
##                                     Чтение события                                     ##
############################################################################################
case 'read':
	$data = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

	if (!empty($data)) {

		if (is_admin()){
			echo '<div class="form"><a href="/admin/events.php?act=edit&amp;id='.$id.'">Редактировать</a> / ';
			echo '<a href="/admin/events.php?act=del&amp;del='.$id.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данное событие?\')">Удалить</a></div>';
		}

		$config['newtitle'] = $data['event_title'];
		$config['description'] = strip_str($data['event_text']);

		echo '<div class="b"><img src="/images/img/files.gif" alt="image" /> ';
		echo '<b>'.$data['event_title'].'</b><small> ('.date_fixed($data['event_time']).')</small></div>';

		if (!empty($data['event_image'])) {

			echo '<div class="img"><a href="/upload/events/'.$data['event_image'].'">'.resize_image('upload/events/', $data['event_image'], 75, $data['event_title']).'</a></div>';
		}

		$data['event_text'] = str_replace('[cut]', '', $data['event_text']);

		echo '<div>'.bb_code($data['event_text']).'</div>';
		echo '<div style="clear:both;">Добавлено: '.profile($data['event_author']).'</div><br />';

		if ($data['event_comments'] > 0) {
		echo '<div class="act"><img src="/images/img/balloon.gif" alt="image" /> <b>Последние комментарии</b></div>';

			$querycomm = DB::run() -> query("SELECT * FROM `commevents` WHERE `commevent_event_id`=? ORDER BY `commevent_time` DESC LIMIT 5;", array($id));
			$comments = $querycomm -> fetchAll();
			$comments = array_reverse($comments);

			foreach ($comments as $comm) {
				echo '<div class="b">';
				echo '<div class="img">'.user_avatars($comm['commevent_author']).'</div>';

				echo '<b>'.profile($comm['commevent_author']).'</b>';
				echo '<small> ('.date_fixed($comm['commevent_time']).')</small><br />';
				echo user_title($comm['commevent_author']).' '.user_online($comm['commevent_author']).'</div>';

				echo '<div>'.bb_code($comm['commevent_text']).'<br />';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<span class="data">('.$comm['commevent_brow'].', '.$comm['commevent_ip'].')</span>';
				}

				echo '</div>';
			}

			if ($data['event_comments'] > 5) {
				echo '<div class="act"><b><a href="index.php?act=comments&amp;id='.$data['event_id'].'">Все комментарии</a></b> ('.$data['event_comments'].') ';
				echo '<a href="index.php?act=end&amp;id='.$data['event_id'].'">&raquo;</a></div><br />';
			}

		} else {
			show_error('Комментариев еще нет!');
		}

		if (is_user()) {
			if (empty($data['event_closed'])) {
				echo '<div class="form"><form action="index.php?act=addcomment&amp;id='.$id.'&amp;read=1&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
				echo '<input type="submit" value="Написать" /></form></div>';

				echo '<br /><a href="#up"><img src="/images/img/ups.gif" alt="image" /></a> ';
				echo '<a href="/pages/rules.php">Правила</a> / ';
				echo '<a href="/pages/smiles.php">Смайлы</a> / ';
				echo '<a href="/pages/tags.php">Теги</a><br /><br />';
			} else {
				show_error('Комментирование данного события закрыто!');
			}
		} else {
			show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
		}
	} else {
		show_error('Ошибка! Выбранного вами события не существует, возможно оно было удалено!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                            Подготовка к добавлению события                             ##
############################################################################################
case 'new':
	if (is_user()) {
		if ($udata['users_point'] >= $config['eventpoint']){
			echo '<b><big>Добавление события</big></b><br /><br />';

			echo '<div class="form cut">';
			echo '<form action="index.php?act=addevent&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
			echo 'Заголовок:<br />';
			echo '<input type="text" name="title" size="50" maxlength="50" /><br />';
			echo '<textarea id="markItUp" cols="50" rows="10" name="msg"></textarea><br />';
			echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br />';
			echo '<i>gif, jpg, jpeg, png и bmp (Не более '.formatsize($config['filesize']).' и '.$config['fileupfoto'].'px)</i><br /><br />';

			if (is_admin()){
				echo '<input name="top" type="checkbox" value="1" /> Вывести на главную<br />';
				echo '<input name="closed" type="checkbox" value="1" /> Закрыть комментарии<br />';
			}
			echo '<input type="submit" value="Добавить" /></form></div><br />';

			echo 'Рекомендация! Для обрезки события используйте тег [cut]<br />';
		} else {
			show_error('Ошибка! У вас недостаточно актива для создания события (Необходимо '.points($config['eventpoint']).')!');
		}
	} else {
		show_login('Вы не авторизованы, для создания события, необходимо');
	}
			echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">Вернуться</a><br />';
break;

############################################################################################
##                                  Добавление события                                    ##
############################################################################################
case 'addevent':
	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';

	$top = (!is_admin() || empty($_POST['top'])) ? 0 : 1;
	$closed = (!is_admin() || empty($_POST['closed'])) ? 0 : 1;

	if (is_user()) {

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('equal', array(is_quarantine($log), true), 'Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!')
			-> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается публиковать события раз в '.flood_period().' сек!')
			-> addRule('max', array($udata['users_point'], $config['eventpoint']), 'У вас недостаточно актива для создания события!')
			-> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
			-> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

		if ($validation->run()) {

			$msg = antimat($msg);

			DB::run() -> query("INSERT INTO `events` (`event_title`, `event_text`, `event_author`, `event_time`, `event_comments`, `event_closed`, `event_top`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($title, $msg, $log, SITETIME, 0, $closed, $top));

			$lastid = DB::run() -> lastInsertId();

			// ---------------------------- Загрузка изображения -------------------------------//
			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $lastid);
				if ($handle) {

					$handle -> process(BASEDIR.'/upload/events/');

					if ($handle -> processed) {
						DB::run() -> query("UPDATE `events` SET `event_image`=? WHERE `event_id`=? LIMIT 1;", array($handle -> file_dst_name, $lastid));
						$handle -> clean();

					} else {
						notice($handle->error, 'danger');
						redirect("index.php?act=editevent&id=$lastid");
					}
				}
			}
			// ---------------------------------------------------------------------------------//

			notice('Событие успешно добавленo!');
			redirect("index.php");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, для создания события, необходимо');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php?act=new">Вернуться</a><br />';
	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                                Редактирование события                                  ##
############################################################################################
case 'editevent':
	if (is_user()) {
		$dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

		$validation = new Validation;

		$validation -> addRule('not_empty', $dataevent, 'Выбранного события не существует, возможно оно было удалено!')
			-> addRule('equal', array($log, $dataevent['event_author']), 'Изменение невозможно, вы не автор данного события!')
			-> addRule('max', array(($dataevent['event_time'] + 3600), SITETIME), 'Изменение невозможно, прошло более 1 часа!');

		if ($validation->run(1)) {

			echo '<b><big>Редактирование</big></b><br /><br />';

			echo '<div class="form cut">';
			echo '<form action="index.php?act=changeevent&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
			echo 'Заголовок:<br />';
			echo '<input type="text" name="title" size="50" maxlength="50" value="'.$dataevent['event_title'].'" /><br />';
			echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$dataevent['event_text'].'</textarea><br />';

			if (!empty($dataevent['event_image']) && file_exists(BASEDIR.'/upload/events/'.$dataevent['event_image'])){
				echo '<a href="/upload/events/'.$dataevent['event_image'].'">'.resize_image('upload/events/', $dataevent['event_image'], 75, $dataevent['event_title']).'</a><br />';
				echo '<b>'.$dataevent['event_image'].'</b> ('.read_file(BASEDIR.'/upload/events/'.$dataevent['event_image']).')<br /><br />';
			}

			echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br />';
			echo '<i>gif, jpg, jpeg, png и bmp (Не более '.formatsize($config['filesize']).' и '.$config['fileupfoto'].'px)</i><br /><br />';

			if (is_admin()){
				$checked = ($dataevent['event_closed'] == 1) ? ' checked="checked"' : '';
				echo '<input name="closed" type="checkbox" value="1"'.$checked.' /> Закрыть комментарии<br />';

				$checked = ($dataevent['event_top'] == 1) ? ' checked="checked"' : '';
				echo '<input name="top" type="checkbox" value="1"'.$checked.' /> Показывать на главной<br />';
			}

			echo '<input type="submit" value="Изменить" /></form></div><br />';

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, для редактирования события, необходимо');
	}

	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                                   Изменение события                                    ##
############################################################################################
case 'changeevent':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';

	$top = (!is_admin() || empty($_POST['top'])) ? 0 : 1;
	$closed = (!is_admin() || empty($_POST['closed'])) ? 0 : 1;

	if (is_user()) {
		$dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('not_empty', $dataevent, 'Выбранного события не существует, возможно оно было удалено!')
			-> addRule('equal', array($log, $dataevent['event_author']), 'Изменение невозможно, вы не автор данного события!')
			-> addRule('max', array(($dataevent['event_time'] + 3600), SITETIME), 'Изменение невозможно, прошло более 1 часа!')
			-> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
			-> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

		if ($validation->run(1)) {

			$msg = antimat($msg);

			DB::run() -> query("UPDATE `events` SET `event_title`=?, `event_text`=?, `event_closed`=?, `event_top`=? WHERE `event_id`=? LIMIT 1;", array($title, $msg, $closed, $top, $id));

			// ---------------------------- Загрузка изображения -------------------------------//
			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $id);
				if ($handle) {

					// Удаление старой картинки
					if (!empty($dataevent['event_image'])) {
						unlink_image('upload/events/', $dataevent['event_image']);
					}

					$handle -> process(BASEDIR.'/upload/events/');

					if ($handle -> processed) {
						DB::run() -> query("UPDATE `events` SET `event_image`=? WHERE `event_id`=? LIMIT 1;", array($handle -> file_dst_name, $id));
						$handle -> clean();

					} else {
						notice($handle->error, 'danger');
					}
				}
			}
			// ---------------------------------------------------------------------------------//

			notice('Событие успешно отредактировано!');
			redirect("index.php?act=editevent&id=$id");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, для редактирования события, необходимо');
	}
	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php?act=editevent&amp;id='.$id.'">Вернуться</a><br />';
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                                     Комментарии                                        ##
############################################################################################
case 'comments':

	$dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

	if (!empty($dataevent)) {
		$config['newtitle'] = 'Комментарии - '.$dataevent['event_title'];

		$page = floor(1 + $start / $config['postevents']);
		$config['description'] = 'Комментарии - '.$dataevent['event_title'].' (Стр. '.$page.')';

		echo '<img src="/images/img/files.gif" alt="image" /> <b><a href="index.php?act=read&amp;id='.$dataevent['event_id'].'">'.$dataevent['event_title'].'</a></b><br /><br />';

		echo '<a href="#down"><img src="/images/img/downs.gif" alt="image" /></a> ';
		echo '<a href="index.php?act=end&amp;id='.$id.'">Обновить</a><hr />';

		$total = DB::run() -> querySingle("SELECT count(*) FROM `commevents` WHERE `commevent_event_id`=?;", array($id));

		if ($total > 0) {
			if ($start >= $total) {
				$start = last_page($total, $config['postevents']);
			}

			$is_admin = is_admin();
			if ($is_admin) {
				echo '<form action="index.php?act=del&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
			}

			$querycomm = DB::run() -> query("SELECT * FROM `commevents` WHERE `commevent_event_id`=? ORDER BY `commevent_time` ASC LIMIT ".$start.", ".$config['postevents'].";", array($id));

			while ($data = $querycomm -> fetch()) {

				echo '<div class="b">';
				echo '<div class="img">'.user_avatars($data['commevent_author']).'</div>';

				if ($is_admin) {
					echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['commevent_id'].'" /></span>';
				}

				echo '<b>'.profile($data['commevent_author']).'</b>';
				echo '<small> ('.date_fixed($data['commevent_time']).')</small><br />';
				echo user_title($data['commevent_author']).' '.user_online($data['commevent_author']).'</div>';

				echo '<div>'.bb_code($data['commevent_text']).'<br />';

				if (is_admin() || empty($config['anonymity'])) {
					echo '<span class="data">('.$data['commevent_brow'].', '.$data['commevent_ip'].')</span>';
				}

				echo '</div>';
			}

			if ($is_admin) {
				echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
			}

			page_strnavigation('index.php?act=comments&amp;id='.$id.'&amp;', $config['postevents'], $start, $total);

		} else {
			show_error('Комментариев еще нет!');
		}

		if (is_user()) {
			if (empty($dataevent['event_closed'])) {
				echo '<div class="form"><form action="index.php?act=addcomment&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
				echo '<input type="submit" value="Написать" /></form></div>';

				echo '<br /><a href="#up"><img src="/images/img/ups.gif" alt="image" /></a> ';
				echo '<a href="/pages/rules.php">Правила</a> / ';
				echo '<a href="/pages/smiles.php">Смайлы</a> / ';
				echo '<a href="/pages/tags.php">Теги</a><br /><br />';
			} else {
				show_error('Комментирование данного события закрыто!');
			}
		} else {
			show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
		}
	} else {
		show_error('Ошибка! Выбранного события не существует, возможно оно было удалено!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                                Добавление комментариев                                 ##
############################################################################################
case 'addcomment':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';

	if (is_user()) {
		$data = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('equal', array(is_quarantine($log), true), 'Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!')
			-> addRule('equal', array(is_flood($log), true), 'Антифлуд! Разрешается публиковать события раз в '.flood_period().' сек!')
			-> addRule('not_empty', $data, 'Выбранного события не существует, возможно оно было удалено!')
			-> addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
			-> addRule('empty', $data['event_closed'], 'Комментирование данного события запрещено!');

		if ($validation->run(3)) {

			$msg = antimat($msg);

			DB::run() -> query("INSERT INTO `commevents` (`commevent_event_id`, `commevent_text`, `commevent_author`, `commevent_time`, `commevent_ip`, `commevent_brow`) VALUES (?, ?, ?, ?, ?, ?);", array($id, $msg, $log, SITETIME, $ip, $brow));

			DB::run() -> query("DELETE FROM `commevents` WHERE `commevent_event_id`=? AND `commevent_time` < (SELECT MIN(`commevent_time`) FROM (SELECT `commevent_time` FROM `commevents` WHERE `commevent_event_id`=? ORDER BY `commevent_time` DESC LIMIT ".$config['maxkommevents'].") AS del);", array($id, $id));

			DB::run() -> query("UPDATE `events` SET `event_comments`=`event_comments`+1 WHERE `event_id`=?;", array($id));
			DB::run() -> query("UPDATE `users` SET `users_allcomments`=`users_allcomments`+1, `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

			notice('Комментарий успешно добавлен!');

			if (isset($_GET['read'])) {
				redirect("index.php?act=read&id=$id");
			}

			redirect("index.php?act=end&id=$id");

		} else {
			show_error($validation->errors);
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
	}

	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                                 Удаление комментариев                                  ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if (is_admin()) {
		if ($uid == $_SESSION['token']) {
			if (!empty($del)) {

				$del = implode(',', $del);

				$delcomments = DB::run() -> exec("DELETE FROM `commevents` WHERE `commevent_id` IN (".$del.") AND `commevent_event_id`=".$id.";");
				DB::run() -> query("UPDATE `events` SET `event_comments`=`event_comments`-? WHERE `event_id`=?;", array($delcomments, $id));

				notice('Выбранные комментарии успешно удалены!');
				redirect("index.php?act=comments&id=$id&start=$start");

			} else {
				show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_error('Ошибка! Удалять комментарии могут только модераторы!');
	}

	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="index.php?act=comments&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

############################################################################################
##                             Переадресация на последнюю страницу                        ##
############################################################################################
case 'end':

	$query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `commevents` WHERE `commevent_event_id`=? LIMIT 1;", array($id));

	if (!empty($query)) {
		$total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
		$end = last_page($total_comments, $config['postevents']);

		redirect("index.php?act=comments&id=$id&start=$end");

	} else {
		show_error('Ошибка! Данного события не существует!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">К событиям</a><br />';
break;

default:
	redirect("index.php");
endswitch;

include_once ('../themes/footer.php');
?>
