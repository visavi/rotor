<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

show_title('Загрузка фотографии');

if (is_user()) {
    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<div class="form">';
            echo '<form action="/pictures?act=upload&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
            echo 'Прикрепить фото:<br />';
            echo '<input type="file" name="photo" /><br />';
            echo '<input type="submit" value="Загрузить" /></form></div><br />';

            echo 'Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br />';
            echo 'Весом не более '.formatsize($config['filesize']).' и размером от 100 до '.(int)$config['fileupfoto'].' px<br /><br />';

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/profile">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Загрузка аватара                                    ##
        ############################################################################################
        case 'upload':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
                    if (is_quarantine($log)) {
                        if (is_flood($log)) {

                            // ------------------------------------------------------//
                            $handle = upload_image($_FILES['photo'], $config['filesize'], $config['fileupfoto'], $log);
                            if ($handle) {

                                //-------- Удаляем старую фотку ----------//
                                $userpic = DB::run() -> querySingle("SELECT `users_picture` FROM `users` WHERE `users_login`=? LIMIT 1;", array($log));

                                if (!empty($userpic)){
                                    unlink_image('upload/photos/', $userpic);
                                    DB::run() -> query("UPDATE `users` SET `users_picture`=? WHERE `users_login`=?;", array('', $log));
                                }
                                //-------- Удаляем старую фотку ----------//

                                $handle -> process(BASEDIR.'/upload/photos/');

                                if ($handle -> processed) {
                                    DB::run() -> query("UPDATE `users` SET `users_picture`=? WHERE `users_login`=?;", array($handle -> file_dst_name, $log));

                                    $handle -> clean();

                                    notice('Фотография успешно загружена!');
                                    redirect("/profile");

                                } else {
                                    show_error($handle -> error);
                                }
                            } else {
                                show_error('Ошибка! Не удалось загрузить изображение!');
                            }
                        } else {
                            show_error('Антифлуд! Вы слишком часто добавляете фотографии!');
                        }
                    } else {
                        show_error('Карантин! Вы не можете добавлять фото в течении '.round($config['karantin'] / 3600).' часов!');
                    }
                } else {
                    show_error('Ошибка! Не удалось загрузить фотографию!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/pictures">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Удаление фотографии                                   ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                $userpic = DB::run() -> querySingle("SELECT `users_picture` FROM `users` WHERE `users_login`=? LIMIT 1;", array($log));

                if (!empty($userpic)){

                    unlink_image('upload/photos/', $userpic);
                    DB::run() -> query("UPDATE `users` SET `users_picture`=? WHERE `users_login`=?", array('', $log));

                    $_SESSION['note'] = 'Фотография успешно удалена!';
                    redirect("/profile");

                } else {
                    show_error('Ошибка! Фотографии для удаления не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<img src="/images/img/back.gif" alt="image" /> <a href="/profile">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы загружать фотографии, необходимо');
}

App::view($config['themes'].'/foot');
