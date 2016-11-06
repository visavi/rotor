<?php

$start = abs(intval(Request::input('start', 0)));

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

    $posts = DBM::run()->select('guest', null, $config['bookpost'], $start, array('time'=>'DESC'));

    App::view('book/index', compact('posts', 'start', 'total', 'page'));
break;

/**
 * Добавление сообщения
 */
case 'add':

    $msg = check(Request::input('msg'));
    $token = check(Request::input('token'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
        ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, $config['guesttextlength'])
        ->addRule('bool', is_flood($log), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!']);

    /* Проерка для гостей */
    if (! is_user() && $config['bookadds']) {
        $protect = check(Request::input('protect'));
        $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!']);
    } else {
        $validation->addRule('bool', is_user(), ['msg' => 'Для добавления сообщения необходимо авторизоваться']);
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
            'user' => $username,
            'text' => $msg,
            'ip'   => App::getClientIp(),
            'brow' => App::getUserAgent(),
            'time' => SITETIME,
        ));

        DBM::run()->execute("DELETE FROM `guest` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `guest` ORDER BY `time` DESC LIMIT :limit) AS del);", array('limit' => intval($config['maxpostbook'])));

        App::setFlash('success', 'Сообщение успешно добавлено!');
    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }

    App::redirect("/book");
break;

/**
 * Подготовка к редактированию
 */
case 'edit':
    $id  = isset($params['id']) ? abs(intval($params['id'])) : 0;

    if (! is_user()) App::abort(403);

    $post = DBM::run()->selectFirst('guest', array('id' => $id, 'user' =>$log));

    if (! $post) {
        App::abort('default', 'Ошибка! Сообщение удалено или вы не автор этого сообщения!');
    }

    if ($post['time'] + 600 < SITETIME) {
        App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
    }

    if (Request::isMethod('post')) {

        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $msg, ['msg' => 'Ошибка! Слишком длинное или короткое сообщение!'], true, 5, $config['guesttextlength']);

        if ($validation->run()) {

            $msg = antimat($msg);

            $guest = DBM::run()->update('guest', array(
                'text'      => $msg,
                'edit'      => $log,
                'edit_time' => SITETIME,
            ), array(
                'id' => $id
            ));

            App::setFlash('success', 'Сообщение успешно отредактировано!');
            App::redirect('/book');
        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('book/edit', compact('post', 'id'));
break;

/**
 * Жалоба
 */
case 'complaint':
    if (! Request::ajax()) App::redirect('/');

    $token = check(Request::input('token'));
    $id = abs(intval(Request::input('id')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = DBM::run()->selectFirst('guest', array('id' => $id));
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');


    $spam = DBM::run()->selectFirst('spam', array('spam_key' => 2, 'spam_idnum' => $id));
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = DBM::run()->insert('spam', array(
            'spam_key'     => 2,
            'spam_idnum'   => $data['id'],
            'spam_user'    => $log,
            'spam_login'   => $data['user'],
            'spam_text'    => $data['text'],
            'spam_time'    => $data['time'],
            'spam_addtime' => SITETIME,
            'spam_link'    => '/book?start='.$start,
        ));

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
    break;
endswitch;
