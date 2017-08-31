<?php
view(setting('themes').'/index');

//show_title('Кто-где');

$total = DB::run() -> querySingle("SELECT count(*) FROM `visit`;");

if ($total > 0) {
    if ($total > setting('lastusers')) {
        $total = setting('lastusers');
    }
    $page = paginate(setting('showuser'), $total);

    $queryvisit = DB::run() -> query("SELECT * FROM `visit` ORDER BY `nowtime` DESC LIMIT ".$page['offset'].", ".setting('showuser').";");

    while ($data = $queryvisit -> fetch()) {

        if (SITETIME - $data['nowtime'] < 600) {
            $lastvisit = '<span style="color:#00ff00">Oнлайн</span>';
        } else {
            $lastvisit = formattime(SITETIME - $data['nowtime'], 0).' назад';
        }

        echo '<div class="b">'.user_gender($data['user']).' <b>'.profile($data['user']).'</b> ('.$lastvisit.')</div>';

        $position = (!empty($data['page'])) ? $data['page'] : 'Не определено';
        echo '<div>Находится: '.$position.'<br>';
        echo 'Переходов: '.$data['count'].'</div>';
    }

    pagination($page);
} else {
    showError('Пользователей еще нет!');
}

view(setting('themes').'/foot');
