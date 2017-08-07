<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '10.01';

$surpriseMoney = mt_rand(10000, 20000);
$surprisePoint = mt_rand(150, 250);
$surpriseRating = mt_rand(3, 7);

$currentYear = date('Y');

if (! is_user()) {
    App::abort('default', 'Для того чтобы получить сюрприз, необходимо авторизоваться!');
}

if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
    App::abort('default', 'Срок получения сюрприза уже закончился!');
}

if (App::user('point') < $surprise['requiredPoint']) {
    App::abort('default', 'Для того получения сюрприза необходимо '.points($surprise['requiredPoint']).'!');
}

$existSurprise = Surprise::where('user_id', App::getUserId())
    ->where('year', $currentYear)
    ->first();

if ($existSurprise) {
    App::abort('default', 'Сюрприз уже получен');
}


$user = User::find(App::getUserId());
$user->update([
    'point' => Capsule::raw('point + '.$surprisePoint),
    'money' => Capsule::raw('money + '.$surpriseMoney),
    'rating' => Capsule::raw('posrating - negrating + '.$surpriseRating),
    'posrating' => Capsule::raw('posrating + '.$surpriseRating),
]);

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.points($surprisePoint).PHP_EOL.moneys($surpriseMoney).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

send_private(App::getUsername(), Setting::get('nickname'), $text);

$surprise = Surprise::create([
    'user_id' => App::getUserId(),
    'year' => $currentYear,
    'created_at' => SITETIME,
]);

App::setFlash('success', 'Сюрприз успешно получен!');
App::redirect('/');

