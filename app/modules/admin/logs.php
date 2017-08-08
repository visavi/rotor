<?php
App::view(Setting::get('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = '404';
}

if (is_admin([101, 102])) {
    //show_title('Просмотр лог-файлов');

    if (empty(Setting::get('errorlog'))){
        echo '<b><span style="color:#ff0000">Внимание! Запись логов выключена в настройках!</span></b><br /><br />';
    }

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case '404':

            echo '<b>Ошибки 404</b> | <a href="/admin/logs?act=403">Ошибки 403</a> | <a href="/admin/logs?act=666">Автобаны</a><br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `num`=?;", [404]);
            $page = App::paginate(Setting::get('loglist'), $total);

            if ($total > 0) {

                $queryban = DB::run() -> query("SELECT * FROM `error` WHERE `num`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('loglist').";", [404]);

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.$data['request'].'</b> <small>('.date_fixed($data['time']).')</small></div>';
                    echo '<div>Referer: '.($data['referer'] ?: 'Не определено').'<br />';
                    echo 'Пользователь: '.$data['username'].'<br />';
                    echo '<small><span class="data">('.$data['brow'].', '.$data['ip'].')</span></small></div>';
                }

                App::pagination($page);

                if (is_admin([101])) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/logs?act=clear&amp;uid='.$_SESSION['token'].'">Очистить логи</a><br />';
                }
            } else {
                show_error('Записей еще нет!');
            }
        break;

        ############################################################################################
        ##                                       Ошибки 403                                       ##
        ############################################################################################
        case '403':

            echo '<a href="/admin/logs?act=404">Ошибки 404</a> | <b>Ошибки 403</b> | <a href="/admin/logs?act=666">Автобаны</a><br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `num`=?;", [403]);
            $page = App::paginate(Setting::get('loglist'), $total);

            if ($total > 0) {

                $queryban = DB::run() -> query("SELECT * FROM `error` WHERE `num`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('loglist').";", [403]);

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.$data['request'].'</b> <small>('.date_fixed($data['time']).')</small></div>';
                    echo '<div>Referer: '.($data['referer'] ?: 'Не определено').'<br />';
                    echo 'Пользователь: '.$data['username'].'<br />';
                    echo '<small><span class="data">('.$data['brow'].', '.$data['ip'].')</span></small></div>';
                }

                App::pagination($page);
            } else {
                show_error('Записей еще нет!');
            }
        break;

        ############################################################################################
        ##                                        Автобаны                                        ##
        ############################################################################################
        case '666':

            echo '<a href="/admin/logs?act=404">Ошибки 404</a> | <a href="/admin/logs?act=403">Ошибки 403</a> | <b>Автобаны</b><br /><br />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM `error` WHERE `num`=?;", [666]);
            $page = App::paginate(Setting::get('loglist'), $total);

            if ($total > 0) {

                $queryban = DB::run() -> query("SELECT * FROM `error` WHERE `num`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('loglist').";", [666]);

                while ($data = $queryban -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> <b>'.$data['request'].'</b> <small>('.date_fixed($data['time']).')</small></div>';
                    echo '<div>Referer: '.($data['referer'] ?: 'Не определено').'<br />';
                    echo 'Пользователь: '.$data['username'].'<br />';
                    echo '<small><span class="data">('.$data['brow'].', '.$data['ip'].')</span></small></div>';
                }

                App::pagination($page);
            } else {
                show_error('Записей еще нет!');
            }
        break;

        ############################################################################################
        ##                                     Очистка логов                                      ##
        ############################################################################################
        case 'clear':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_admin([101])) {
                    DB::run() -> query("TRUNCATE `error`;");

                    App::setFlash('success', 'Лог-файлы успешно очищены!');
                    App::redirect("/admin/logs");

                } else {
                    show_error('Ошибка! Очищать логи могут только суперадмины!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/logs">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');
