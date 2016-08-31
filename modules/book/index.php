<?php

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

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


	App::view('book/index', compact('posts', 'start', 'total'));
break;

/**
 * Добавление сообщения
 */
case 'add':

	$msg = check($_POST['msg']);
	$uid = check($_GET['uid']);

    $validation = new Validation();

    $validation->addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('string', $msg, 'Ошибка! Слишком длинное или короткое сообщение!', true, 5, $config['guesttextlength'])
        ->addRule('bool', is_user(), 'Для добавления сообщения необходимо авторизоваться')
        ->addRule('bool', is_flood($log), 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!');

    /* Проерка для гостей */
    if (! is_user() && $config['bookadds']) {
        $provkod = check(strtolower($_POST['provkod']));

        $validation->addRule('bool', is_user(), 'Для добавления сообщения необходимо авторизоваться');
        $validation->addRule('equal', [$provkod, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!');
    }

    if ($validation->run()) {

        $msg = antimat($msg);

        if (is_user()) {
            $bookscores = ($config['bookscores']) ? 1 : 0;

            $user = DBM::run()->update('users', array(
                'users_allguest' => array('+', 1),
                'users_point' => array('+', $bookscores),
                'users_money' => array('+', 5),
            ), array(
                'users_login' => $log
            ));
        }

        $username = is_user() ? $log : $config['guestsuser'];

        $guest = DBM::run()->insert('guest', array(
            'guest_user' => $username,
            'guest_text' => $msg,
            'guest_ip'   => $ip,
            'guest_brow' => $brow,
            'guest_time' => SITETIME,
        ));

        DBM::run()->execute("DELETE FROM `guest` WHERE `guest_time` < (SELECT MIN(`guest_time`) FROM (SELECT `guest_time` FROM `guest` ORDER BY `guest_time` DESC LIMIT :limit) AS del);", array('limit' => intval($config['maxpostbook'])));

        App::setFlash('success', 'Сообщение успешно добавлено!');
    } else {
        App::setInput($_POST);
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect("/book");

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
 * Подготовка к редактированию
 */
case 'edit':

	$id = abs(intval($_GET['id']));

	if (is_user()) {

		$post = DBM::run()->selectFirst('guest', array('guest_id' => $id, 'guest_user' =>$log));

		if (! empty($post)) {
			if ($post['guest_time'] + 600 > SITETIME) {

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
						$msg = antimat($msg);

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
endswitch;
