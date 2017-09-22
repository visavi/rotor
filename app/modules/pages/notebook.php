<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

//show_title('Блокнот');

if (isUser()) {
    switch ($action):
        ############################################################################################
        ##                                    Главная страница                                    ##
        ############################################################################################
        case "index":
            $note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `user`=? LIMIT 1;", [user('login')]);

            echo 'Здесь вы можете хранить отрывки сообщений или любую другую важную информацию<br><br>';

            if (!empty($note['text'])) {
                echo '<div>Личная запись:<br>';
                echo bbCode($note['text']).'</div><br>';

                echo 'Последнее изменение: '.dateFixed($note['time']).'<br><br>';
            } else {
                showError('Запись пустая или отсутствует!');
            }

            echo '<i class="fa fa-pencil"></i> <a href="/notebook?act=edit">Редактировать</a><br>';
            break;

        ############################################################################################
        ##                                   Редактирование записи                                ##
        ############################################################################################
        case "edit":

            $note = DB::run() -> queryFetch("SELECT * FROM `notebook` WHERE `user`=? LIMIT 1;", [user('login')]);

            echo '<div class="form">';
            echo '<form action="/notebook?act=change&amp;uid='.$_SESSION['token'].'" method="post">';
            echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$note['text'].'</textarea><br>';
            echo '<input type="submit" value="Сохранить"></form></div><br>';

            echo '* Доступ к личной записи не имеет никто кроме вас<br><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/notebook">Вернуться</a><br>';
            break;

        ############################################################################################
        ##                                    Сохранение записи                                   ##
        ############################################################################################
        case "change":

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($msg) < 10000) {

                    $querynote = DB::run() -> querySingle("SELECT `id` FROM `notebook` WHERE `user`=? LIMIT 1;", [user('login')]);
                    if (!empty($querynote)) {
                        DB::update("UPDATE `notebook` SET `text`=?, `time`=? WHERE `user`=?", [$msg, SITETIME, user('login')]);
                    } else {
                        DB::insert("INSERT INTO `notebook` (`user`, `text`, `time`) VALUES (?, ?, ?);", [user('login'), $msg, SITETIME]);
                    }

                    setFlash('success', 'Запись успешно сохранена!');
                    redirect("/notebook");
                } else {
                    showError('Ошибка! Слишком длинная запись, не более 10тыс. символов!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/notebook?act=edit">Вернуться</a><br>';
            break;

    endswitch;

} else {
    showError('Для сохранения заметок необходимо авторизоваться');
}

view(setting('themes').'/foot');
