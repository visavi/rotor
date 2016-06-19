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

if (is_admin()) {
	show_title('Управление событиями');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':
	echo '<div class="form"><a href="/events/index.php">Обзор событий</a></div>';

	$total = DB::run() -> querySingle("SELECT count(*) FROM `events`;");

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['postevents']);
		}

		$queryevents = DB::run() -> query("SELECT * FROM `events` ORDER BY `event_time` DESC LIMIT ".$start.", ".$config['postevents'].";");

		echo '<form action="events.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

		while ($data = $queryevents -> fetch()) {
			echo '<div class="b">';

			$icon = (empty($data['event_closed'])) ? 'document_plus.gif' : 'document_minus.gif';
			echo '<img src="/images/img/'.$icon.'" alt="image" /> ';

			echo '<b><a href="/events/index.php?act=read&amp;id='.$data['event_id'].'">'.$data['event_title'].'</a></b><small> ('.date_fixed($data['event_time']).')</small><br />';
			echo '<input type="checkbox" name="del[]" value="'.$data['event_id'].'" /> ';
			echo '<a href="events.php?act=edit&amp;id='.$data['event_id'].'&amp;start='.$start.'">Редактировать</a></div>';

			if (!empty($data['event_image'])) {
				echo '<div class="img"><a href="/upload/events/'.$data['event_image'].'">'.resize_image('upload/events/', $data['event_image'], 75, $data['event_title']).'</a></div>';
			}

			if (!empty($data['event_top'])){
				echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
			}

			if(stristr($data['event_text'], '[cut]')) {
				$data['event_text'] = current(explode('[cut]', $data['event_text'])).' <a href="/events/index.php?act=read&amp;id='.$data['event_id'].'">Читать далее &raquo;</a>';
			}

			echo '<div>'.bb_code($data['event_text']).'</div>';

			echo '<div style="clear:both;">Добавлено: '.profile($data['event_author']).'<br />';
			echo '<a href="/events/index.php?act=comments&amp;id='.$data['event_id'].'">Комментарии</a> ('.$data['event_comments'].') ';
			echo '<a href="/events/index.php?act=end&amp;id='.$data['event_id'].'">&raquo;</a></div>';
		}

		echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

		page_strnavigation('events.php?', $config['postevents'], $start, $total);

		echo 'Всего событий: <b>'.(int)$total.'</b><br /><br />';
	} else {
		show_error('Событий еще нет!');
	}

	echo '<img src="/images/img/open.gif" alt="image" /> <a href="/events/index.php?act=new">Добавить событие</a><br />';

	if (is_admin(array(101))) {
		echo '<img src="/images/img/reload.gif" alt="image" /> <a href="events.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
	}
break;

############################################################################################
##                          Подготовка к редактированию события                           ##
############################################################################################
case 'edit':
	$dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

	if (!empty($dataevent)) {

		echo '<b><big>Редактирование</big></b><br /><br />';

		echo '<div class="form cut">';
		echo '<form action="events.php?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
		echo 'Заголовок:<br />';
		echo '<input type="text" name="title" size="50" maxlength="50" value="'.$dataevent['event_title'].'" /><br />';
		echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$dataevent['event_text'].'</textarea><br />';

		if (!empty($dataevent['event_image']) && file_exists(BASEDIR.'/upload/events/'.$dataevent['event_image'])){

			echo '<a href="/upload/events/'.$dataevent['event_image'].'">'.resize_image('upload/events/', $dataevent['event_image'], 75, $dataevent['event_title']).'</a><br />';
			echo '<b>'.$dataevent['event_image'].'</b> ('.read_file(BASEDIR.'/upload/events/'.$dataevent['event_image']).')<br /><br />';
		}

		echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br />';
		echo '<i>gif, jpg, jpeg, png и bmp (Не более '.formatsize($config['filesize']).' и '.$config['fileupfoto'].'px)</i><br /><br />';

		$checked = ($dataevent['event_closed'] == 1) ? ' checked="checked"' : '';
		echo '<input name="closed" type="checkbox" value="1"'.$checked.' /> Закрыть комментарии<br />';

		$checked = ($dataevent['event_top'] == 1) ? ' checked="checked"' : '';
		echo '<input name="top" type="checkbox" value="1"'.$checked.' /> Показывать на главной<br />';

		echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
	} else {
		show_error('Ошибка! Выбранного события не существует, возможно оно было удалено!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="events.php?start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                            Редактирование выбранного события                           ##
############################################################################################
case 'change':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';
	$closed = (empty($_POST['closed'])) ? 0 : 1;
	$top = (empty($_POST['top'])) ? 0 : 1;

	$dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `event_id`=? LIMIT 1;", array($id));

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_empty', $dataevent, 'Выбранного события не существует, возможно оно было удалено!')
		-> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
		-> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

	if ($validation->run(1)) {

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
		redirect("events.php?act=edit&id=$id");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="events.php?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="events.php?start='.$start.'">К событиям</a><br />';
break;

############################################################################################
##                                  Пересчет комментариев                                 ##
############################################################################################
case 'restatement':

	$uid = check($_GET['uid']);

	if (is_admin(array(101))) {
		if ($uid == $_SESSION['token']) {
			restatement('events');

			notice('Комментарии успешно пересчитаны!');
			redirect("events.php");

		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="events.php">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление событий                                    ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($del)) {
			if (is_writeable(BASEDIR.'/upload/events')){

				$del = implode(',', $del);

				$querydel = DB::run()->query("SELECT `event_image` FROM `events` WHERE `event_id` IN (".$del.");");
				$arr_image = $querydel->fetchAll();

				if (count($arr_image)>0){
					foreach ($arr_image as $delete){
						unlink_image('upload/events/', $delete['event_image']);
					}
				}

				DB::run() -> query("DELETE FROM `events` WHERE `event_id` IN (".$del.");");
				DB::run() -> query("DELETE FROM `commevents` WHERE `commevent_event_id` IN (".$del.");");

				notice('Выбранные события успешно удалены!');
				redirect("events.php?start=$start");

				} else {
				show_error('Ошибка! Не установлены атрибуты доступа на директорию с изображениями!');
			}
		} else {
			show_error('Ошибка! Отсутствуют выбранные события!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="events.php?start='.$start.'">Вернуться</a><br />';
break;

default:
	redirect("events.php");
endswitch;

echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect(BASEDIR.'/index.php');
}

include_once ('../themes/footer.php');
?>
