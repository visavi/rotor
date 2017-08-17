<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$page = abs(intval(Request::input('page', 1)));

if (is_admin([101, 102, 103])) {
    //show_title('История банов');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist`;");
            $page = App::paginate(Setting::get('listbanhist'), $total);

            if ($total > 0) {

                $queryhist = DB::run() -> query("SELECT * FROM `banhist` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('listbanhist').";");

                echo '<form action="/admin/banhist?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryhist -> fetch()) {
                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['user']).'</div>';
                    echo '<b>'.profile($data['user']).'</b> '.user_online($data['user']).' ';

                    echo '<small>('.date_fixed($data['time']).')</small><br>';

                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> ';

                    echo '<a href="/admin/ban?act=editban&amp;uz='.$data['user'].'">Изменить</a> / <a href="/admin/banhist?act=view&amp;uz='.$data['user'].'">Все изменения</a></div>';

                    echo '<div>';
                    if (!empty($data['type'])) {
                        echo 'Причина: '.App::bbCode($data['reason']).'<br>';
                        echo 'Срок: '.formattime($data['term']).'<br>';
                    }

                    switch ($data['type']) {
                        case '1': $stat = '<span style="color:#ff0000">Забанил</span>:';
                            break;
                        case '2': $stat = '<span style="color:#ffa500">Изменил</span>:';
                            break;
                        default: $stat = '<span style="color:#00cc00">Разбанил</span>:';
                    }

                    echo $stat.' '.profile($data['send']).'<br>';

                    echo '</div>';
                }

                echo '<br><input type="submit" value="Удалить выбранное"></form>';

                App::pagination($page);

                echo '<div class="form">';
                echo '<b>Поиск по пользователю:</b><br>';
                echo '<form action="/admin/banhist?act=view" method="get">';
                echo '<input type="hidden" name="act" value="view">';
                echo '<input type="text" name="uz">';
                echo '<input type="submit" value="Искать"></form></div><br>';

                echo 'Всего действий: <b>'.$total.'</b><br><br>';
            } else {
                show_error('Истории банов еще нет!');
            }
        break;

        ############################################################################################
        ##                                Просмотр по пользователям                               ##
        ############################################################################################
        case 'view':
            $uz = (isset($_GET['uz'])) ? check($_GET['uz']) : '';

            if (user($uz)) {
                $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `user`=?;", [$uz]);
                $page = App::paginate(Setting::get('listbanhist'), $total);

                if ($total > 0) {

                    $queryhist = DB::run() -> query("SELECT * FROM `banhist` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('listbanhist').";", [$uz]);

                    echo '<form action="/admin/banhist?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                    while ($data = $queryhist -> fetch()) {
                        echo '<div class="b">';
                        echo '<div class="img">'.user_avatars($data['user']).'</div>';
                        echo '<b>'.profile($data['user']).'</b> '.user_online($data['user']).' ';

                        echo '<small>('.date_fixed($data['time']).')</small><br>';

                        echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> ';
                        echo '<a href="/admin/ban?act=editban&amp;uz='.$data['user'].'">Изменить</a></div>';

                        echo '<div>';
                        if (!empty($data['type'])) {
                            echo 'Причина: '.App::bbCode($data['reason']).'<br>';
                            echo 'Срок: '.formattime($data['term']).'<br>';
                        }

                        switch ($data['type']) {
                            case '1': $stat = '<span style="color:#ff0000">Забанил</span>:';
                                break;
                            case '2': $stat = '<span style="color:#ffa500">Изменил</span>:';
                                break;
                            default: $stat = '<span style="color:#00cc00">Разбанил</span>:';
                        }

                        echo $stat.' '.profile($data['send']).'<br>';

                        echo '</div>';
                    }

                    echo '<br><input type="submit" value="Удалить выбранное"></form>';

                    App::pagination($page);

                    echo 'Всего действий: <b>'.$total.'</b><br><br>';

                } else {
                    show_error('Истории банов еще нет!');
                }
            } else {
                show_error('Ошибка! Данный пользователь не найден!');
            }
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/banhist">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Удаление банов                                       ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `banhist` WHERE `id` IN (".$del.");");

                    App::setFlash('success', 'Выбранные баны успешно удалены!');
                    App::redirect("/admin/banhist?page=$page");
                } else {
                    show_error('Ошибка! Отсутствуют выбранные баны!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/banhist?page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');
