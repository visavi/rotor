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
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_admin(array(101, 102, 103))) {
	show_title('Управление голосованием');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time` DESC;", array(0));
			$votes = $queryvote -> fetchAll();

			if (count($votes) > 0) {
				foreach($votes as $valvote) {
					echo '<div class="b">';
					echo '<img src="/images/img/stat.gif" alt="image" /> <b><a href="/votes/index.php?act=poll&amp;id='.$valvote['vote_id'].'">'.$valvote['vote_title'].'</a></b><br />';
					echo '<a href="votes.php?act=edit&amp;id='.$valvote['vote_id'].'">Изменить</a>';
					echo ' / <a href="votes.php?act=action&amp;do=close&amp;id='.$valvote['vote_id'].'&amp;uid='.$_SESSION['token'].'">Закрыть</a>';

					if (is_admin(array(101))) {
						echo ' / <a href="votes.php?act=del&amp;id='.$valvote['vote_id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
					}

					echo '</div>';

					echo '<div>Создано: '.date_fixed($valvote['vote_time']).'<br />';
					echo 'Всего голосов: '.$valvote['vote_count'].'</div>';
				}
				echo '<br />';
			} else {
				show_error('Открытых голосований еще нет!');
			}

			echo '<img src="/images/img/stat.gif" alt="image" /> <a href="votes.php?act=new">Создать голосование</a><br />';
			echo '<img src="/images/img/luggage.gif" alt="image" /> <a href="votes.php?act=history">История голосований</a><br />';

			if (is_admin(array(101))) {
				echo '<img src="/images/img/reload.gif" alt="image" /> <a href="votes.php?act=rest&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
			}

		break;

		############################################################################################
		##                                      Создание                                          ##
		############################################################################################
		case 'new':

			echo '<div class="form">';
			echo '<form action="votes.php?act=add&amp;uid='.$_SESSION['token'].'" method="post">';

			echo 'Вопрос:<br />';
			echo '<input type="text" name="title" size="50" maxlength="100" /><br />';
			echo 'Ответ 1:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 2:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 3:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 4:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 5:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 6:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 7:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 8:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 9:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo 'Ответ 10:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
			echo '<input type="submit" value="Создать" /></form></div><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                      Создание                                          ##
		############################################################################################
		case 'add':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$answer = check($_POST['answer']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 3 && utf_strlen($title) <= 100) {
					$answer = array_diff($answer, array(''));

					if (count($answer) > 0) {
						DB::run() -> query("INSERT INTO `vote` (`vote_title`, `vote_time`) VALUES (?, ?);", array($title, SITETIME));
						$lastid = DB::run() -> lastInsertId();

						$dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`answer_vote_id`, `answer_option`) VALUES (?, ?);");

						foreach ($answer as $data) {
							$dbr -> execute($lastid, $data);
						}

						$_SESSION['note'] = 'Голосование успешно создано!';
						redirect("votes.php");
					} else {
						show_error('Ошибка! Отсутствуют варианты ответов!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php?act=new">Вернуться</a><br />';
			echo '<img src="/images/img/stat.gif" alt="image" /> <a href="votes.php">К голосованиям</a><br />';
		break;

		############################################################################################
		##                                   Редактирование                                       ##
		############################################################################################
		case 'edit':

			$votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));

			if (!empty($votes)) {
				echo '<div class="form">';
				echo '<form action="votes.php?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

				echo 'Вопрос:<br />';
				echo '<input type="text" name="title" size="50" maxlength="100" value="'.$votes['vote_title'].'" /><br />';

				$queryanswer = DB::run() -> query("SELECT * FROM `voteanswer` WHERE `answer_vote_id`=? ORDER BY `answer_id`;", array($id));
				$answer = $queryanswer -> fetchAll();

				for ($i = 0; $i < 10; $i++) {
					if (!empty($answer[$i])) {
						echo '<span style="color:#ff0000">Ответ '.($i + 1).':</span><br /><input type="text" name="answer['.$answer[$i]['answer_id'].']" maxlength="50" value="'.$answer[$i]['answer_option'].'" /><br />';
					} else {
						echo 'Ответ '.($i + 1).':<br /><input type="text" name="newanswer[]" maxlength="50" /><br />';
					}
				}

				echo '<input type="submit" value="Изменить" /></form></div><br />';

				echo 'Поля отмеченные красным цветом обязательны для заполнения!<br /><br />';
			} else {
				show_error('Ошибка! Данного голосования не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Редактирование                                       ##
		############################################################################################
		case 'change':

			$uid = check($_GET['uid']);
			$title = check($_POST['title']);
			$answer = check($_POST['answer']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($title) >= 3 && utf_strlen($title) <= 100) {
					$queryvote = DB::run() -> querySingle("SELECT `vote_id` FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));
					if (!empty($queryvote)) {
						if (!in_array('', $answer)) {
							DB::run() -> query("UPDATE `vote` SET `vote_title`=? WHERE `vote_id`=?;", array($title, $id));

							$dbr = DB::run() -> prepare("UPDATE `voteanswer` SET `answer_option`=? WHERE `answer_id`=?;");
							foreach ($answer as $key => $data) {
								$dbr -> execute($data, $key);
							}

							if (isset($_POST['newanswer'])) {
								$newanswer = check($_POST['newanswer']);
								$newanswer = array_diff($newanswer, array(''));
								if (count($newanswer) > 0) {
									$dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`answer_vote_id`, `answer_option`) VALUES (?, ?);");
									foreach ($newanswer as $data) {
										$dbr -> execute($id, $data);
									}
								}
							}

							$_SESSION['note'] = 'Голосование успешно изменено!';
							redirect("votes.php");
						} else {
							show_error('Ошибка! Не заполнены все обязательные поля с ответами!');
						}
					} else {
						show_error('Ошибка! Данного голосования не существует!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php?act=edit&amp;id='.$id.'">Вернуться</a><br />';
			echo '<img src="/images/img/stat.gif" alt="image" /> <a href="votes.php">К голосованиям</a><br />';
		break;

		############################################################################################
		##                                      Закрытие                                          ##
		############################################################################################
		case 'action':

			$uid = check($_GET['uid']);
			$do = check($_GET['do']);

			if ($uid == $_SESSION['token']) {
				if ($do == 'close' || $do == 'open') {
					$queryvote = DB::run() -> querySingle("SELECT `vote_id` FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));
					if (!empty($queryvote)) {
						if ($do == 'close') {
							DB::run() -> query("UPDATE `vote` SET `vote_closed`=? WHERE `vote_id`=?;", array(1, $id));
							DB::run() -> query("DELETE FROM `votepoll` WHERE `poll_vote_id`=?;", array($id));
							$_SESSION['note'] = 'Голосование успешно закрыто!';
							redirect("votes.php");
						}

						if ($do == 'open') {
							DB::run() -> query("UPDATE `vote` SET `vote_closed`=? WHERE `vote_id`=?;", array(0, $id));
							$_SESSION['note'] = 'Голосование успешно открыто!';
							redirect("votes.php?act=history");
						}
					} else {
						show_error('Ошибка! Данного голосования не существует!');
					}
				} else {
					show_error('Ошибка! Не выбрано действие для голосования!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                      Удаление                                          ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);

			if ($uid == $_SESSION['token']) {
				if (is_admin(array(101))) {
					$queryvote = DB::run() -> querySingle("SELECT `vote_id` FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));
					if (!empty($queryvote)) {
						DB::run() -> query("DELETE FROM `vote` WHERE `vote_id`=?;", array($id));
						DB::run() -> query("DELETE FROM `voteanswer` WHERE `answer_vote_id`=?;", array($id));
						DB::run() -> query("DELETE FROM `votepoll` WHERE `poll_vote_id`=?;", array($id));

						$_SESSION['note'] = 'Голосование успешно удалено!';
						redirect("votes.php");
					} else {
						show_error('Ошибка! Данного голосования не существует!');
					}
				} else {
					show_error('Ошибка! Удалять голосования могут только суперадмины!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Пересчет счетчиков                                  ##
		############################################################################################
		case 'rest':
			$uid = check($_GET['uid']);
			if ($uid == $_SESSION['token']) {
				if (is_admin(array(101))) {
					DB::run() -> query("UPDATE `vote` SET `vote_count`=(SELECT SUM(`answer_result`) FROM `voteanswer` WHERE `vote`.vote_id=`voteanswer`.`answer_vote_id`) WHERE `vote_closed`=?;", array(0));

					$_SESSION['note'] = 'Все данные успешно пересчитаны!';
					redirect("votes.php");
				} else {
					show_error('Ошибка! Пересчитывать голосования могут только суперадмины!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="votes.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                          История                                      ##
		############################################################################################
		case 'history':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time`;", array(1));

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time` DESC LIMIT ".$start.", ".$config['allvotes'].";", array(1));

				while ($data = $queryvote -> fetch()) {
					echo '<div class="b">';
					echo '<img src="/images/img/luggage.gif" alt="image" /> <b><a href="/votes/history.php?act=result&amp;id='.$data['vote_id'].'&amp;start='.$start.'">'.$data['vote_title'].'</a></b><br />';

					echo '<a href="votes.php?act=action&amp;do=open&amp;id='.$data['vote_id'].'&amp;uid='.$_SESSION['token'].'">Открыть</a>';

					if (is_admin(array(101))) {
						echo ' / <a href="votes.php?act=del&amp;id='.$data['vote_id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
					}

					echo '</div>';
					echo '<div>Создано: '.date_fixed($data['vote_time']).'<br />';
					echo 'Всего голосов: '.$data['vote_count'].'</div>';
				}

				page_strnavigation('votes.php?act=history&amp;', $config['allvotes'], $start, $total);
			} else {
				show_error('Голосований в архиве еще нет!');
			}

			echo '<img src="/images/img/stat.gif" alt="image" /> <a href="votes.php">Список голосований</a><br />';
		break;

	default:
		redirect("votes.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
