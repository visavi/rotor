<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isAdmin([101, 102])) {
    //show_title('Управление статусами');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            $querystatus = DB::select("SELECT * FROM `status` ORDER BY `topoint` DESC;");
            $status = $querystatus -> fetchAll();
            $total = count($status);

            if ($total > 0) {
                echo '<b>Статус</b>  — <b>Актив</b><br>';
                foreach ($status as $statval) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-user-circle-o"></i> ';

                    if (empty($statval['color'])) {
                        echo '<b>'.$statval['name'].'</b> <small>('.$statval['topoint'].'-'.$statval['point'].')</small><br>';
                    } else {
                        echo '<b><span style="color:'.$statval['color'].'">'.$statval['name'].'</span></b> <small>('.$statval['topoint'].'-'.$statval['point'].')</small><br>';
                    }
                    echo '</div>';
                    echo '<a href="/admin/status?act=edit&amp;id='.$statval['id'].'">Изменить</a> / ';
                    echo '<a href="/admin/status?act=del&amp;id='.$statval['id'].'&amp;uid='.$_SESSION['token'].'">Удалить</a><br>';
                }
                echo '<br>Всего статусов: <b>'.$total.'</b><br><br>';
            } else {
                showError('Статусы еще не назначены!');
            }

            echo '<i class="fa fa-check"></i> <a href="/admin/status?act=add">Создать</a><br>';
        break;

        ############################################################################################
        ##                                 Подготовка к редактированию                            ##
        ############################################################################################
        case "edit":

            $id = abs(intval($_GET['id']));

            $status = DB::run() -> queryFetch("SELECT * FROM `status` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($status)) {
                echo '<b><big>Изменение статуса</big></b><br><br>';

                echo '<div class="form">';
                echo '<form action="/admin/status?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'От:<br>';
                echo '<input type="text" name="topoint" maxlength="10" value="'.$status['topoint'].'"><br>';
                echo 'До:<br>';
                echo '<input type="text" name="point" maxlength="10" value="'.$status['point'].'"><br>';
                echo 'Статус:<br>';
                echo '<input type="text" name="name" maxlength="30" value="'.$status['name'].'"><br>';
                echo 'Цвет:<br>';
                echo '<input type="text" name="color" maxlength="7" value="'.$status['color'].'"><br>';

                echo '<input type="submit" value="Изменить"></form></div><br>';
            } else {
                showError('Ошибка! Данного статуса не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br>';
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
                if (utfStrlen($name) >= 5 && utfStrlen($name) < 30) {
                    if (preg_match('|^#+[A-z0-9]{6}$|', $color) || empty($color)) {
                        DB::update("UPDATE `status` SET `topoint`=?, `point`=?, `name`=?, `color`=? WHERE `id`=?;", [$topoint, $point, $name, $color, $id]);

                        setFlash('success', 'Статус успешно изменен!');
                        redirect("/admin/status");
                    } else {
                        showError('Ошибка! Недопустимый формат цвета статуса!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое название статуса!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status?act=edit&amp;id='.$id.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                            Подготовка к добавлению статуса                             ##
        ############################################################################################
        case 'add':

            echo '<b><big>Создание статуса</big></b><br><br>';

            echo '<div class="form">';
            echo '<form action="/admin/status?act=create&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'От:<br>';
            echo '<input type="text" name="topoint" maxlength="10"><br>';
            echo 'До:<br>';
            echo '<input type="text" name="point" maxlength="10"><br>';
            echo 'Статус:<br>';
            echo '<input type="text" name="name" maxlength="30"><br>';
            echo 'Цвет:<br>';
            echo '<input type="text" name="color" maxlength="7"><br>';

            echo '<input type="submit" value="Создать"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br>';
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
                if (utfStrlen($name) >= 5 && utfStrlen($name) < 30) {
                    if (preg_match('|^#+[A-z0-9]{6}$|', $color) || empty($color)) {
                        DB::insert("INSERT INTO `status` (`topoint`, `point`, `name`, `color`) VALUES (?, ?, ?, ?);", [$topoint, $point, $name, $color]);

                        setFlash('success', 'Статус успешно добавлен!');
                        redirect("/admin/status");
                    } else {
                        showError('Ошибка! Недопустимый формат цвета статуса!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое название статуса!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status?act=add">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Удаление статуса                                    ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            $id = abs(intval($_GET['id']));

            if ($uid == $_SESSION['token']) {
                if (!empty($id)) {
                    DB::delete("DELETE FROM `status` WHERE `id`=?;", [$id]);

                    setFlash('success', 'Статус успешно удален!');
                    redirect("/admin/status");
                } else {
                    showError('Ошибка! Отсутствует выбранный статус для удаления!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
