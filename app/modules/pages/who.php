<?php
App::view($config['themes'].'/index');

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Кто-где');

$total = DB::run() -> querySingle("SELECT count(*) FROM `visit`;");

if ($total > 0) {
    if ($total > $config['lastusers']) {
        $total = $config['lastusers'];
    }

    $queryvisit = DB::run() -> query("SELECT * FROM `visit` ORDER BY `nowtime` DESC LIMIT ".$page['offset'].", ".$config['showuser'].";");

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

App::view($config['themes'].'/foot');
