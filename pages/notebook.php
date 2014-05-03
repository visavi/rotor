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

show_title('Блокнот');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":
			$note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `note_user`=? LIMIT 1;", array($log));

			echo 'Здесь вы можете хранить отрывки сообщений или любую другую важную информацию<br /><br />';

			if (!empty($note['note_text'])) {
				echo '<div>Личная запись:<br />';
				echo bb_code($note['note_text']).'</div><br />';

				echo 'Последнее изменение: '.date_fixed($note['note_time']).'<br /><br />';
			} else {
				show_error('Запись пустая или отсутствует!');
			}

			echo '<img src="/images/img/edit.gif" alt="image" /> <a href="notebook.php?act=edit">Редактировать</a><br />';
		break;

		############################################################################################
		##                                   Редактирование записи                                ##
		############################################################################################
		case "edit":

			$note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `note_user`=? LIMIT 1;", array($log));

			$note['note_text'] = nosmiles($note['note_text']);
			$note['note_text'] = str_replace('<br />', "\r\n", $note['note_text']);

			echo '<div class="form">';
			echo '<form action="notebook.php?act=change&amp;uid='.$_SESSION['token'].'" method="post">';
			echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$note['note_text'].'</textarea><br />';
			echo '<input type="submit" value="Сохранить" /></form></div><br />';

			echo '* Доступ к личной записи не имеет никто кроме вас<br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notebook.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Сохранение записи                                   ##
		############################################################################################
		case "change":

			$uid = check($_GET['uid']);
			$msg = check($_POST['msg']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($msg) < 10000) {
					$msg = no_br($msg);
					$msg = smiles($msg);

					$querynote = DB::run() -> querySingle("SELECT `note_id` FROM `notebook` WHERE `note_user`=? LIMIT 1;", array($log));
					if (!empty($querynote)) {
						DB::run() -> query("UPDATE `notebook` SET `note_text`=?, `note_time`=? WHERE `note_user`=?", array($msg, SITETIME, $log));
					} else {
						DB::run() -> query("INSERT INTO `notebook` (`note_user`, `note_text`, `note_time`) VALUES (?, ?, ?);", array($log, $msg, SITETIME));
					}

					$_SESSION['note'] = 'Запись успешно сохранена!';
					redirect("notebook.php");
				} else {
					show_error('Ошибка! Слишком длинная запись, не более 10тыс. символов!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="notebook.php?act=edit">Вернуться</a><br />';
		break;

	default:
		redirect("notebook.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, чтобы сохранять заметки, необходимо');
}

include_once ('../themes/footer.php');
?>
