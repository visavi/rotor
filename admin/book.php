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
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}

if (is_admin()) {
	show_title('Управление гостевой');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo '<a href="#down"><img src="/images/img/downs.gif" alt="image" /></a> ';
			echo '<a href="book.php?rand='.mt_rand(100, 999).'">Обновить</a> / ';
			echo '<a href="/book/index.php?start='.$start.'">Обзор</a><br /><hr />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM guest;");

			if ($total > 0) {
				if ($start >= $total) {
					$start = 0;
				}

				$queryguest = DB::run() -> query("SELECT * FROM guest ORDER BY guest_time DESC LIMIT ".$start.", ".$config['bookpost'].";");

				echo '<form action="book.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				while ($data = $queryguest -> fetch()) {

					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['guest_user']).'</div>';

					echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['guest_id'].'" /></span>';

					if ($data['guest_user'] == $config['guestsuser']) {
						echo '<b>'.$data['guest_user'].'</b> <small>('.date_fixed($data['guest_time']).')</small>';
					} else {
						echo '<b>'.profile($data['guest_user']).'</b> <small>('.date_fixed($data['guest_time']).')</small><br />';
						echo user_title($data['guest_user']).' '.user_online($data['guest_user']);
					}

					echo '</div>';

					echo '<div class="right">';
					echo '<a href="book.php?act=edit&amp;id='.$data['guest_id'].'&amp;start='.$start.'">Редактировать</a> / ';
					echo '<a href="book.php?act=reply&amp;id='.$data['guest_id'].'&amp;start='.$start.'">Ответить</a></div>';

					echo '<div>'.bb_code($data['guest_text']).'<br />';

					if (!empty($data['guest_edit'])) {
						echo '<img src="/images/img/exclamation_small.gif" alt="image" /> <small>Отредактировано: '.nickname($data['guest_edit']).' ('.date_fixed($data['guest_edit_time']).')</small><br />';
					}

					echo '<span class="data">('.$data['guest_brow'].', '.$data['guest_ip'].')</span>';

					if (!empty($data['guest_reply'])) {
						echo '<br /><span style="color:#ff0000">Ответ: '.$data['guest_reply'].'</span>';
					}

					echo '</div>';
				}
				echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';

				page_strnavigation('book.php?', $config['bookpost'], $start, $total);

				echo 'Всего сообщений: <b>'.(int)$total.'</b><br /><br />';

				if (is_admin(array(101))) {
					echo '<img src="/images/img/error.gif" alt="image" /> <a href="book.php?act=prodel">Очистить</a><br />';
				}
			} else {
				show_error('Сообщений еще нет!');
			}
		break;

		############################################################################################
		##                                        Ответ                                           ##
		############################################################################################
		case 'reply':

			$data = DB::run() -> queryFetch("SELECT * FROM guest WHERE guest_id=? LIMIT 1;", array($id));

			if (!empty($data)) {
				$data['guest_reply'] = yes_br(nosmiles($data['guest_reply']));

				echo '<b><big>Добавление ответа</big></b><br /><br />';

				echo '<div class="b"><img src="/images/img/edit.gif" alt="image" /> <b>'.profile($data['guest_user']).'</b> '.user_title($data['guest_user']) . user_online($data['guest_user']).' <small>('.date_fixed($data['guest_time']).')</small></div>';
				echo '<div>Сообщение: '.bb_code($data['guest_text']).'</div><hr />';

				echo '<div class="form">';
				echo '<form action="book.php?id='.$id.'&amp;act=addreply&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Cообщение:<br />';
				echo '<textarea cols="25" rows="5" name="reply">'.$data['guest_reply'].'</textarea>';
				echo '<br /><input type="submit" value="Ответить" /></form></div><br />';
			} else {
				show_error('Ошибка! Сообщения для ответа не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                  Добавление ответа                                     ##
		############################################################################################
		case 'addreply':

			$uid = check($_GET['uid']);
			$reply = check($_POST['reply']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen($reply) >= 5 && utf_strlen($reply) < $config['guesttextlength']) {
					$queryguest = DB::run() -> querySingle("SELECT guest_id FROM guest WHERE guest_id=? LIMIT 1;", array($id));
					if (!empty($queryguest)) {
						$reply = no_br($reply);
						$reply = smiles($reply);

						DB::run() -> query("UPDATE guest SET guest_reply=? WHERE guest_id=?", array($reply, $id));

						$_SESSION['note'] = 'Ответ успешно добавлен!';
						redirect("book.php?start=$start");
					} else {
						show_error('Ошибка! Сообщения для ответа не существует!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий текст ответа!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php?act=reply&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="book.php?start='.$start.'">В гостевую</a><br />';
		break;

		############################################################################################
		##                                    Редактирование                                      ##
		############################################################################################
		case 'edit':

			$data = DB::run() -> queryFetch("SELECT * FROM guest WHERE guest_id=? LIMIT 1;", array($id));

			if (!empty($data)) {
				$data['guest_text'] = yes_br(nosmiles($data['guest_text']));

				echo '<b><big>Редактирование сообщения</big></b><br /><br />';

				echo '<img src="/images/img/edit.gif" alt="image" /> <b>'.nickname($data['guest_user']).'</b> <small>('.date_fixed($data['guest_time']).')</small><br /><br />';

				echo '<div class="form">';
				echo '<form action="book.php?act=addedit&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
				echo 'Cообщение:<br />';
				echo '<textarea cols="50" rows="5" name="msg">'.$data['guest_text'].'</textarea><br /><br />';
				echo '<input type="submit" value="Изменить" /></form></div><br />';
			} else {
				show_error('Ошибка! Сообщения для редактирования не существует!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Изменение сообщения                                    ##
		############################################################################################
		case 'addedit':

			$uid = check($_GET['uid']);
			$msg = check($_POST['msg']);

			if ($uid == $_SESSION['token']) {
				if (utf_strlen(trim($msg)) >= 5 && utf_strlen($msg) < $config['guesttextlength']) {
					$queryguest = DB::run() -> querySingle("SELECT guest_id FROM guest WHERE guest_id=? LIMIT 1;", array($id));
					if (!empty($queryguest)) {
						$msg = no_br($msg);
						$msg = smiles($msg);

						DB::run() -> query("UPDATE guest SET guest_text=?, guest_edit=?, guest_edit_time=? WHERE guest_id=?", array($msg, $log, SITETIME, $id));

						$_SESSION['note'] = 'Сообщение успешно отредактировано!';
						redirect("book.php?start=$start");
					} else {
						show_error('Ошибка! Сообщения для редактирования не существует!');
					}
				} else {
					show_error('Ошибка! Слишком длинный или короткий текст сообщения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Удаление сообщений                                     ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if (!empty($del)) {
					$del = implode(',', $del);

					DB::run() -> query("DELETE FROM guest WHERE guest_id IN (".$del.");");

					$_SESSION['note'] = 'Выбранные сообщения успешно удалены!';
					redirect("book.php?start=$start");
				} else {
					show_error('Ошибка! Отсутствуют выбранные сообщения!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                 Подтверждение очистки                                  ##
		############################################################################################
		case 'prodel':
			echo 'Вы уверены что хотите удалить все сообщения в гостевой?<br />';
			echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="book.php?act=alldel&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Очистка гостевой                                     ##
		############################################################################################
		case 'alldel':

			$uid = check($_GET['uid']);

			if (is_admin(array(101))) {
				if ($uid == $_SESSION['token']) {
					DB::run() -> query("DELETE FROM guest;");

					$_SESSION['note'] = 'Гостевая книга успешно очищена!';
					redirect("book.php");
				} else {
					show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
				}
			} else {
				show_error('Ошибка! Очищать гостевую могут только суперадмины!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="book.php">Вернуться</a><br />';
		break;

	default:
		redirect("book.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
