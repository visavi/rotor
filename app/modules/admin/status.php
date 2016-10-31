<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin(array(101, 102))) {
    show_title('Управление статусами');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $querystatus = DB::run() -> query("SELECT * FROM `status` ORDER BY `status_topoint` DESC;");
            $status = $querystatus -> fetchAll();
            $total = count($status);

            if ($total > 0) {
                echo '<b>Статус</b>  — <b>Актив</b><br />';
                foreach ($status as $statval) {
                    echo '<div class="b">';
                    echo '<img src="/assets/img/images/user.gif" alt="image" /> ';

                    if (empty($statval['status_color'])) {
                        echo '<b>'.$statval['status_name'].'</b> <small>('.$statval['status_topoint'].'-'.$statval['status_point'].')</small><br />';
                    } else {
                        echo '<b><span style="color:'.$statval['status_color'].'">'.$statval['status_name'].'</span></b> <small>('.$statval['status_topoint'].'-'.$statval['status_point'].')</small><br />';
                    }
                    echo '</div>';
                    echo '<a href="/admin/status?act=edit&amp;id='.$statval['status_id'].'">Изменить</a> / ';
                    echo '<a href="/admin/status?act=del&amp;id='.$statval['status_id'].'&amp;uid='.$_SESSION['token'].'">Удалить</a><br />';
                }
                echo '<br />Всего статусов: <b>'.$total.'</b><br /><br />';
            } else {
                show_error('Статусы еще не назначены!');
            }

            echo '<img src="/assets/img/images/open.gif" alt="image" /> <a href="/admin/status?act=add">Создать</a><br />';
        break;

        ############################################################################################
        ##                                 Подготовка к редактированию                            ##
        ############################################################################################
        case "edit":

            $id = abs(intval($_GET['id']));

            $status = DB::run() -> queryFetch("SELECT * FROM `status` WHERE `status_id`=? LIMIT 1;", array($id));

            if (!empty($status)) {
                echo '<b><big>Изменение статуса</big></b><br /><br />';

                echo '<div class="form">';
                echo '<form action="/admin/status?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'От:<br />';
                echo '<input type="text" name="topoint" maxlength="10" value="'.$status['status_topoint'].'" /><br />';
                echo 'До:<br />';
                echo '<input type="text" name="point" maxlength="10" value="'.$status['status_point'].'" /><br />';
                echo 'Статус:<br />';
                echo '<input type="text" name="name" maxlength="30" value="'.$status['status_name'].'" /><br />';
                echo 'Цвет:<br />';
                echo '<input type="text" name="color" maxlength="7" value="'.$status['status_color'].'" /><br />';

                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данного статуса не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Редактирование статусов                               ##
        ############################################################################################
        case "change":

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));
            $topoint = abs(intval($_POST['topoint']));
            $point = abs(intval($_POST['point']));
            $name = check($_POST['name']);
            $color = check($_POST['color']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($name) >= 5 && utf_strlen($name) < 30) {
                    if (preg_match('|^#+[A-z0-9]{6}$|', $color) || empty($color)) {
                        DB::run() -> query("UPDATE `status` SET `status_topoint`=?, `status_point`=?, `status_name`=?, `status_color`=? WHERE `status_id`=?;", array($topoint, $point, $name, $color, $id));

                        $_SESSION['note'] = 'Статус успешно изменен!';
                        redirect("/admin/status");
                    } else {
                        show_error('Ошибка! Недопустимый формат цвета статуса!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое название статуса!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status?act=edit&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                            Подготовка к добавлению статуса                             ##
        ############################################################################################
        case 'add':

            echo '<b><big>Создание статуса</big></b><br /><br />';

            echo '<div class="form">';
            echo '<form action="/admin/status?act=create&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'От:<br />';
            echo '<input type="text" name="topoint" maxlength="10" /><br />';
            echo 'До:<br />';
            echo '<input type="text" name="point" maxlength="10" /><br />';
            echo 'Статус:<br />';
            echo '<input type="text" name="name" maxlength="30" /><br />';
            echo 'Цвет:<br />';
            echo '<input type="text" name="color" maxlength="7" /><br />';

            echo '<input type="submit" value="Создать" /></form></div><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                            Подготовка к добавлению статуса                             ##
        ############################################################################################
        case 'create':

            $uid = check($_GET['uid']);
            $topoint = abs(intval($_POST['topoint']));
            $point = abs(intval($_POST['point']));
            $name = check($_POST['name']);
            $color = check($_POST['color']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($name) >= 5 && utf_strlen($name) < 30) {
                    if (preg_match('|^#+[A-z0-9]{6}$|', $color) || empty($color)) {
                        DB::run() -> query("INSERT INTO `status` (`status_topoint`, `status_point`, `status_name`, `status_color`) VALUES (?, ?, ?, ?);", array($topoint, $point, $name, $color));

                        $_SESSION['note'] = 'Статус успешно добавлен!';
                        redirect("/admin/status");
                    } else {
                        show_error('Ошибка! Недопустимый формат цвета статуса!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое название статуса!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status?act=add">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Удаление статуса                                    ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if ($uid == $_SESSION['token']) {
                if (!empty($id)) {
                    DB::run() -> query("DELETE FROM `status` WHERE `status_id`=?;", array($id));

                    $_SESSION['note'] = 'Статус успешно удален!';
                    redirect("/admin/status");
                } else {
                    show_error('Ошибка! Отсутствует выбранный статус для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
