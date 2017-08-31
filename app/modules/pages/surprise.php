<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '10.01';

$surpriseMoney = mt_rand(10000, 20000);
$surprisePoint = mt_rand(150, 250);
$surpriseRating = mt_rand(3, 7);

$currentYear = date('Y');

if (! is_user()) {
    abort('default', 'Для того чтобы получить сюрприз, необходимо авторизоваться!');
}

if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
    abort('default', 'Срок получения сюрприза уже закончился!');
}

if (user('point') < $surprise['requiredPoint']) {
    abort('default', 'Для того получения сюрприза необходимо '.points($surprise['requiredPoint']).'!');
}

$existSurprise = Surprise::where('user_id', getUserId())
    ->where('year', $currentYear)
    ->first();

if ($existSurprise) {
    abort('default', 'Сюрприз уже получен');
}


$user = User::find(getUserId());
$user->update([
    'point' => DB::raw('point + '.$surprisePoint),
    'money' => DB::raw('money + '.$surpriseMoney),
    'rating' => DB::raw('posrating - negrating + '.$surpriseRating),
    'posrating' => DB::raw('posrating + '.$surpriseRating),
]);

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.points($surprisePoint).PHP_EOL.moneys($surpriseMoney).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

send_private(getUsername(), setting('nickname'), $text);

$surprise = Surprise::create([
    'user_id' => getUserId(),
    'year' => $currentYear,
    'created_at' => SITETIME,
]);

setFlash('success', 'Сюрприз успешно получен!');
redirect('/');

