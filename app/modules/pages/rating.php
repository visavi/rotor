<?php

$login = check(param('login'));

if (! is_user()) {
    App::abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
}

$getUser = user($login);
if (! $getUser) {
    App::abort('default', 'Данного пользователя не существует!');
}

if (App::getUsername() == $login) {
    App::abort('default', 'Запрещено изменять репутацию самому себе!');
}

if (App::user('point') < App::setting('editratingpoint')) {
    App::abort('default', 'Для изменения репутации необходимо набрать '.points(App::setting('editratingpoint')).'!');
}

$getRating = Rating::where('user', App::getUsername())->where('login', $login)->find_one();
if ($getRating) {
    App::abort('default', 'Вы уже изменяли репутацию этому пользователю!');
}

$vote = Request::input('vote') ? 1 : 0;

if (Request::isMethod('post')) {

    $token = check(Request::input('token'));
    $text = check(Request::input('text'));

    $validation = new Validation();

    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('string', $text, ['text' => 'Слишком длинный или короткий комментарий!'], true, 5, 250);

    if (App::user('rating') < 10 && empty($vote)) {
        $validation->addError('Уменьшать репутацию могут только пользователи с рейтингом 10 или выше!');
    }

    if ($validation->run()) {

        $text = antimat($text);

        $rating = Rating::create();
        $rating->set([
            'user' => App::getUsername(),
            'login' => $login,
            'text' => $text,
            'vote' => $vote,
            'time' => SITETIME,
        ]);
        $rating->save();

        Rating::where_lt('time', SITETIME - 3600 * 24 * 365)->delete_many();

        if ($vote == 1) {

            $user = User::where('login', $login)->find_one();
            $user->set_expr('rating', 'posrating - negrating + 1');
            $user->set_expr('posrating', 'posrating + 1');
            $user->save();

            $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($getUser['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

        } else {

            $user = User::where('login', $login)->find_one();
            $user->set_expr('rating', 'posrating - negrating - 1');
            $user->set_expr('negrating', 'negrating + 1');
            $user->save();

            $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($getUser['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;
        }

        send_private($login, App::getUsername(), $text);

        App::setFlash('success', 'Репутация успешно изменена!');
        redirect('/user/'.$login);

    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }
}

App::view('pages/rating', compact('login', 'vote'));
