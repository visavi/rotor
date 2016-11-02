<?php

$uz = isset($params['login']) ? check($params['login']) : check($log);

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    if (! $user = user($uz)) App::abort('default', 'Пользователя с данным логином не существует!');

    App::view('pages/user', compact('user'));
break;

############################################################################################
##                                      Редактирование                                    ##
############################################################################################
case 'note':

    if (! is_admin()) App::abort(403, 'Данная страница доступна только администрации!');
    if (! user($uz)) App::abort('default', 'Пользователя с данным логином не существует!');

    if (Request::isMethod('post')) {

        $note = check(Request::input('note'));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['note' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $note, ['note' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

        if ($validation->run()) {

            DB::run()->query("INSERT INTO `note` (`note_user`, `note_text`, `note_edit`, `note_time`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `note_text`=?, `note_edit`=?, `note_time`=?;", array($uz, $note, $log, SITETIME, $note, $log, SITETIME));

            App::setFlash('success', 'Заметка успешно сохранена!');
            App::redirect('/user/'.$uz);

        } else {
            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }
    }

    $note = DBM::run()->selectFirst('note', ['note_user' => $uz]);

    App::view('pages/user_note', compact('note', 'uz'));
break;

endswitch;
