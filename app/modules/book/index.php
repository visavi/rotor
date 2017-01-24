<?php

switch ($act):
/**
 * Главная страница
 */
case 'index':
    $total = ORM::forTable('guest')->count();
    $page = App::paginate(App::setting('bookpost'), $total);

    $posts = ORM::forTable('guest')
        ->order_by_desc('time')
        ->limit(App::setting('bookpost'))
        ->offset($page['offset'])
        ->findMany();

    App::view('book/index', compact('posts', 'page'));
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
        $protect = check(strtolower(Request::input('protect')));
        $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!']);
    } else {
        $validation->addRule('bool', is_user(), ['msg' => 'Для добавления сообщения необходимо авторизоваться']);
    }

    if ($validation->run()) {

        $msg = antimat($msg);


        if (is_user()) {
            $bookscores = (App::setting('bookscores')) ? 1 : 0;

            $user = ORM::forTable('users')->where('login', App::getUsername())->findOne();
            $user->allguest++;
            $user->point++;
            $user->money += 5;
            $user->save();
        }

        $username = is_user() ? App::getUsername() : App::setting('guestsuser');

        $guest = ORM::forTable('guest')->create();
        $guest->user = $username;
        $guest->text = $msg;
        $guest->ip = App::getClientIp();
        $guest->brow = App::getUserAgent();
        $guest->time = SITETIME;
        $guest->save();

        ORM::forTable('guest')->rawExecute('
            DELETE FROM guest WHERE time < (
                SELECT MIN(time) FROM (
                    SELECT time FROM guest ORDER BY time DESC LIMIT '.App::setting('maxpostbook').'
                ) AS del
            );'
        );

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
    $id = param('id');

    if (! is_user()) App::abort(403);

    $post = DBM::run()->selectFirst('guest', ['id' => $id, 'user' =>$log]);

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

            $guest = DBM::run()->update('guest', [
                'text'      => $msg,
                'edit'      => $log,
                'edit_time' => SITETIME,
            ], [
                'id' => $id
            ]);

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
    $page = abs(intval(Request::input('page')));
    $id = abs(intval(Request::input('id')));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться');

    $data = DBM::run()->selectFirst('guest', ['id' => $id]);
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');


    $spam = DBM::run()->selectFirst('spam', ['relate' => 2, 'idnum' => $id]);
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {
        $spam = DBM::run()->insert('spam', [
            'relate'     => 2,
            'idnum'   => $data['id'],
            'user'    => $log,
            'login'   => $data['user'],
            'text'    => $data['text'],
            'time'    => $data['time'],
            'addtime' => SITETIME,
            'link'    => '/book?page='.$page,
        ]);

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
    break;
endswitch;
