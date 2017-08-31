<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} elseif (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} else {
    $uz = "";
}
$page = abs(intval(Request::input('page', 1)));

//show_title('Рейтинг толстосумов');

switch ($action):
############################################################################################
##                                   Вывод толстосумов                                    ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");
        $page = paginate(setting('userlist'), $total);

        if ($total > 0) {

            $queryusers = DB::run() -> query("SELECT * FROM `users` ORDER BY `money` DESC, `login` ASC LIMIT ".$page['offset'].", ".setting('userlist').";");

            $i = 0;
            while ($data = $queryusers -> fetch()) {
                ++$i;

                echo '<div class="b">'.($page['offset'] + $i).'. '.userGender($data['login']);

                if ($uz == $data['login']) {
                    echo ' <b><big>'.profile($data['login'], '#ff0000').'</big></b> ('.moneys($data['money']).')</div>';
                } else {
                    echo ' <b>'.profile($data['login']).'</b> ('.moneys($data['money']).')</div>';
                }
            }

            pagination($page);

            echo '<div class="form">';
            echo '<b>Поиск пользователя:</b><br>';
            echo '<form action="/ratinglist?act=search&amp;page='.$page['current'].'" method="post">';
            echo '<input type="text" name="uz" value="'.getUsername().'">';
            echo '<input type="submit" value="Искать"></form></div><br>';

            echo 'Всего юзеров: <b>'.$total.'</b><br><br>';
        } else {
            showError('Пользователей еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Поиск пользователя                                    ##
    ############################################################################################
    case 'search':

        if (!empty($uz)) {
            $queryuser = DB::run() -> querySingle("SELECT `login` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($uz)]);

            if (!empty($queryuser)) {
                $queryrating = DB::run() -> query("SELECT `login` FROM `users` ORDER BY `money` DESC, `login` ASC;");
                $ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

                foreach ($ratusers as $key => $ratval) {
                    if ($queryuser == $ratval) {
                        $rat = $key + 1;
                    }
                }

                if (!empty($rat)) {
                    $end = ceil($rat / setting('userlist'));

                    setFlash('success', 'Позиция в рейтинге: '.$rat);
                    redirect("/ratinglist?page=$end&uz=$queryuser");
                } else {
                    showError('Пользователь с данным логином не найден!');
                }
            } else {
                showError('Пользователь с данным логином не зарегистрирован!');
            }
        } else {
            showError('Ошибка! Вы не ввели логин пользователя');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ratinglist?page='.$page.'">Вернуться</a><br>';
    break;

endswitch;

view(setting('themes').'/foot');
