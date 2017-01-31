<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '08.01';

$surprise['money'] = [10000, 20000];
$surprise['point'] = [150, 250];
$surprise['rating'] = [3, 7];

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

$existSurprise = Surprise::where('user', App::getUsername())->where('year', $currentYear)->find_one();
if ($existSurprise) {
    App::abort('default', 'Сюрприз уже получен');
}

$user = User::find_one(App::getUserId());
$user->set_expr('point', 'point + '.$surprisePoint);
$user->set_expr('money', 'money + '.$surpriseMoney);
$user->set_expr('rating', 'posrating - negrating + '.$surpriseRating);
$user->set_expr('posrating', 'posrating + '.$surpriseRating);
$user->save();

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.points($surprisePoint).PHP_EOL.moneys($surpriseMoney).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

send_private(App::getUsername(), App::setting('nickname'), $text);

$surprise = Surprise::create();
$surprise->set([
    'user' => App::getUsername(),
    'year' => $currentYear,
    'time' => SITETIME,
]);
$surprise->save();

App::setFlash('success', 'Сюрприз успешно получен!');
App::redirect('/');

