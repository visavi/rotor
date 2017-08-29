<?php
App::view(Setting::get('themes').'/index');

if (is_admin([101, 102, 103])) {
    //show_title('Список забаненых');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `ban`=? AND `timeban`>?;", [1, SITETIME]);
    $page = App::paginate(Setting::get('reglist'), $total);

    if ($total > 0) {

        $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `ban`=? AND `timeban`>? ORDER BY `timelastban` DESC LIMIT ".$page['offset'].", ".Setting::get('reglist').";", [1, SITETIME]);

        while ($data = $queryusers -> fetch()) {
            echo '<div class="b">';
            echo user_gender($data['login']).' <b>'.profile($data['login']).'</b> (Забанен: '.date_fixed($data['timelastban']).')</div>';

            echo '<div>До окончания бана осталось '.formattime($data['timeban'] - SITETIME).'<br>';
            echo 'Забанил: <b>'.profile($data['loginsendban']).'</b><br>';
            echo 'Причина: '.App::bbCode($data['reasonban']).'<br>';
            echo '<i class="fa fa-pencil"></i> <a href="/admin/ban?act=edit&amp;uz='.$data['login'].'">Редактировать</a></div>';
        }

        App::pagination($page);

        echo 'Всего забанено: <b>'.$total.'</b><br><br>';

    } else {
        App::showError('Пользователей еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');
