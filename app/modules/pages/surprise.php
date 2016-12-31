<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '07.01';

$surprise['money'] = [7000, 15000];
$surprise['point'] = [100, 200];
$surprise['rating'] = [2, 5];

$surpriseMoney = mt_rand($surprise['money'][0], $surprise['money'][1]);
$surprisePoint = mt_rand($surprise['point'][0], $surprise['point'][1]);
$surpriseRating = mt_rand($surprise['rating'][0], $surprise['rating'][1]);

$currentYear = date('Y');

if (! is_user()) {
    App::abort('default', 'Для того чтобы получить сюрприз, необходимо авторизоваться!');
}

if (date('d.m') > $surprise['requiredDate']) {
    App::abort('default', 'Срок получения сюрприза уже закончился!');
}

if (App::user('point') < $surprise['requiredPoint']) {
    App::abort('default', 'Для того получения сюрприза необходимо '.points($surprise['requiredPoint']).'!');
}

$existSurprise = DBM::run()->selectFirst('surprise', ['user' => App::getUsername(), 'year' => $currentYear]);

if ($existSurprise) {
    App::abort('default', 'Сюрприз уже получен');
}

$user = DBM::run()->update('users', [
    'point'     => ['+', $surpriseMoney],
    'money'     => ['+', $surprisePoint],
    'posrating' => ['+', $surpriseRating],
], [
    'login' => App::getUsername()
]);

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.points($surprisePoint).PHP_EOL.moneys($surpriseMoney).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

send_private(App::getUsername(), App::setting('nickname'), $text);

DBM::run()->insert('surprise', [
    'user' => App::getUsername(),
    'year' => $currentYear,
    'time' => SITETIME,
]);

App::setFlash('success', 'Сюрприз успешно получен!');
App::redirect('/');

