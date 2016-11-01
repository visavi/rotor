<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin(array(101))) {
    show_title('Админ-логи');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $total = DB::run() -> querySingle("SELECT count(*) FROM admlog;");

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryban = DB::run() -> query("SELECT * FROM `admlog` ORDER BY `admlog_time` DESC LIMIT ".$start.", ".$config['loglist'].";");

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.profile($data['admlog_user']).'</b>';
                    echo ' ('.date_fixed($data['admlog_time']).')</div>';
                    echo '<div>Страница: '.$data['admlog_request'].'<br />';
                    echo 'Откуда: '.$data['admlog_referer'].'<br />';
                    echo '<small><span style="color:#cc00cc">('.$data['admlog_brow'].', '.$data['admlog_ip'].')</span></small></div>';
                }

                page_strnavigation('/admin/logadmin?', $config['loglist'], $start, $total);

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

                $_SESSION['note'] = 'Лог-файл успешно очищен!';
                redirect("/admin/logadmin");
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/logadmin">Вернуться</a><br />';
            break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect('/');
}

App::view($config['themes'].'/foot');
