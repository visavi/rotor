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

include_once ('../includes/upload.php');

$smilemaxsize = 10240; // Максимальный вес смайла, byte
$smilemaxweight = 100; // Максимальный размер смайла, px
$smileminweight = 16; // Минимальный размер смайла, px

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

if (is_admin(array(101, 102))) {
	show_title('Управление смайлами');

switch ($act):
############################################################################################
##                                    Список смайлов                                      ##
############################################################################################
case 'index':

	$total = DB::run() -> querySingle("SELECT count(*) FROM `smiles`;");

	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$querysmiles = DB::run() -> query("SELECT * FROM `smiles` ORDER BY LENGTH(`smiles_code`) ASC LIMIT ".$start.", ".$config['smilelist'].";");

		echo '<form action="smiles.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

		while ($data = $querysmiles -> fetch()) {
			echo '<img src="/images/smiles/'.$data['smiles_name'].'" alt="" /> — <b>'.$data['smiles_code'].'</b><br />';

			echo '<input type="checkbox" name="del[]" value="'.$data['smiles_id'].'" /> <a href="smiles.php?act=edit&amp;id='.$data['smiles_id'].'&amp;start='.$start.'">Редактировать</a><br />';
		}

		echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

		page_strnavigation('smiles.php?', $config['smilelist'], $start, $total);

		echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
	} else {
		show_error('Смайлы еще не загружены!');
	}

	echo '<img src="/images/img/download.gif" alt="image" /> <a href="smiles.php?act=add&amp;start='.$start.'">Загрузить</a><br />';
break;

############################################################################################
##                                  Форма загрузки смайла                                 ##
############################################################################################
case 'add':

	$config['newtitle'] = 'Добавление смайла';

	echo '<div class="form">';
	echo '<form action="smiles.php?act=load&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

	echo 'Прикрепить смайл:<br /><input type="file" name="smile" /><br />';
	echo 'Код смайла: <br /><input type="text" name="code" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';

	echo '<input type="submit" value="Загрузить" /></form></div><br />';

	echo 'Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png, bmp<br />';
	echo 'Весом не более '.formatsize($smilemaxsize).' и размером до '.$smilemaxweight.' px<br /><br />';

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Загрузка смайла                                      ##
############################################################################################
case 'load':

	$config['newtitle'] = 'Результат добавления';

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$code = (isset($_POST['code'])) ? check(strtolower($_POST['code'])) : '';

	if (is_writeable(BASEDIR.'/images/smiles')){

		$smile = DB::run() -> queryFetch("SELECT * FROM `smiles` WHERE `smiles_code`=? LIMIT 1;", array($code));

		$validation = new Validation;

		$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
			-> addRule('empty', $smile, 'Смайл с данным кодом уже имеется в списке!')
			-> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 2, 20)
			-> addRule('regex', array($code, '|^:+[a-z0-9_\-/]+$|i'), 'Код смайла должен начинаться с двоеточия. Разрешены знаки латинского алфавита, цифры и дефис!', true);


		if ($validation->run()) {

			$handle = new FileUpload($_FILES['smile']);

			if ($handle -> uploaded) {

				$handle -> file_new_name_body = substr($code, 1);
				$handle -> file_overwrite = true;

				$handle -> ext_check = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
				$handle -> file_max_size = $smilemaxsize;  // byte
				$handle -> image_max_width = $smilemaxweight;  // px
				$handle -> image_max_height = $smilemaxweight; // px
				$handle -> image_min_width = $smileminweight;   // px
				$handle -> image_min_height = $smileminweight;  // px

				$handle -> process(BASEDIR.'/images/smiles/');

				if ($handle -> processed) {

					DB::run() -> query("INSERT INTO `smiles` (`smiles_cats`, `smiles_name`, `smiles_code`) VALUES (?, ?, ?);", array(1, $handle -> file_dst_name, $code));
					$handle -> clean();

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
	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?act=add&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Редактирование                                      ##
############################################################################################
case 'edit':

	$data = DB::run() -> queryFetch("SELECT * FROM `smiles` WHERE `smiles_id`=? LIMIT 1;", array($id));

	if (!empty($data)) {
		echo '<b><big>Редактирование смайла</big></b><br /><br />';

		echo '<img src="/images/smiles/'.$data['smiles_name'].'" alt="" /> — <b>'.$data['smiles_code'].'</b><br />';

		echo '<div class="form">';
		echo '<form action="smiles.php?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
		echo 'Код смайла:<br />';
		echo '<input type="text" name="code" value="'.$data['smiles_code'].'" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';
		echo '<input type="submit" value="Изменить" /></form></div><br />';
	} else {
		show_error('Ошибка! Смайла для редактирования не существует!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Изменение смайла                                     ##
############################################################################################
case 'change':

	$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
	$code = (isset($_POST['code'])) ? check(strtolower($_POST['code'])) : '';

	$smile = DB::run() -> queryFetch("SELECT * FROM `smiles` WHERE `smiles_id`=? LIMIT 1;", array($id));
	$checkcode = DB::run() -> querySingle("SELECT `smiles_id` FROM `smiles` WHERE `smiles_code`=? AND `smiles_id`<>? LIMIT 1;", array($code, $id));

	$validation = new Validation;

	$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
		-> addRule('not_empty', $smile, 'Не найден смайл для редактирования!')
		-> addRule('empty', $checkcode, 'Смайл с данным кодом уже имеется в списке!')
		-> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 1, 20)
		-> addRule('regex', array($code, '|^:+[a-z0-9_\-/]+$|i'), 'Код смайла должен начинаться с двоеточия. Разрешены знаки латинского алфавита, цифры и дефис!', true);

	if ($validation->run()) {

		$newname = rename_file($smile['smiles_name'], substr($code, 1));

		if (rename(BASEDIR.'/images/smiles/'.$smile['smiles_name'], BASEDIR.'/images/smiles/'.$newname)){

			DB::run() -> query("UPDATE `smiles` SET `smiles_name`=?, `smiles_code`=? WHERE `smiles_id`=?", array($newname, $code, $id));

			notice('Смайл успешно отредактирован!');
			redirect("smiles.php?start=$start");

		} else {
			show_error('Ошибка! Не удалось переименовать файл смайла!');
		}
	} else {
		show_error($validation->errors);
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Удаление смайлов                                     ##
############################################################################################
case 'del':
	$uid = check($_GET['uid']);
	$del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

	if ($uid == $_SESSION['token']) {
		if (!empty($del)) {
			if (is_writeable(BASEDIR.'/images/smiles')){

				$del = implode(',', $del);

				$querydel = DB::run() -> query("SELECT `smiles_name` FROM `smiles` WHERE `smiles_id` IN (".$del.");");
				$arr_smiles = $querydel -> fetchAll();

				if (count($arr_smiles)>0){
					foreach ($arr_smiles as $delfile) {
						if (file_exists(BASEDIR.'/images/smiles/'.$delfile['smiles_name'])) {
							unlink(BASEDIR.'/images/smiles/'.$delfile['smiles_name']);
						}
					}
				}

				DB::run() -> query("DELETE FROM `smiles` WHERE `smiles_id` IN (".$del.");");

				notice('Выбранные смайлы успешно удалены!');
				redirect("smiles.php?act=$ref&start=$start");

			} else {
				show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
			}
		} else {
			show_error('Ошибка! Отсутствуют выбранные смайлы!');
		}
	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	echo '<img src="/images/img/back.gif" alt="image" /> <a href="smiles.php?act='.$ref.'&amp;start='.$start.'">Вернуться</a><br />';
break;

default:
	redirect("smiles.php");
endswitch;

echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
