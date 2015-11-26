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

if (is_admin(array(101))) {
	show_title('Шаблоны писем');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":

			$total = DB::run() -> querySingle("SELECT count(*) FROM `notice`;");

			if ($total > 0) {

				$querynotice = DB::run() -> query("SELECT * FROM `notice` ORDER BY `notice_id`;");

				foreach ($querynotice as $notice) {

					echo '<div class="b">';

					echo '<img src="/images/img/mail.gif" alt="image" /> <b><a href="notice.php?act=edit&amp;id='.$notice['notice_id'].'">'.$notice['notice_name'].'</a></b>';
					if (empty($notice['notice_protect'])) {
						echo ' (<a href="notice.php?act=del&amp;id='.$notice['notice_id'].'&amp;uid='.$_SESSION['token'].'">Удалить</a>)';
					} else {
						echo ' (Системный шаблон)';
					}
					echo '</div>';

					echo '<div>Изменено: ';

					if (!empty($notice['notice_user'])){
						echo profile($notice['notice_user']);
					}

					echo ' ('.date_fixed($notice['notice_time']).')';

					echo '</div>';
				}

				echo '<br />Всего шаблонов: '.$total.'<br /><br />';

			} else {
				show_error('Шаблонов еще нет!');
			}
			echo '<img src="/images/img/open.gif" alt="image" /> <a href="notice.php?act=new">Добавить</a><br />';
		break;

		############################################################################################
		##                                Coздание шаблона                                        ##
		############################################################################################
		case "new":
			show_title('Новый шаблон');

			echo '<div class="form">';
			echo '<form action="notice.php?act=save&amp;uid='.$_SESSION['token'].'" method="post">';

			echo 'Название: <br />';
			echo '<input type="text" name="name" maxlength="100" size="50" /><br />';
			echo '<textarea id="markItUp" cols="35" rows="20" name="text"></textarea><br />';
			echo '<input type="submit" value="Сохранить" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notice.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                              Редактирование шаблона                                    ##
		############################################################################################
		case "edit":
			$notice = DB::run() -> queryFetch("SELECT * FROM `notice` WHERE `notice_id`=? LIMIT 1;", array($id));

			if (!empty($notice)) {

				if (!empty($notice['notice_protect'])) {
					echo '<div class="info"><img src="/images/img/warning.gif" alt="image" /> <b>Вы редактируете системный шаблон</b></div><br />';
				}

				echo '<div class="form">';
				echo '<form action="notice.php?act=save&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

				$notice['notice_text'] = yes_br(nosmiles($notice['notice_text']));

				echo 'Название: <br />';
				echo '<input type="text" name="name" maxlength="100" size="50" value="'.$notice['notice_name'].'" /><br />';
				echo '<textarea id="markItUp" cols="35" rows="20" name="text">'.$notice['notice_text'].'</textarea><br />';
				echo '<input type="submit" value="Изменить" /></form></div><br />';

			} else {
				show_error('Ошибка! Шаблона для редактирования не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notice.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Сохранение шаблона                                    ##
		############################################################################################
		case "save":

			$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
			$name = (isset($_POST['name'])) ? check($_POST['name']) : '';
			$text = (isset($_POST['text'])) ? check($_POST['text']) : '';

			$validation = new Validation;

			$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
				-> addRule('string', $name, 'Слишком длинный или короткий заголовок шаблона!', true, 5, 100)
				-> addRule('string', $text, 'Слишком длинный или короткий текст шаблона!', true, 10, 65000);

			if ($validation->run()) {

				$text = no_br(str_replace('&#37;', '%', $text));

				$notice = DB::run() -> queryFetch("SELECT * FROM `notice` WHERE `notice_id`=? LIMIT 1;", array($id));

				if (empty($notice)) {

					DB::run() -> query("INSERT INTO `notice` (`notice_name`, `notice_text`, `notice_user`, `notice_time`) VALUES (?, ?, ?, ?);", array($name, $text, $log, SITETIME));
						$id = DB::run() -> lastInsertId();

				} else {

					DB::run() -> query("UPDATE `notice` SET `notice_name`=?, `notice_text`=?, `notice_user`=?, `notice_time`=? WHERE `notice_id`=?", array($name, $text, $log, SITETIME, $id));
				}

				$_SESSION['note'] = 'Шаблон успешно сохранен!';
				redirect("notice.php?act=edit&id=$id");

			} else {
				show_error($validation->errors);
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notice.php?act=edit&amp;id='.$id.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Удаление шаблона                                      ##
		############################################################################################
		case 'del':

			$uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;

			$notice = DB::run() -> queryFetch("SELECT * FROM `notice` WHERE `notice_id`=? LIMIT 1;", array($id));

			$validation = new Validation;

			$validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
				-> addRule('not_empty', $notice, 'Не найден шаблон для удаления!')
				-> addRule('empty', $notice['notice_protect'], 'Запрещено удалять защищенный шаблон!');

			if ($validation->run()) {

				DB::run() -> query("DELETE FROM `notice` WHERE `notice_id`=? LIMIT 1;", array($id));

				$_SESSION['note'] = 'Выбранный шаблон успешно удален!';
				redirect("notice.php");

			} else {
				show_error($validation->errors);
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notice.php">Вернуться</a><br />';
		break;


	default:
		redirect("notice.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
