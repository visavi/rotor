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

if (! is_admin(array(101, 102))) redirect('/admin/');

show_title('Управление смайлами');

switch ($act):
/**
 * Список смайлов
 */
case 'index':

	$total = DBM::run()->count('smiles');

	if ($total > 0 && $start >= $total) {
		$start = last_page($total, $config['smilelist']);
	}

	$smiles = DBM::run()->query("SELECT * FROM `smiles` ORDER BY CHAR_LENGTH(`smiles_code`) ASC LIMIT :start, :limit;", array('start' => intval($start), 'limit' => intval($config['smilelist'])));

	echo '<form action="smiles.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

	foreach($smiles as $smile) {
		echo '<img src="/images/smiles/'.$smile['smiles_name'].'" alt="" /> — <b>'.$smile['smiles_code'].'</b><br />';

		echo '<input type="checkbox" name="del[]" value="'.$smile['smiles_id'].'" /> <a href="smiles.php?act=edit&amp;id='.$smile['smiles_id'].'&amp;start='.$start.'">Редактировать</a><br />';
	}

	echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

	page_strnavigation('smiles.php?', $config['smilelist'], $start, $total);

	echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';

	//show_error('Смайлы еще не загружены!');

	echo '<img src="/images/img/download.gif" alt="image" /> <a href="smiles.php?act=add&amp;start='.$start.'">Загрузить</a><br />';
break;

/**
 * Форма загрузки смайла
 */
case 'add':

	$config['newtitle'] = 'Добавление смайла';

	echo '<div class="form">';
	echo '<form action="smiles.php?act=load&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

	echo 'Прикрепить смайл:<br /><input type="file" name="smile" /><br />';
	echo 'Код смайла: <br /><input type="text" name="code" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';

	echo '<input type="submit" value="Загрузить" /></form></div><br />';

	echo 'Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png, bmp<br />';
	echo 'Весом не более '.formatsize($config['smilemaxsize']).' и размером до '.$config['smilemaxweight'].' px<br /><br />';

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?start='.$start.'">Вернуться</a><br />';
break;

/**
 * Загрузка смайла
 */
case 'load':

	$config['newtitle'] = 'Результат добавления';

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

	if (is_writeable(BASEDIR.'/images/smiles')){

		$smile = DBM::run()->selectFirst('smiles', array('smiles_code' => $code));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('empty', $smile, 'Смайл с данным кодом уже имеется в списке!')
			-> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 2, 20)
			-> addRule('regex', array($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i'), 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!', true);


		if ($validation->run()) {

			$handle = new FileUpload($_FILES['smile']);

			if ($handle -> uploaded) {

				if (! preg_match('/[А-Яа-яЁё]/u', $code)) {
					$handle -> file_new_name_body = substr($code, 1);
				}
				//$handle -> file_overwrite = true;

				$handle -> ext_check = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
				$handle -> file_max_size = $config['smilemaxsize'];  // byte
				$handle -> image_max_width = $config['smilemaxweight'];  // px
				$handle -> image_max_height = $config['smilemaxweight']; // px
				$handle -> image_min_width = $config['smileminweight'];   // px
				$handle -> image_min_height = $config['smileminweight'];  // px
				$handle -> process(BASEDIR.'/images/smiles/');

				if ($handle -> processed) {

					$smile = DBM::run()->insert('smiles', array(
						'smiles_cats' => 1,
						'smiles_name' => $handle->file_dst_name,
						'smiles_code' => $code,
					));

					$handle -> clean();
					clearCache();

					notice('Смайл успешно загружен!');
					redirect("smiles.php");

				} else {
					show_error($handle->error);
				}
			} else {
				show_error($handle->error);
			}
		} else {
			show_error($validation->errors);
		}
	} else {
		show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
	}

	render('includes/back', array('link' => 'smiles.php?act=add&amp;start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Редактирование
 */
case 'edit':

	$smile = DBM::run()->selectFirst('smiles', array('smiles_id' => $id));

	if (! empty($smile)) {
		echo '<b><big>Редактирование смайла</big></b><br /><br />';

		echo '<img src="/images/smiles/'.$smile['smiles_name'].'" alt="" /> — <b>'.$smile['smiles_code'].'</b><br />';

		echo '<div class="form">';
		echo '<form action="smiles.php?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
		echo 'Код смайла:<br />';
		echo '<input type="text" name="code" value="'.$smile['smiles_code'].'" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';
		echo '<input type="submit" value="Изменить" /></form></div><br />';
	} else {
		show_error('Ошибка! Смайла для редактирования не существует!');
	}

	render('includes/back', array('link' => 'smiles.php?start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Изменение смайла
 */
case 'change':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

	$smile = DBM::run()->selectFirst('smiles', array('smiles_id' => $id));

	$checkcode = DBM::run()->selectFirst('smiles', array(
		'smiles_code' => $code,
		'smiles_id' => $id,
	));
	$checkcode = DBM::run()->queryFirst("SELECT `smiles_id` FROM `smiles` WHERE `smiles_code`=:code AND `smiles_id`<>:id LIMIT 1;", compact('code', 'id'));

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_empty', $smile, 'Не найден смайл для редактирования!')
		-> addRule('empty', $checkcode, 'Смайл с данным кодом уже имеется в списке!')
		-> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 1, 20)
		-> addRule('regex', array($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i'), 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!', true);

	if ($validation->run()) {

		if (! preg_match('/[А-Яа-яЁё]/u', $code)) {
			$newname = rename_file($smile['smiles_name'], substr($code, 1));
		} else {
			$newname = $smile['smiles_name'];
		}

		if (rename(BASEDIR.'/images/smiles/'.$smile['smiles_name'], BASEDIR.'/images/smiles/'.$newname)){

			$smile = DBM::run()->update('smiles', array(
				'smiles_name' => $newname,
				'smiles_code' => $code,
			), array(
				'smiles_id' => $id
			));
			clearCache();

			notice('Смайл успешно отредактирован!');
			redirect("smiles.php?start=$start");

		} else {
			show_error('Ошибка! Не удалось переименовать файл смайла!');
		}
	} else {
		show_error($validation->errors);
	}

	render('includes/back', array('link' => 'smiles.php?act=edit&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Удаление смайлов
 */
case 'del':
	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (! empty($del)) {
			if (is_writeable(BASEDIR.'/images/smiles')){

				$del = implode(',', $del);

				$arr_smiles = DBM::run()->query("SELECT `smiles_name` FROM `smiles` WHERE `smiles_id` IN(".$del.");");

				if (count($arr_smiles)>0){
					foreach ($arr_smiles as $delfile) {
						if (file_exists(BASEDIR.'/images/smiles/'.$delfile['smiles_name'])) {
							unlink(BASEDIR.'/images/smiles/'.$delfile['smiles_name']);
						}
					}
				}
				DBM::run()->execute("DELETE FROM `smiles` WHERE `smiles_id` IN (".$del.");");
				clearCache();

				notice('Выбранные смайлы успешно удалены!');
				redirect("smiles.php?start=$start");

			} else {
				show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
			}
		} else {
			show_error('Ошибка! Отсутствуют выбранные смайлы!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	render('includes/back', array('link' => 'smiles.php?start='.$start, 'title' => 'Вернуться'));
break;

default:
	redirect("smiles.php");
endswitch;

render('includes/back', array('link' => '/admin/', 'title' => 'В админку', 'icon' => 'panel.gif'));

include_once ('../themes/footer.php');
?>
