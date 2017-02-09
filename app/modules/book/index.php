<?php

switch ($act):
/**
 * Главная страница
 */
case 'index':

    $total = Guest::count();
    $page = App::paginate(App::setting('bookpost'), $total);

    $posts = Guest::orderBy('time', 'desc')
        ->limit(App::setting('bookpost'))
        ->offset($page['offset'])
        ->with('user')
        ->get();

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

            $user = User::where('id', App::getUserId());
            $user->update([
                'allguest' => Capsule::raw('allguest + 1'),
                'point' => Capsule::raw('point + 1'),
                'money' => Capsule::raw('money + 5'),
            ]);
        }

        $username = is_user() ? App::getUserId() : 0;

        $guest = new Guest();
        $guest->user_id = $username;
        $guest->text = $msg;
        $guest->ip = App::getClientIp();
        $guest->brow = App::getUserAgent();
        $guest->time = SITETIME;
        $guest->save();

        Capsule::delete('
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

    $post = Guest::where('user_id', App::getUserId())->find($id);

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

            $post->text = $msg;
            $post->edit = $log;
            $post->edit_time = SITETIME;
            $post->save();

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

    $data = Guest::find($id);
    $validation->addRule('custom', $data, 'Выбранное вами сообщение для жалобы не существует!');

    $spam = Spam::where(['relate' => 2, 'idnum' => $id])->first();
    $validation->addRule('custom', !$spam, 'Жалоба на данное сообщение уже отправлена!');

    if ($validation->run()) {

        $spam = new Spam();
        $spam->relate  = 2;
        $spam->idnum   = $data['id'];
        $spam->user    = App::getUsername();
        $spam->login   = $data['user_id'];
        $spam->text    = $data['text'];
        $spam->time    = $data['time'];
        $spam->addtime = SITETIME;
        $spam->link    = '/book?page='.$page;
        $spam->save();

        exit(json_encode(['status' => 'success']));
    } else {
        exit(json_encode(['status' => 'error', 'message' => current($validation->getErrors())]));
    }
    break;
endswitch;
