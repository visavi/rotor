<?php

$login = check(param('login'));

if (! is_user()) {
    App::abort(403, 'Для изменения рейтинга небходимо авторизоваться!');
}

$user = User::where('login', $login)->first();

if (! $user) {
    App::abort('default', 'Данного пользователя не существует!');
}

if (App::getUserId() == $user->id) {
    App::abort('default', 'Запрещено изменять репутацию самому себе!');
}

if (App::user('point') < Setting::get('editratingpoint')) {
    App::abort('default', 'Для изменения репутации необходимо набрать '.points(Setting::get('editratingpoint')).'!');
}

// Голосовать за того же пользователя можно через 90 дней
$getRating = Rating::where('user_id', App::getUserId())
    ->where('recipient_id', $user->id)
    ->where('created_at', '>', SITETIME - 3600 * 24 * 90)
    ->first();

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

        $rating = Rating::create([
            'user_id'      => App::getUserId(),
            'recipient_id' => $user->id,
            'text'         => $text,
            'vote'         => $vote,
            'created_at'   => SITETIME,
        ]);

        if ($vote == 1) {
            $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам плюс! (Ваш рейтинг: ' . ($user['rating'] + 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

            $user->update([
                'rating' => Capsule::raw('posrating - negrating + 1'),
                'posrating' => Capsule::raw('posrating + 1'),
            ]);

        } else {

            $text = 'Пользователь [b]' . App::getUsername() . '[/b] поставил вам минус! (Ваш рейтинг: ' . ($user['rating'] - 1) . ')' . PHP_EOL . 'Комментарий: ' . $text;

            $user->update([
                'rating' => Capsule::raw('posrating - negrating - 1'),
                'negrating' => Capsule::raw('negrating + 1'),
            ]);
        }

        send_private($user->id, App::getUserId(), $text);

        App::setFlash('success', 'Репутация успешно изменена!');
        App::redirect('/user/'.$user->login);
    } else {
        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }
}

App::view('pages/rating', compact('user', 'vote'));
