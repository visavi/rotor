<?php
App::view(Setting::get('themes').'/index');

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
            $page = App::paginate(Setting::get('loglist'), $total);

            if ($total > 0) {

                $queryban = DB::run() -> query("SELECT * FROM `admlog` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('loglist').";");

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['user']).'</b>';
                    echo ' ('.date_fixed($data['time']).')</div>';
                    echo '<div>Страница: '.$data['request'].'<br />';
                    echo 'Откуда: '.$data['referer'].'<br />';
                    echo '<small><span style="color:#cc00cc">('.$data['brow'].', '.$data['ip'].')</span></small></div>';
                }

                App::pagination($page);

                echo '<i class="fa fa-times"></i> <a href="/admin/logadmin?act=del&amp;uid='.$_SESSION['token'].'">Очистить логи</a><br />';
            } else {
                show_error('Записей еще нет!');
            }
        break;

        ############################################################################################
        ##                                    Очистка логов                                       ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                DB::run() -> query("DELETE FROM admlog;");

                App::setFlash('success', 'Лог-файл успешно очищен!');
                App::redirect("/admin/logadmin");
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/logadmin">Вернуться</a><br />';
            break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
