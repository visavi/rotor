<?php
App::view(Setting::get('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin([101, 102, 103])) {
    //show_title('Управление антиматом');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case "index":

            echo 'Все слова в списке будут заменяться на ***<br />';
            echo 'Чтобы удалить слово нажмите на него, добавить слово можно в форме ниже<br /><br />';

            $querymat = DB::run() -> query("SELECT * FROM antimat;");
            $arrmat = $querymat -> fetchAll();
            $total = count($arrmat);

            if ($total > 0) {
                foreach($arrmat as $key => $value) {
                    if ($key == 0) {
                        $comma = '';
                    } else {
                        $comma = ', ';
                    }
                    echo $comma.'<a href="/admin/antimat?act=del&amp;id='.$value['id'].'&amp;uid='.$_SESSION['token'].'">'.$value['string'].'</a>';
                }

                echo '<br /><br />';
            } else {
                show_error('Список пуст, добавьте слово!');
            }

            echo '<div class="b">';
            echo 'Добавить слово:<br />';
            echo '<form action="/admin/antimat?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<input type="text" name="mat" />';
            echo '<input type="submit" value="Добавить" /></form></div><br />';

            echo 'Всего слов в базе: <b>'.$total.'</b><br /><br />';

            if (is_admin([101]) && $total > 0) {
                echo '<i class="fa fa-times"></i> <a href="/admin/antimat?act=prodel">Очистить</a><br />';
            }
        break;

        ############################################################################################
        ##                                Добавление в список                                     ##
        ############################################################################################
        case "add":

            $uid = check($_GET['uid']);
            $mat = check(utf_lower($_POST['mat']));

            if ($uid == $_SESSION['token']) {
                if (!empty($mat)) {
                    $querymat = DB::run() -> querySingle("SELECT id FROM antimat WHERE string=? LIMIT 1;", [$mat]);
                    if (empty($querymat)) {
                        DB::run() -> query("INSERT INTO antimat (string) VALUES (?);", [$mat]);

                        App::setFlash('success', 'Слово успешно добавлено в список антимата!');
                        App::redirect("/admin/antimat");

                    } else {
                        show_error('Ошибка! Введенное слово уже имеетеся в списке!');
                    }
                } else {
                    show_error('Ошибка! Вы не ввели слово для занесения в список!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Удаление из списка                                   ##
        ############################################################################################
        case "del":

            $uid = check($_GET['uid']);
            $id = intval($_GET['id']);

            if ($uid == $_SESSION['token']) {
                if (!empty($id)) {
                    DB::run() -> query("DELETE FROM antimat WHERE id=?;", [$id]);

                    App::setFlash('success', 'Слово успешно удалено из списка антимата!');
                    App::redirect("/admin/antimat");

                } else {
                    show_error('Ошибка удаления! Отсутствуют выбранное слово!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Подтверждение очистки                                  ##
        ############################################################################################
        case "prodel":

            echo 'Вы уверены что хотите удалить все слова в антимате?<br />';
            echo '<i class="fa fa-times"></i> <b><a href="/admin/antimat?act=clear&amp;uid='.$_SESSION['token'].'">Да уверен!</a></b><br /><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Очистка антимата                                    ##
        ############################################################################################
        case "clear":

            $uid = check($_GET['uid']);

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("DELETE FROM antimat;");

                    App::setFlash('success', 'Список антимата успешно очищен!');
                    App::redirect("/admin/antimat");

                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Очищать гостевую могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/antimat">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');
