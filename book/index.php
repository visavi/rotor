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
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Гостевая книга', 'Общение без ограничений');

switch ($act):
/**
 * Главная страница
 */
case 'index':

	$total = DBM::run()->count('guest');

	if ($total > 0 && $start >= $total) {
		$start = last_page($total, $config['bookpost']);
	}

	$page = floor(1 + $start / $config['bookpost']);
	$config['newtitle'] = 'Гостевая книга (Стр. '.$page.')';

	$posts = DBM::run()->select('guest', null, $config['bookpost'], $start, array('guest_time'=>'DESC'));

	render('book/index', array('posts' => $posts, 'start' => $start, 'total' => $total));

break;

/**
 * Добавление сообщения
 */
case 'add':

	$msg = check($_POST['msg']);
	$uid = check($_GET['uid']);

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if (utf_strlen($msg) >= 5 && utf_strlen($msg) < $config['guesttextlength']) {
				if (is_quarantine($log) || $config['bookadds'] == 1) {
					if (is_flood($log)) {

						$msg = smiles(antimat(no_br($msg)));

						$bookscores = ($config['bookscores']) ? 1 : 0;

						$user = DBM::run()->update('users', array(
							'users_allguest' => array(1),
							'users_point'    => array($bookscores),
							'users_money'    => array(5),
						), array(
							'users_login' => $log
						));

						$guest = DBM::run()->insert('guest', array(
							'guest_user' => $log,
							'guest_text' => $msg,
							'guest_ip'   => $ip,
							'guest_brow' => $brow,
							'guest_time' => SITETIME,
						));

						DBM::run()->execute("DELETE FROM `guest` WHERE `guest_time` < (SELECT MIN(`guest_time`) FROM (SELECT `guest_time` FROM `guest` ORDER BY `guest_time` DESC LIMIT :limit) AS del);", array('limit' => intval($config['maxpostbook'])));

						notice('Сообщение успешно добавлено!');
						redirect("index.php");

					} else {
						show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
					}
				} else {
					show_error('Карантин! Вы не можете писать в течении '.round($config['karantin'] / 3600).' часов!');
				}
			} else {
				show_error('Ошибка! Слишком длинное или короткое сообщение!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}

		/**
		 * Добавление для гостей
		 */
	} elseif ($config['bookadds'] == 1) {
		$provkod = check(strtolower($_POST['provkod']));

		if ($uid == $_SESSION['token']) {
			if ($provkod == $_SESSION['protect']) {
				if (utf_strlen($msg) >= 5 && utf_strlen($msg) < $config['guesttextlength']) {
					if (is_flood($log)) {

						$msg = smiles(antimat(no_br($msg)));

						$guest = DBM::run()->insert('guest', array(
							'guest_user' => $config['guestsuser'],
							'guest_text' => $msg,
							'guest_ip'   => $ip,
							'guest_brow' => $brow,
							'guest_time' => SITETIME,
						));

						DBM::run()->execute("DELETE FROM `guest` WHERE `guest_time` < (SELECT MIN(`guest_time`) FROM (SELECT `guest_time` FROM `guest` ORDER BY `guest_time` DESC LIMIT :limit) AS del);", array('limit' => intval($config['maxpostbook'])));

						notice('Сообщение успешно добавлено!');
						redirect("index.php");

					} else {
						show_error('Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');
					}
				} else {
					show_error('Ошибка! Слишком длинное или короткое сообщение!');
				}
			} else {
				show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

	render('includes/back', array('link' => 'index.php', 'title' => 'Вернуться'));
break;

/**
 * Жалоба на спам
 */
case 'spam':

	$uid = check($_GET['uid']);
	$id = abs(intval($_GET['id']));

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			$data = DBM::run()->selectFirst('guest', array('guest_id' => $id));

			if (! empty($data)) {

				$spam = DBM::run()->selectFirst('spam', array('spam_key' => 2, 'spam_idnum' => $id));

				if (empty($spam)) {
					if (is_flood($log)) {

						$spam = DBM::run()->insert('spam', array(
							'spam_key'     => 2,
							'spam_idnum'   => $data['guest_id'],
							'spam_user'    => $log,
							'spam_login'   => $data['guest_user'],
							'spam_text'    => $data['guest_text'],
							'spam_time'    => $data['guest_time'],
							'spam_addtime' => SITETIME,
							'spam_link'    => '/book/index.php?start='.$start,
						));

						notice('Жалоба успешно отправлена!');
						redirect("index.php?start=$start");

					} else {
						show_error('Антифлуд! Разрешается жаловаться на спам не чаще чем раз в '.flood_period().' секунд!');
					}
				} else {
					show_error('Ошибка! Жалоба на данное сообщение уже отправлена!');
				}
			} else {
				show_error('Ошибка! Выбранное вами сообщение для жалобы не существует!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы подать жалобу, необходимо');
	}

	render('includes/back', array('link' => 'index.php?start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Ответ на сообщение
 */
case 'reply':

	$id = abs(intval($_GET['id']));

	if (is_user()) {
		$post = DBM::run()->selectFirst('guest', array('guest_id' => $id));
		if (! empty($post)) {

			render ('book/reply', array('post' => $post));

		} else {
			show_error('Ошибка! Выбранное вами сообщение для ответа не существует!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы отвечать на сообщения, необходимо');
	}

	render('includes/back', array('link' => 'index.php?start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Цитирование сообщения
 */
case 'quote':

	$id = abs(intval($_GET['id']));

	if (is_user()) {
		$post = DBM::run()->selectFirst('guest', array('guest_id' => $id));

		if (!empty($post)) {
			$post['guest_text'] = preg_replace('|\[q\](.*?)\[/q\](<br />)?|', '', $post['guest_text']);
			$post['guest_text'] = yes_br(nosmiles($post['guest_text']));

			render ('book/quote', array('post' => $post));

		} else {
			show_error('Ошибка! Выбранное вами сообщение для цитирования не существует!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы цитировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'index.php?start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Подготовка к редактированию
 */
case 'edit':

	$id = abs(intval($_GET['id']));

	if (is_user()) {

		$post = DBM::run()->selectFirst('guest', array('guest_id' => $id, 'guest_user' =>$log));

		if (! empty($post)) {
			if ($post['guest_time'] + 600 > SITETIME) {

				$post['guest_text'] = yes_br(nosmiles($post['guest_text']));

				render('book/edit', array('post' => $post, 'id' => $id, 'start' => $start));

			} else {
				show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
			}
		} else {
			show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'index.php?start='.$start, 'title' => 'Вернуться'));
break;

/**
 * Редактирование сообщения
 */
case 'editpost':

	$uid = check($_GET['uid']);
	$id = abs(intval($_GET['id']));
	$msg = check($_POST['msg']);

	if (is_user()) {
		if ($uid == $_SESSION['token']) {
			if (utf_strlen($msg) >= 5 && utf_strlen($msg) < $config['guesttextlength']) {

				$post = DBM::run()->selectFirst('guest', array('guest_id' => $id, 'guest_user' =>$log));
				if (! empty($post)) {
					if ($post['guest_time'] + 600 > SITETIME) {
						$msg = smiles(antimat(no_br($msg)));

						$guest = DBM::run()->update('guest', array(
							'guest_text'      => $msg,
							'guest_edit'      => $log,
							'guest_edit_time' => SITETIME,
						), array(
							'guest_id' => $id
						));

						notice('Сообщение успешно отредактировано!');
						redirect("index.php?start=$start");

					} else {
						show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
					}
				} else {
					show_error('Ошибка! Сообщение удалено или вы не автор этого сообщения!');
				}
			} else {
				show_error('Ошибка! Слишком длинное или короткое сообщение!');
			}
		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы редактировать сообщения, необходимо');
	}

	render('includes/back', array('link' => 'index.php?act=edit&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться'));
	render('includes/back', array('link' => 'index.php?start='.$start, 'title' => 'В гостевую', 'icon' => 'reload.gif'));
break;

default:
	redirect("index.php");
endswitch;

include_once ('../themes/footer.php');
?>
