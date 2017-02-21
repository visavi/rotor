<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$uz = (isset($_REQUEST['uz'])) ? check($_REQUEST['uz']) : '';

show_title('Статистика вкладов');

switch ($act):
############################################################################################
##                                      Вывод вкладов                                     ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `bank`;");
        $page = App::paginate(App::setting('vkladlist'), $total);

        if ($total > 0) {

            $queryvklad = DB::run() -> query("SELECT * FROM `bank` ORDER BY `sum` DESC, `user` ASC LIMIT ".$page['offset'].", ".$config['vkladlist'].";");

            $i = 0;
            while ($data = $queryvklad -> fetch()) {
                ++$i;

                echo '<div class="b">'.($page['offset'] + $i).'. '.user_gender($data['user']).' ';

                if ($uz == $data['user']) {
                    echo '<b><big>'.profile($data['user'], '#ff0000').'</big></b> ('.moneys($data['sum']).')</div>';
                } else {
                    echo '<b>'.profile($data['user']).'</b> ('.moneys($data['sum']).')</div>';
                }

                echo '<div>Начислений: '.$data['oper'].'<br />';
                echo 'Посл. операция: '.date_fixed($data['time']).'</div>';
            }

            App::pagination($page);

            echo '<div class="form">';
            echo '<b>Поиск пользователя:</b><br />';
            echo '<form action="/games/livebank?act=search&amp;page='.$page['current'].'" method="post">';
            echo '<input type="text" name="uz" value="'.$log.'" />';
            echo '<input type="submit" value="Искать" /></form></div><br />';

            echo 'Всего вкладчиков: <b>'.$total.'</b><br /><br />';
        } else {
            show_error('Вкладов еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Поиск пользователя                                    ##
    ############################################################################################
    case 'search':

        if (!empty($uz)) {
            $queryuser = DB::run() -> querySingle("SELECT `login` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($uz)]);

            if (!empty($queryuser)) {
                $queryrating = DB::run() -> query("SELECT `user` FROM `bank` ORDER BY `sum` DESC, `user` ASC;");
                $ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

                foreach ($ratusers as $key => $ratval) {
                    if ($queryuser == $ratval) {
                        $rat = $key + 1;
                    }
                }

                if (!empty($rat)) {
                    $end = ceil($rat / $config['vkladlist']);

                    notice('Позиция в рейтинге: '.$rat);
                    redirect("/games/livebank?page=$end&uz=$queryuser");
                } else {
                    show_error('Пользователь с данным логином не найден!');
                }
            } else {
                show_error('Пользователь с данным логином не зарегистрирован!');
            }
        } else {
            show_error('Ошибка! Вы не ввели логин пользователя');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/games/livebank?page='.$page.'">Вернуться</a><br />';
    break;

endswitch;

echo '<i class="fa fa-money"></i> <a href="/games/bank">В банк</a><br />';
echo '<i class="fa fa-cube"></i> <a href="/games">Развлечения</a><br />';

App::view($config['themes'].'/foot');
