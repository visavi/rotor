<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isAdmin([101]) && getUsername() == setting('nickname')) {
    //show_title('Очистка базы юзеров');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo 'Удалить пользователей которые не посещали сайт:<br>';

            echo '<div class="form">';
            echo '<form action="/admin/delusers?act=poddel" method="post">';
            echo 'Период:<br>';
            echo '<select name="deldate">';
            echo '<option value="1080">3 года</option>';
            echo '<option value="900">2.5 года</option>';
            echo '<option value="720">2 года</option>';
            echo '<option value="560">1.5 года</option>';
            echo '<option value="360">1 год</option>';
            echo '<option value="180">0.5 года</option>';
            echo '</select><br>';
            echo 'Минимум актива:<br>';
            echo '<input type="text" name="point" value="0"><br>';
            echo '<input value="Анализ" type="submit"></form></div><br>';

            echo 'Всего пользователей: <b>'.statsUsers().'</b><br><br>';
        break;

        ############################################################################################
        ##                                Подтверждение удаления                                  ##
        ############################################################################################
        case "poddel":

            $deldate = abs(intval($_POST['deldate']));
            $point = abs(intval($_POST['point']));

            if ($deldate >= 180) {
                $deltime = $deldate * 24 * 3600;

                $queryusers = DB::run() -> query("SELECT login FROM users WHERE timelastlogin<? AND point<=?;", [SITETIME - $deltime, $point]);
                $users = $queryusers -> fetchAll(PDO::FETCH_COLUMN);
                $total = count($users);

                if ($total > 0) {
                    echo 'Будут удалены пользователи не посещавшие сайт более <b>'.$deldate.'</b> дней <br>';
                    echo 'И имеющие в своем активе не более '.points($point).'<br><br>';

                    echo '<b>Список:</b> ';

                    foreach ($users as $key => $value) {
                        if ($key == 0) {
                            $comma = '';
                        } else {
                            $comma = ', ';
                        }
                        echo $comma.' '.profile($value);
                    }

                    echo '<br><br>Будет удалено пользователей: <b>'.$total.'</b><br><br>';

                    echo '<i class="fa fa-times"></i> <b><a href="/admin/delusers?act=del&amp;deldate='.$deldate.'&amp;point='.$point.'&amp;uid='.$_SESSION['token'].'">Удалить пользователей</a></b><br><br>';
                } else {
                    showError('Пользователи для удаления отсутствуют!');
                }
            } else {
                showError('Ошибка! Указанно недопустимое время для удаления!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/delusers">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                Удаление пользователей                                  ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);
            $deldate = abs(intval($_GET['deldate']));
            $point = abs(intval($_GET['point']));

            if ($uid == $_SESSION['token']) {
                if ($deldate >= 180) {
                    $deltime = $deldate * 24 * 3600;

                    $queryusers = DB::run() -> query("SELECT login FROM users WHERE timelastlogin<? AND point<=?;", [SITETIME - $deltime, $point]);
                    $users = $queryusers -> fetchAll(PDO::FETCH_COLUMN);
                    $total = count($users);

                    if ($total > 0) {
                        foreach ($users as $value) {
                            delete_album($value);
                            delete_users($value);
                        }

                        echo 'Пользователи не посещавшие сайт более <b>'.$deldate.'</b> дней, успешно удалены!<br>';
                        echo 'Было удалено пользователей: <b>'.$total.'</b><br><br>';
                    } else {
                        showError('Пользователи для удаления отсутствуют!');
                    }
                } else {
                    showError('Ошибка! Указанно недопустимое время для удаления!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/delusers">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
