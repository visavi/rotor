<?php
App::view($config['themes'].'/index');

if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101, 102, 103))) {
    show_title('Список забаненых');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `users_ban`=? AND `users_timeban`>?;", array(1, SITETIME));

    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `users_ban`=? AND `users_timeban`>? ORDER BY `users_timelastban` DESC LIMIT ".$start.", ".$config['reglist'].";", array(1, SITETIME));

        while ($data = $queryusers -> fetch()) {
            echo '<div class="b">';
            echo user_gender($data['users_login']).' <b>'.profile($data['users_login']).'</b> (Забанен: '.date_fixed($data['users_timelastban']).')</div>';

            echo '<div>До окончания бана осталось '.formattime($data['users_timeban'] - SITETIME).'<br />';
            echo 'Забанил: <b>'.profile($data['users_loginsendban']).'</b><br />';
            echo 'Причина: '.bb_code($data['users_reasonban']).'<br />';
            echo '<i class="fa fa-pencil"></i> <a href="/admin/ban?act=edit&amp;uz='.$data['users_login'].'">Редактировать</a></div>';
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
