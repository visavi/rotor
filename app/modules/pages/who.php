<?php
App::view(Setting::get('themes').'/index');

//show_title('Кто-где');

$total = DB::run() -> querySingle("SELECT count(*) FROM `visit`;");

if ($total > 0) {
    if ($total > Setting::get('lastusers')) {
        $total = Setting::get('lastusers');
    }
    $page = App::paginate(Setting::get('showuser'), $total);

    $queryvisit = DB::run() -> query("SELECT * FROM `visit` ORDER BY `nowtime` DESC LIMIT ".$page['offset'].", ".Setting::get('showuser').";");

    while ($data = $queryvisit -> fetch()) {

        if (SITETIME - $data['nowtime'] < 600) {
            $lastvisit = '<span style="color:#00ff00">Oнлайн</span>';
        } else {
            $lastvisit = formattime(SITETIME - $data['nowtime'], 0).' назад';
        }

        echo '<div class="b">'.user_gender($data['user']).' <b>'.profile($data['user']).'</b> ('.$lastvisit.')</div>';

        $position = (!empty($data['page'])) ? $data['page'] : 'Не определено';
        echo '<div>Находится: '.$position.'<br />';
        echo 'Переходов: '.$data['count'].'</div>';
    }

    App::pagination($page);
} else {
    show_error('Пользователей еще нет!');
}

App::view(Setting::get('themes').'/foot');
