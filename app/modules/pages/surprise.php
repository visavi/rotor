<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '07.01';

$surprise['money'] = 10000;
$surprise['point'] = 150;
$surprise['rating'] = 3;

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
    'point'     => ['+', $surprise['money']],
    'money'     => ['+', $surprise['point']],
    'posrating' => ['+', $surprise['rating']],
], [
    'login' => App::getUsername()
]);

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.points($surprise['point']).PHP_EOL.moneys($surprise['money']).PHP_EOL.$surprise['rating'].' рейтинга репутации'.PHP_EOL.'Ура!!!';

send_private(App::getUsername(), App::setting('nickname'), $text);

DBM::run()->insert('surprise', [
    'user' => App::getUsername(),
    'year' => $currentYear,
    'time' => SITETIME,
]);

App::setFlash('success', 'Сюрприз успешно получен!');
App::redirect('/');

