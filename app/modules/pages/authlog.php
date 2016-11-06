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

show_title('История моих авторизаций');

if (is_user()) {
    ############################################################################################
    ##                                   История авторизаций                                  ##
    ############################################################################################
    $total = DB::run() -> querySingle("SELECT count(*) FROM `login` WHERE `user`=?;", array($log));
    if ($total > 0) {
        if ($start >= $total) {
            $start = 0;
        }

        $querylogin = DB::run() -> query("SELECT * FROM `login` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$start.", ".$config['loginauthlist'].";", array($log));
        while ($data = $querylogin -> fetch()) {
            echo '<div class="b">';
            echo' <i class="fa fa-clock-o"></i>  ';

            if (empty($data['type'])) {
                echo '<b>Автовход</b>';
            } else {
                echo '<b>Авторизация</b>';
            }
            echo ' <small>('.date_fixed($data['time']).')</small>';

            echo '</div>';

            echo '<div><span class="data">';
            echo 'Browser '.$data['brow'].' / ';
            echo 'IP '.$data['ip'];
            echo '</span></div>';
        }

        page_strnavigation('/authlog?', $config['loginauthlist'], $start, $total);
    } else {
        show_error('История авторизаций отсутствует');
    }
} else {
    show_login('Вы не авторизованы, для просмотра истории, необходимо');
}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/menu">Вернуться</a><br />';

App::view($config['themes'].'/foot');
