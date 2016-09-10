<?php
App::view($config['themes'].'/index');

$start = abs(intval(Request::input('start', 0)));

show_title('Кто в онлайне');

$total_all = DB::run() -> querySingle("SELECT count(*) FROM `online`;");
$total = DB::run() -> querySingle("SELECT count(*) FROM `online` WHERE `online_user`<>?;", array(''));

echo 'Всего на сайте: <b>'.$total_all.'</b><br />';
echo 'Зарегистрированных:  <b>'.$total.'</b><br /><br />';

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
    case 'index':

        if ($total > 0) {
            if ($start >= $total) {
                $start = 0;
            }

            $queryonline = DB::run() -> query("SELECT * FROM `online` WHERE `online_user`<>? ORDER BY `online_time` DESC LIMIT ".$start.", ".$config['onlinelist'].";", array(''));

            while ($data = $queryonline -> fetch()) {
                echo '<div class="b">';
                echo user_gender($data['online_user']).' <b>'.profile($data['online_user']).'</b> (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<div><span class="data">('.$data['online_brow'].', '.$data['online_ip'].')</span></div>';
                }
            }

            page_strnavigation('/online?', $config['onlinelist'], $start, $total);
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
            if ($start >= $total) {
                $start = 0;
            }

            $queryonline = DB::run() -> query("SELECT * FROM `online` ORDER BY `online_time` DESC LIMIT ".$start.", ".$config['onlinelist'].";");

            while ($data = $queryonline -> fetch()) {
                if (empty($data['online_user'])) {
                    echo '<div class="b">';
                    echo '<img src="/images/img/user.gif" alt="image" /> <b>'.$config['guestsuser'].'</b>  (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';
                } else {
                    echo '<div class="b">';
                    echo user_gender($data['online_user']).' <b>'.profile($data['online_user']).'</b> (Время: '.date_fixed($data['online_time'], 'H:i:s').')</div>';
                }

                if (is_admin() || empty($config['anonymity'])) {
                    echo '<div><span class="data">('.$data['online_brow'].', '.$data['online_ip'].')</span></div>';
                }
            }

            page_strnavigation('/online/all?', $config['onlinelist'], $start, $total);
        } else {
            show_error('На сайте никого нет!');
        }

        echo '<i class="fa fa-users"></i> <a href="/online">Скрыть гостей</a><br />';
    break;

endswitch;

App::view($config['themes'].'/foot');
