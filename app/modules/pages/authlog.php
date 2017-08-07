<?php
App::view(Setting::get('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

//show_title('История моих авторизаций');

if (is_user()) {
    ############################################################################################
    ##                                   История авторизаций                                  ##
    ############################################################################################
    $total = DB::run() -> querySingle("SELECT count(*) FROM `login` WHERE `user`=?;", [App::getUsername()]);
    $page = App::paginate(Setting::get('loginauthlist'), $total);

    if ($total > 0) {

        $querylogin = DB::run() -> query("SELECT * FROM `login` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('loginauthlist').";", [App::getUsername()]);
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

        App::pagination($page);
    } else {
        show_error('История авторизаций отсутствует');
    }
} else {
    show_login('Вы не авторизованы, для просмотра истории, необходимо');
}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/menu">Вернуться</a><br />';

App::view(Setting::get('themes').'/foot');
