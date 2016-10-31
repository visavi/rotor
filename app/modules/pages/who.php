<?php
App::view($config['themes'].'/index');

$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

show_title('Кто-где');

$total = DB::run() -> querySingle("SELECT count(*) FROM `visit`;");

if ($total > 0) {
    if ($total > $config['lastusers']) {
        $total = $config['lastusers'];
    }
    if ($start >= $total) {
        $start = 0;
    }

    $queryvisit = DB::run() -> query("SELECT * FROM `visit` ORDER BY `visit_nowtime` DESC LIMIT ".$start.", ".$config['showuser'].";");

    while ($data = $queryvisit -> fetch()) {

        if (SITETIME - $data['visit_nowtime'] < 600) {
            $lastvisit = '<span style="color:#00ff00">Oнлайн</span>';
        } else {
            $lastvisit = formattime(SITETIME - $data['visit_nowtime'], 0).' назад';
        }

        echo '<div class="b">'.user_gender($data['visit_user']).' <b>'.profile($data['visit_user']).'</b> ('.$lastvisit.')</div>';

        $position = (!empty($data['visit_page'])) ? $data['visit_page'] : 'Не определено';
        echo '<div>Находится: '.$position.'<br />';
        echo 'Переходов: '.$data['visit_count'].'</div>';
    }

    page_strnavigation('/who?', $config['showuser'], $start, $total);
} else {
    show_error('Пользователей еще нет!');
}

App::view($config['themes'].'/foot');
