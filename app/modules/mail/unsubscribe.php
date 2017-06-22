<?php
$key = check(Request::input('key'));

/**
 * Отписка от рассылки
 */
if (! $key) {
    App::abort('default', 'Отсутствует ключ для отписки от рассылки');
}
$user = User::where('subscribe', $key)->first();

if (! $user) {
    App::abort('default', 'Ключ для отписки от рассылки устарел!');
}

$user->subscribe = null;
$user->save();

App::setFlash('success', 'Вы успешно отписались от рассылки!');
App::redirect('/');
