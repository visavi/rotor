<?php

$user = check(param('login', $log));

switch ($act):
/**
 * Главная страница
 */
case 'index':

    if (! $user = user($user)) {
        App::abort('default', 'Пользователя с данным логином не существует!');
    }

    App::view('pages/user', compact('user'));
break;

/**
 * Заметка
 */
case 'note':

    if (! is_admin()) App::abort(403, 'Данная страница доступна только администрации!');
    if (! user($user)) App::abort('default', 'Пользователя с данным логином не существует!');

    $note = Note::where('user', $user)->first();

    if (Request::isMethod('post')) {

        $notice = check(Request::input('notice'));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['notice' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $notice, ['notice' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

        if ($validation->run()) {

            $record = [
                'user' => $user,
                'text' => $notice,
                'edit' => $log,
                'time' => SITETIME,
            ];

            Note::saveNote($note, $record);

            App::setFlash('success', 'Заметка успешно сохранена!');
            App::redirect('/user/'.$user);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    App::view('pages/user_note', compact('note', 'user'));
break;

endswitch;
