<?php
App::view(Setting::get('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

//show_title('Блокнот');

if (is_user()) {
    switch ($action):
        ############################################################################################
        ##                                    Главная страница                                    ##
        ############################################################################################
        case "index":
            $note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `user`=? LIMIT 1;", [App::getUsername()]);

            echo 'Здесь вы можете хранить отрывки сообщений или любую другую важную информацию<br /><br />';

            if (!empty($note['text'])) {
                echo '<div>Личная запись:<br />';
                echo App::bbCode($note['text']).'</div><br />';

                echo 'Последнее изменение: '.date_fixed($note['time']).'<br /><br />';
            } else {
                show_error('Запись пустая или отсутствует!');
            }

            echo '<i class="fa fa-pencil"></i> <a href="/notebook?act=edit">Редактировать</a><br />';
            break;

        ############################################################################################
        ##                                   Редактирование записи                                ##
        ############################################################################################
        case "edit":

            $note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `user`=? LIMIT 1;", [App::getUsername()]);

            echo '<div class="form">';
            echo '<form action="/notebook?act=change&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$note['text'].'</textarea><br />';
            echo '<input type="submit" value="Сохранить" /></form></div><br />';

            echo '* Доступ к личной записи не имеет никто кроме вас<br /><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/notebook">Вернуться</a><br />';
            break;

        ############################################################################################
        ##                                    Сохранение записи                                   ##
        ############################################################################################
        case "change":

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) < 10000) {

                    $querynote = DB::run() -> querySingle("SELECT `id` FROM `notebook` WHERE `user`=? LIMIT 1;", [App::getUsername()]);
                    if (!empty($querynote)) {
                        DB::run() -> query("UPDATE `notebook` SET `text`=?, `time`=? WHERE `user`=?", [$msg, SITETIME, App::getUsername()]);
                    } else {
                        DB::run() -> query("INSERT INTO `notebook` (`user`, `text`, `time`) VALUES (?, ?, ?);", [App::getUsername(), $msg, SITETIME]);
                    }

                    App::setFlash('success', 'Запись успешно сохранена!');
                    App::redirect("/notebook");
                } else {
                    show_error('Ошибка! Слишком длинная запись, не более 10тыс. символов!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/notebook?act=edit">Вернуться</a><br />';
            break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы сохранять заметки, необходимо');
}

App::view(Setting::get('themes').'/foot');
