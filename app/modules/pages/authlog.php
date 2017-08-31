<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

//show_title('История моих авторизаций');

if (isUser()) {
    ############################################################################################
    ##                                   История авторизаций                                  ##
    ############################################################################################
    $total = DB::run() -> querySingle("SELECT count(*) FROM `login` WHERE `user`=?;", [getUsername()]);
    $page = paginate(setting('loginauthlist'), $total);

    if ($total > 0) {

        $querylogin = DB::run() -> query("SELECT * FROM `login` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".setting('loginauthlist').";", [getUsername()]);
        while ($data = $querylogin -> fetch()) {
            echo '<div class="b">';
            echo' <i class="fa fa-clock-o"></i>  ';

            if (empty($data['type'])) {
                echo '<b>Автовход</b>';
            } else {
                echo '<b>Авторизация</b>';
            }
            echo ' <small>('.dateFixed($data['time']).')</small>';

            echo '</div>';

            echo '<div><span class="data">';
            echo 'Browser '.$data['brow'].' / ';
            echo 'IP '.$data['ip'];
            echo '</span></div>';
        }

        pagination($page);
    } else {
        showError('История авторизаций отсутствует');
    }
} else {
    showError('Для просмотра истории необходимо авторизоваться');
}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/menu">Вернуться</a><br>';

view(setting('themes').'/foot');
