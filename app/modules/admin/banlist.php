<?php
view(setting('themes').'/index');

if (isAdmin([101, 102, 103])) {
    //show_title('Список забаненых');

    $total = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `ban`=? AND `timeban`>?;", [1, SITETIME]);
    $page = paginate(setting('reglist'), $total);

    if ($total > 0) {

        $queryusers = DB::run() -> query("SELECT * FROM `users` WHERE `ban`=? AND `timeban`>? ORDER BY `timelastban` DESC LIMIT ".$page['offset'].", ".setting('reglist').";", [1, SITETIME]);

        while ($data = $queryusers -> fetch()) {
            echo '<div class="b">';
            echo userGender($data['login']).' <b>'.profile($data['login']).'</b> (Забанен: '.dateFixed($data['timelastban']).')</div>';

            echo '<div>До окончания бана осталось '.formatTime($data['timeban'] - SITETIME).'<br>';
            echo 'Забанил: <b>'.profile($data['loginsendban']).'</b><br>';
            echo 'Причина: '.bbCode($data['reasonban']).'<br>';
            echo '<i class="fa fa-pencil"></i> <a href="/admin/ban?act=edit&amp;uz='.$data['login'].'">Редактировать</a></div>';
        }

        pagination($page);

        echo 'Всего забанено: <b>'.$total.'</b><br><br>';

    } else {
        showError('Пользователей еще нет!');
    }

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect("/");
}

view(setting('themes').'/foot');
