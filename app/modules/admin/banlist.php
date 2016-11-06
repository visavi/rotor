<?php
App::view($config['themes'].'/index');

if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101, 102, 103))) {
    show_title('Список забаненых');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `ban`=? AND `timeban`>?;", array(1, SITETIME));

    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `ban`=? AND `timeban`>? ORDER BY `timelastban` DESC LIMIT ".$start.", ".$config['reglist'].";", array(1, SITETIME));

        while ($data = $queryusers -> fetch()) {
            echo '<div class="b">';
            echo user_gender($data['login']).' <b>'.profile($data['login']).'</b> (Забанен: '.date_fixed($data['timelastban']).')</div>';

            echo '<div>До окончания бана осталось '.formattime($data['timeban'] - SITETIME).'<br />';
            echo 'Забанил: <b>'.profile($data['loginsendban']).'</b><br />';
            echo 'Причина: '.bb_code($data['reasonban']).'<br />';
            echo '<i class="fa fa-pencil"></i> <a href="/admin/ban?act=edit&amp;uz='.$data['login'].'">Редактировать</a></div>';
        }

        page_strnavigation('/admin/banlist?', $config['banlist'], $start, $total);

        echo 'Всего забанено: <b>'.$total.'</b><br /><br />';

    } else {
        show_error('Пользователей еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view($config['themes'].'/foot');
