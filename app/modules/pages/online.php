<?php
App::view($config['themes'].'/index');

$start = abs(intval(Request::input('start', 0)));

show_title('Кто в онлайне');

$total_all = DB::run() -> querySingle("SELECT count(*) FROM `online`;");
$total = DB::run() -> querySingle("SELECT count(*) FROM `online` WHERE `user`<>?;", ['']);

echo 'Всего на сайте: <b>'.$total_all.'</b><br />';
echo 'Зарегистрированных:  <b>'.$total.'</b><br /><br />';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        if ($total > 0) {

            $queryonline = DB::run() -> query("SELECT * FROM `online` WHERE `user`<>? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['onlinelist'].";", ['']);

            while ($data = $queryonline -> fetch()) {
                echo '<div class="b">';
                echo user_gender($data['user']).' <b>'.profile($data['user']).'</b> (Время: '.date_fixed($data['time'], 'H:i:s').')</div>';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<div><span class="data">('.$data['brow'].', '.$data['ip'].')</span></div>';
                }
            }

            App::pagination($page);
        } else {
            show_error('Авторизованных пользователей нет!');
        }

        echo '<i class="fa fa-users"></i> <a href="/online/all">Показать гостей</a><br />';
    break;

    ############################################################################################
    ##                                Список всех пользователей                               ##
    ############################################################################################
    case 'all':

        $total = $total_all;

        if ($total > 0) {

            $queryonline = DB::run() -> query("SELECT * FROM `online` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['onlinelist'].";");

            while ($data = $queryonline -> fetch()) {
                if (empty($data['user'])) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-user-circle-o"></i> <b>'.$config['guestsuser'].'</b>  (Время: '.date_fixed($data['time'], 'H:i:s').')</div>';
                } else {
                    echo '<div class="b">';
                    echo user_gender($data['user']).' <b>'.profile($data['user']).'</b> (Время: '.date_fixed($data['time'], 'H:i:s').')</div>';
                }

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<div><span class="data">('.$data['brow'].', '.$data['ip'].')</span></div>';
                }
            }

            App::pagination($page);
        } else {
            show_error('На сайте никого нет!');
        }

        echo '<i class="fa fa-users"></i> <a href="/online">Скрыть гостей</a><br />';
    break;

endswitch;

App::view($config['themes'].'/foot');
