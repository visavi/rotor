<?php
//show_title('История голосований '.$uz);

$login = check(param('login'));

if (! is_user()) {
    App::abort(403, 'Для просмотра истории небходимо авторизоваться!');
}

$user = User::where('login', $login)->first();

if (! $user) {
    App::abort('default', 'Данного пользователя не существует!');
}

switch ($act):
/**
 *  Полученные голоса
 */
case 'received':
    $ratings = Rating::where('recipient_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->with('user')
        ->get();

    App::view('pages/rathistory', compact('ratings', 'user'));
break;

/**
 *  Отданные голоса
 */
case 'gave':
    $ratings = Rating::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->with('recipient')
        ->get();

    App::view('pages/rathistory_gave', compact('ratings', 'user'));
break;

############################################################################################
##                                     Удаление истории                                   ##
############################################################################################
case 'delete':

    $id    = abs(intval(Request::input('id')));
    $token = check(Request::input('token'));

    if (! Request::ajax()) {
        redirect('/');
    }

    $validation = new Validation();
    $validation
        ->addRule('bool', is_admin([101]), 'Удалять рейтинг могут только администраторы')
        ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('not_empty', $id, ['Не выбрана запись для удаление!']);

    if ($validation->run()) {

        Rating::find($id)->delete();

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => current($validation->getErrors())
        ]);
    }
break;

endswitch;
