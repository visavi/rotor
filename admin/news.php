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

if (is_admin(array(101, 102))) {
	show_title('Управление новостями');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

	echo '<div class="form"><a href="/news/index.php">Обзор новостей</a></div>';

	$total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");

	if ($total > 0) {
		if ($start >= $total) {
			$start = last_page($total, $config['postnews']);
		}

		$querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `news_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

		echo '<form action="news.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

		while ($data = $querynews -> fetch()) {

			echo '<div class="b">';

			$icon = (empty($data['news_closed'])) ? 'document_plus.gif' : 'document_minus.gif';
			echo '<img src="/images/img/'.$icon.'" alt="image" /> ';

			echo '<b><a href="/news/index.php?act=read&amp;id='.$data['news_id'].'">'.$data['news_title'].'</a></b><small> ('.date_fixed($data['news_time']).')</small><br />';
			echo '<input type="checkbox" name="del[]" value="'.$data['news_id'].'" /> ';
			echo '<a href="news.php?act=edit&amp;id='.$data['news_id'].'&amp;start='.$start.'">Редактировать</a></div>';

			if (!empty($data['news_image'])) {
				echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, $data['news_title']).'</a></div>';
			}

			if (!empty($data['news_top'])){
				echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
			}

			if(stristr($data['news_text'], '[cut]')) {
				$data['news_text'] = current(explode('[cut]', $data['news_text'])).' <a href="/news/index.php?act=read&amp;id='.$data['news_id'].'">Читать далее &raquo;</a>';
			}

			echo '<div>'.bb_code($data['news_text']).'</div>';

			echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'<br />';
			echo '<a href="/news/index.php?act=comments&amp;id='.$data['news_id'].'">Комментарии</a> ('.$data['news_comments'].') ';
			echo '<a href="/news/index.php?act=end&amp;id='.$data['news_id'].'">&raquo;</a></div>';
		}

		echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

		page_strnavigation('news.php?', $config['postnews'], $start, $total);

		echo 'Всего новостей: <b>'.(int)$total.'</b><br /><br />';
	} else {
		show_error('Новостей еще нет!');
	}

	echo '<img src="/images/img/open.gif" alt="image" /> <a href="news.php?act=add">Добавить</a><br />';

	if (is_admin(array(101))) {
		echo '<img src="/images/img/reload.gif" alt="image" /> <a href="news.php?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
	}
break;

############################################################################################
##                          Подготовка к редактированию новости                           ##
############################################################################################
case 'edit':
	$datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

	if (!empty($datanews)) {
		$datanews['news_text'] = yes_br(nosmiles($datanews['news_text']));

		echo '<b><big>Редактирование</big></b><br /><br />';

		echo '<div class="form cut">';
		echo '<form action="news.php?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
		echo 'Заголовок:<br />';
		echo '<input type="text" name="title" size="50" maxlength="50" value="'.$datanews['news_title'].'" /><br />';
		echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$datanews['news_text'].'</textarea><br />';

		if (!empty($datanews['news_image']) && file_exists(BASEDIR.'/upload/news/'.$datanews['news_image'])){

			echo '<a href="/upload/news/'.$datanews['news_image'].'">'.resize_image('upload/news/', $datanews['news_image'], 75, $datanews['news_title']).'</a><br />';
			echo '<b>'.$datanews['news_image'].'</b> ('.read_file(BASEDIR.'/upload/news/'.$datanews['news_image']).')<br /><br />';
		}

		echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br /><br />';

		echo 'Закрыть комментарии: ';
		$checked = ($datanews['news_closed'] == 1) ? ' checked="checked"' : '';
		echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

		echo 'Показывать на главной: ';
		$checked = ($datanews['news_top'] == 1) ? ' checked="checked"' : '';
		echo '<input name="top" type="checkbox" value="1"'.$checked.' /><br />';

		echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
	} else {
		show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php?start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                            Редактирование выбранной новости                            ##
############################################################################################
case 'change':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';
	$closed = (empty($_POST['closed'])) ? 0 : 1;
	$top = (empty($_POST['top'])) ? 0 : 1;

	$datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_empty', $datanews, 'Выбранной новости не существует, возможно она была удалена!')
		-> addRule('string', $title, 'Слишком длинный или короткий заголовок новости!', true, 5, 50)
		-> addRule('string', $msg, 'Слишком длинный или короткий текст новости!', true, 5, 10000);

	if ($validation->run(1)) {

		$msg = smiles(no_br($msg));

		DB::run() -> query("UPDATE `news` SET `news_title`=?, `news_text`=?, `news_closed`=?, `news_top`=? WHERE `news_id`=? LIMIT 1;", array($title, $msg, $closed, $top, $id));

		// ---------------------------- Загрузка изображения -------------------------------//
		if (is_uploaded_file($_FILES['image']['tmp_name'])) {
			$handle = upload_image2($_FILES['image'], $config['filesize'], $config['fileupfoto'], $id);
			if ($handle) {

				// Удаление старой картинки
				if (!empty($datanews['news_image'])) {
					unlink_image('upload/news/', $datanews['news_image']);
				}

				$handle -> process(BASEDIR.'/upload/news/');

				if ($handle -> processed) {

					DB::run() -> query("UPDATE `news` SET `news_image`=? WHERE `news_id`=? LIMIT 1;", array($handle -> file_dst_name, $id));
					$handle -> clean();

				} else {
					notice($handle->error, '#ff0000');
				}
			}
		}
		// ---------------------------------------------------------------------------------//

		notice('Новость успешно отредактирована!');
		redirect("news.php?start=$start");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="news.php?start='.$start.'">К новостям</a><br />';
break;

############################################################################################
##                            Подготовка к добавлению новости                             ##
############################################################################################
case 'add':

	echo '<b><big>Создание новости</big></b><br /><br />';

	echo '<div class="form cut">';
	echo '<form action="news.php?act=addnews&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
	echo 'Заголовок:<br />';
	echo '<input type="text" name="title" size="50" maxlength="50" /><br />';
	echo '<textarea id="markItUp" cols="50" rows="10" name="msg"></textarea><br />';
	echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br /><br />';

	echo 'Вывести на главную: <input name="top" type="checkbox" value="1" /><br />';
	echo 'Закрыть комментарии: <input name="closed" type="checkbox" value="1" /><br />';

	echo '<br /><input type="submit" value="Добавить" /></form></div><br />';

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php">Вернуться</a><br />';
break;

############################################################################################
##                                  Добавление новости                                    ##
############################################################################################
case 'addnews':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
	$title = (isset($_POST['title'])) ? check($_POST['title']) : '';
	$top = (empty($_POST['top'])) ? 0 : 1;
	$closed = (empty($_POST['closed'])) ? 0 : 1;


	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
		-> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

	if ($validation->run()) {

		$msg = smiles(no_br($msg));

		DB::run() -> query("INSERT INTO `news` (`news_title`, `news_text`, `news_author`, `news_time`, `news_comments`, `news_closed`, `news_top`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($title, $msg, $log, SITETIME, 0, $closed, $top));

		$lastid = DB::run() -> lastInsertId();

		// Выводим на главную если там нет новостей
		if (!empty($top) && empty($config['lastnews'])) {
			DB::run() -> query("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;", array(1, 'lastnews'));
			save_setting();
		}

		// ---------------------------- Загрузка изображения -------------------------------//
		if (is_uploaded_file($_FILES['image']['tmp_name'])) {
			$handle = upload_image2($_FILES['image'], $config['filesize'], $config['fileupfoto'], $lastid);
			if ($handle) {

				$handle -> process(BASEDIR.'/upload/news/');

				if ($handle -> processed) {
					DB::run() -> query("UPDATE `news` SET `news_image`=? WHERE `news_id`=? LIMIT 1;", array($handle -> file_dst_name, $lastid));
					$handle -> clean();

				} else {
					notice($handle->error, '#ff0000');
					redirect("news.php?act=edit&id=$lastid");
				}
			}
		}
		// ---------------------------------------------------------------------------------//

		notice('Новость успешно добавлена!');
		redirect("news.php");

	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php?act=add">Вернуться</a><br />';
	echo '<img src="/images/img/reload.gif" alt="image" /> <a href="news.php">К новостям</a><br />';
break;

############################################################################################
##                                  Пересчет комментариев                                 ##
############################################################################################
case 'restatement':

	$uid = check($_GET['uid']);

	if (is_admin(array(101))) {
		if ($uid == $_SESSION['token']) {
			restatement('news');

			notice('Комментарии успешно пересчитаны!');
			redirect("news.php");

		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_error('Ошибка! Пересчитывать комментарии могут только суперадмины!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление новостей                                   ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);
	$del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($del)) {
			if (is_writeable(BASEDIR.'/upload/news')){

				$del = implode(',', $del);

				$querydel = DB::run()->query("SELECT `news_image` FROM `news` WHERE `news_id` IN (".$del.");");
				$arr_image = $querydel->fetchAll();

				if (count($arr_image)>0){
					foreach ($arr_image as $delete){
						unlink_image('upload/news/', $delete['news_image']);
					}
				}

				DB::run() -> query("DELETE FROM `news` WHERE `news_id` IN (".$del.");");
				DB::run() -> query("DELETE FROM `commnews` WHERE `commnews_news_id` IN (".$del.");");

				notice('Выбранные новости успешно удалены!');
				redirect("news.php?start=$start");

			} else {
				show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с изображениями!');
			}
		} else {
			show_error('Ошибка! Отсутствуют выбранные новости!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="news.php?start='.$start.'">Вернуться</a><br />';
break;

default:
	redirect("news.php");
endswitch;

echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
