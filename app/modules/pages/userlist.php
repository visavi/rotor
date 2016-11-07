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
if (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} elseif (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} else {
    $uz = "";
}

show_title('Список пользователей');

switch ($act):
############################################################################################
##                                    Вывод пользователей                                 ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");

        if ($total > 0) {
            if ($start >= $total) {
                $start = 0;
            }

            $queryusers = DB::run() -> query("SELECT * FROM `users` ORDER BY `point` DESC, `login` ASC LIMIT ".$start.", ".$config['userlist'].";");

            $i = 0;
            while ($data = $queryusers -> fetch()) {
                ++$i;

                echo '<div class="b"> ';
                echo '<div class="img">'.user_avatars($data['login']).'</div>';

                if ($uz == $data['login']) {
                    echo ($start + $i).'. <b><big>'.profile($data['login'], '#ff0000').'</big></b> ';
                } else {
                    echo ($start + $i).'. <b>'.profile($data['login']).'</b> ';
                }
                echo '('.points($data['point']).')<br />';
                echo user_title($data['login']).' '.user_online($data['login']);
                echo '</div>';

                echo '<div>';
                echo 'Форум: '.$data['allforum'].' | Гостевая: '.$data['allguest'].' | Коммент: '.$data['allcomments'].'<br />';
                echo 'Посещений: '.$data['visits'].'<br />';
                echo 'Деньги: '.user_money($data['login']).'<br />';
                echo 'Дата регистрации: '.date_fixed($data['joined'], 'j F Y').'</div>';
            }

            page_strnavigation('/userlist?', $config['userlist'], $start, $total);

            echo '<div class="form">';
            echo '<b>Поиск пользователя:</b><br />';
            echo '<form action="/userlist?act=search&amp;start='.$start.'" method="post">';
            echo '<input type="text" name="uz" value="'.$log.'" />';
            echo '<input type="submit" value="Искать" /></form></div><br />';

            echo 'Всего пользователей: <b>'.$total.'</b><br /><br />';
        } else {
            show_error('Пользователей еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Поиск пользователя                                    ##
    ############################################################################################
    case 'search':

        if (!empty($uz)) {
            $queryuser = DB::run() -> querySingle("SELECT `login` FROM `users` WHERE LOWER(`login`)=? OR LOWER(`nickname`)=? LIMIT 1;", [strtolower($uz), utf_lower($uz)]);

            if (!empty($queryuser)) {
                $queryrating = DB::run() -> query("SELECT `login` FROM `users` ORDER BY `point` DESC, `login` ASC;");
                $ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

                foreach ($ratusers as $key => $ratval) {
                    if ($queryuser == $ratval) {
                        $rat = $key + 1;
                    }
                }

                if (!empty($rat)) {
                    $page = floor(($rat - 1) / $config['userlist']) * $config['userlist'];

                    notice('Позиция в рейтинге: '.$rat);
                    redirect("/userlist?start=$page&uz=$queryuser");
                } else {
                    show_error('Пользователь с данным логином не найден!');
                }
            } else {
                show_error('Пользователь с данным логином не зарегистрирован!');
            }
        } else {
            show_error('Ошибка! Вы не ввели логин или ник пользователя');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/userlist?start='.$start.'">Вернуться</a><br />';
    break;

endswitch;

echo '<i class="fa fa-users"></i> <a href="/onlinewho">Новички</a><br />';

App::view($config['themes'].'/foot');
