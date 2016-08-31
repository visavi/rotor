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

if (is_admin(array(101, 102, 103))) {
	show_title('Управление антиматом');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case "index":

			echo 'Все слова в списке будут заменяться на ***<br />';
			echo 'Чтобы удалить слово нажмите на него, добавить слово можно в форме ниже<br /><br />';

			$querymat = DB::run() -> query("SELECT * FROM antimat;");
			$arrmat = $querymat -> fetchAll();
			$total = count($arrmat);

			if ($total > 0) {
				foreach($arrmat as $key => $value) {
					if ($key == 0) {
						$comma = '';
					} else {
						$comma = ', ';
					}
					echo $comma.'<a href="antimat.php?act=del&amp;id='.$value['mat_id'].'&amp;uid='.$_SESSION['token'].'">'.$value['mat_string'].'</a>';
				}

				echo '<br /><br />';
			} else {
				show_error('Список пуст, добавьте слово!');
			}

			echo '<div class="b">';
			echo 'Добавить слово:<br />';
			echo '<form action="antimat.php?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
			echo '<input type="text" name="mat" />';
			echo '<input type="submit" value="Добавить" /></form></div><br />';

			echo 'Всего слов в базе: <b>'.$total.'</b><br /><br />';

			if (is_admin(array(101)) && $total > 0) {
				echo '<img src="/images/img/error.gif" alt="image" /> <a href="antimat.php?act=prodel">Очистить</a><br />';
			}
		break;

		############################################################################################
		##                                Добавление в список                                     ##
		############################################################################################
		case "add":

			$uid = check($_GET['uid']);
			$mat = check(utf_lower($_POST['mat']));

			if ($uid == $_SESSION['token']) {
				if (!empty($mat)) {
					$querymat = DB::run() -> querySingle("SELECT mat_id FROM antimat WHERE mat_string=? LIMIT 1;", array($mat));
					if (empty($querymat)) {
						DB::run() -> query("INSERT INTO antimat (mat_string) VALUES (?);", array($mat));

						$_SESSION['note'] = 'Слово успешно добавлено в список антимата!';
						redirect("antimat.php");

					} else {
						show_error('Ошибка! Введенное слово уже имеетеся в списке!');
					}
				} else {
					show_error('Ошибка! Вы не ввели слово для занесения в список!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="antimat.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Удаление из списка                                   ##
		############################################################################################
		case "del":

			$uid = check($_GET['uid']);
			$id = intval($_GET['id']);

			if ($uid == $_SESSION['token']) {
				if (!empty($id)) {
					DB::run() -> query("DELETE FROM antimat WHERE mat_id=?;", array($id));

					$_SESSION['note'] = 'Слово успешно удалено из списка антимата!';
					redirect("antimat.php");

				} else {
					show_error('Ошибка удаления! Отсутствуют выбранное слово!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="antimat.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Подтверждение очистки                                  ##
		############################################################################################
		case "prodel":

			echo 'Вы уверены что хотите удалить все слова в антимате?<br />';
			echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="antimat.php?act=clear&amp;uid='.$_SESSION['token'].'">Да уверен!</a></b><br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="antimat.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Очистка антимата                                    ##
		############################################################################################
		case "clear":

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					DB::run() -> query("DELETE FROM antimat;");

					$_SESSION['note'] = 'Список антимата успешно очищен!';
					redirect("antimat.php");

				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Очищать гостевую могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="antimat.php">Вернуться</a><br />';
		break;

	default:
		redirect("antimat.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
