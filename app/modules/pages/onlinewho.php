<?php
App::view(App::setting('themes').'/index');

//show_title('Онлайн пользователей');

$daytime = date("d", SITETIME);
$montime = date("d.m", SITETIME);

echo '<div class="b"><b>Пользователи онлайн:</b></div>';

$allonline = allonline();
$total = count($allonline);

if ($total > 0) {
    foreach($allonline as $key => $value) {
        $comma = (empty($key)) ? '' : ', ';
        echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
    }

    echo '<br />Всего пользователей: '.$total.' чел.<br /><br />';
} else {
    show_error('Зарегистированных пользователей нет!');
}

echo '<div class="b"><b>Поздравляем именинников:</b></div>';

$queryuser = DB::run() -> query("SELECT `login` FROM `users` WHERE substr(`birthday`,1,5)=?;", [$montime]);
$arrhappy = $queryuser -> fetchAll(PDO::FETCH_COLUMN);
$total = count($arrhappy);

if ($total > 0) {
    foreach($arrhappy as $key => $value) {
        $comma = (empty($key)) ? '' : ', ';
        echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
    }

    echo '<br />Всего именниников: '.$total.' чел.<br /><br />';
} else {
    show_error('Сегодня именинников нет!');
}
// ---------------------------------------------------------------------------------//
echo '<div class="b"><b>Приветствуем новичков:</b></div>';

$queryuser = DB::run() -> query("SELECT `login` FROM `users` WHERE `joined`>?;", [SITETIME-86400]);
$arrnovice = $queryuser -> fetchAll(PDO::FETCH_COLUMN);
$total = count($arrnovice);

if ($total > 0) {
    foreach($arrnovice as $key => $value) {
        $comma = (empty($key)) ? '' : ', ';
        echo $comma.user_gender($value).'<b>'.profile($value).'</b>';
    }

    echo '<br />Всего новичков: '.$total.' чел.<br /><br />';
} else {
    show_error('Новичков пока нет!');
}

echo '<i class="fa fa-users"></i> <a href="/who">Kто-где?</a><br />';

App::view(App::setting('themes').'/foot');
