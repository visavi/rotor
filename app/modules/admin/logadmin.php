<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin([101])) {
    //show_title('Админ-логи');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $total = DB::run() -> querySingle("SELECT count(*) FROM admlog;");
            $page = paginate(setting('loglist'), $total);

            if ($total > 0) {

                $queryban = DB::run() -> query("SELECT * FROM `admlog` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('loglist').";");

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['user']).'</b>';
                    echo ' ('.dateFixed($data['time']).')</div>';
                    echo '<div>Страница: '.$data['request'].'<br>';
                    echo 'Откуда: '.$data['referer'].'<br>';
                    echo '<small><span style="color:#cc00cc">('.$data['brow'].', '.$data['ip'].')</span></small></div>';
                }

                pagination($page);

                echo '<i class="fa fa-times"></i> <a href="/admin/logadmin?act=del&amp;uid='.$_SESSION['token'].'">Очистить логи</a><br>';
            } else {
                showError('Записей еще нет!');
            }
        break;

        ############################################################################################
        ##                                    Очистка логов                                       ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                DB::run() -> query("DELETE FROM admlog;");

                setFlash('success', 'Лог-файл успешно очищен!');
                redirect("/admin/logadmin");
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/logadmin">Вернуться</a><br>';
            break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
