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

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/profile">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                Загрузка фото и аватара                                 ##
        ############################################################################################
        case 'upload':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
                    if (is_flood($log)) {

                        // ------------------------------------------------------//
                        $handle = upload_image($_FILES['photo'], $config['filesize'], $config['fileupfoto'], $log);
                        if ($handle) {

                            //-------- Удаляем старую фотку и аватар ----------//
                            $userpic = DB::run()->querySingle("SELECT picture, avatar FROM users WHERE login=? LIMIT 1;", [$log]);

                            if (!empty($userpic['picture'])){
                                unlink_image('upload/photos/', $userpic['picture']);
                                unlink_image('upload/avatars/', $userpic['avatar']);

                                DB::run()->query("UPDATE `users` SET `picture`=?, `avatar`=? WHERE `login`=?;", ['', '', $log]);
                            }

                            //-------- Генерируем аватар ----------//
                            $handle->process(HOME.'/upload/photos/');
                            $picture = $handle -> file_dst_name;

                            $handle->file_new_name_body = $log;
                            $handle->image_resize = true;
                            $handle->image_ratio_crop      = true;
                            $handle->image_y = 48;
                            $handle->image_x = 48;
                            $handle->image_watermark = false;
                            $handle->image_convert = 'png';
                            $handle->file_overwrite = true;

                            $handle->process(HOME.'/upload/avatars/');
                            $avatar = $handle -> file_dst_name;

                            if ($handle->processed) {

                                DB::run()->query("UPDATE `users` SET `picture`=?, `avatar`=? WHERE `login`=?;", [$picture, $avatar, $log]);

                                $handle->clean();

                                save_avatar();

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
                    show_error('Ошибка! Не удалось загрузить фотографию!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/pictures">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Удаление фото и аватара                               ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                $userpic = DB::run() -> querySingle("SELECT `picture` FROM `users` WHERE `login`=? LIMIT 1;", [$log]);

                if (!empty($userpic)){

                    unlink_image('upload/photos/', $userpic);
                    DB::run() -> query("UPDATE `users` SET `picture`=? WHERE `login`=?", ['', $log]);

                    notice('Фотография успешно удалена!');
                    redirect("/profile");

                } else {
                    show_error('Ошибка! Фотографии для удаления не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/profile">Вернуться</a><br />';
        break;

    endswitch;

} else {
    show_login('Вы не авторизованы, чтобы загружать фотографии, необходимо');
}

App::view($config['themes'].'/foot');
