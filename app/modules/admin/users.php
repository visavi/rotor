<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} elseif (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} else {
    $uz = '';
}

if (isAdmin([101, 102])) {
    //show_title('Управление пользователями');

    switch ($action):




        ############################################################################################
        ##                           Подтверждение удаление профиля                               ##
        ############################################################################################
        case 'poddel':

            echo '<i class="fa fa-times"></i> Вы подтверждаете, что хотите полностью удалить аккаунт пользователя <b>'.$uz.'</b>?<br><br>';

            echo '<div class="form">';
            echo '<form action="/admin/users?act=deluser&amp;uz='.$uz.'&amp;uid='.$_SESSION['token'].'" method="post">';

            echo '<b>Добавить в черный список:</b><br>';
            echo 'Логин пользователя: <input name="loginblack" type="checkbox" value="1"  checked="checked"><br>';
            echo 'Email пользователя: <input name="mailblack" type="checkbox" value="1"  checked="checked"><br><br>';

            echo '<b>Удаление сообщений:</b><br>';
            echo 'Темы в форуме: <input name="deltopicforum" type="checkbox" value="1"><br>';
            echo 'Темы и сообщения: <input name="delpostforum" type="checkbox" value="1"><br>';
            echo 'Комментарии в галерее: <input name="delcommphoto" type="checkbox" value="1"><br>';
            echo 'Комментарии в новостях: <input name="delcommnews" type="checkbox" value="1"><br>';
            echo 'Комментарии в блогах: <input name="delcommblog" type="checkbox" value="1"><br>';
            echo 'Комментарии в загрузках: <input name="delcommload" type="checkbox" value="1"><br>';
            echo 'Фотографии в галерее: <input name="delimages" type="checkbox" value="1"><br><br>';

            echo '<input type="submit" value="Удалить профиль"></form></div><br>';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users?act=edit&amp;uz='.$uz.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/users">Выбор юзера</a><br>';
            break;
        ############################################################################################
        ##                                   Удаление профиля                                     ##
        ############################################################################################
        case 'deluser':

            $uid = check($_GET['uid']);
            $loginblack = (empty($_POST['loginblack'])) ? 0 : 1;
            $mailblack = (empty($_POST['mailblack'])) ? 0 : 1;
            $deltopicforum = (empty($_POST['deltopicforum'])) ? 0 : 1;
            $delpostforum = (empty($_POST['delpostforum'])) ? 0 : 1;
            $delcommphoto = (empty($_POST['delcommphoto'])) ? 0 : 1;
            $delcommnews = (empty($_POST['delcommnews'])) ? 0 : 1;
            $delcommblog = (empty($_POST['delcommblog'])) ? 0 : 1;
            $delcommload = (empty($_POST['delcommload'])) ? 0 : 1;
            $delimages = (empty($_POST['delimages'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                $user = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$uz]);

                if (!empty($user)) {
                    if ($user['level'] < 101 || $user['level'] > 105) {

                        // -------------//
                        if (!empty($mailblack)) {
                            $blackmail = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [1, $user['email']]);
                            if (empty($blackmail) && !empty($user['email'])) {
                                DB::insert("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [1, $user['email'], getUser('login'), SITETIME]);
                            }
                        }

                        // -------------//
                        if (!empty($loginblack)) {
                            $blacklogin = DB::run() -> querySingle("SELECT `id` FROM `blacklist` WHERE `type`=? AND `value`=? LIMIT 1;", [2, strtolower($user['login'])]);
                            if (empty($blacklogin)) {
                                DB::insert("INSERT INTO `blacklist` (`type`, `value`, `user`, `time`) VALUES (?, ?, ?, ?);", [2, $user['login'], getUser('login'), SITETIME]);
                            }
                        }

                        // ------ Удаление фотографий в галерее -------//
                        if (!empty($delimages)) {
                            deleteAlbum($uz);
                        }

                        // ------ Удаление тем в форуме -------//
                        if (!empty($delpostforum) || !empty($deltopicforum)) {

                            $query = DB::select("SELECT `id` FROM `topics` WHERE `author`=?;", [$uz]);
                            $topics = $query -> fetchAll(PDO::FETCH_COLUMN);

                            if (!empty($topics)){
                                $strtopics = implode(',', $topics);

                                // ------ Удаление загруженных файлов -------//
                                foreach($topics as $delDir){
                                    removeDir(UPLOADS.'/forum/'.$delDir);
                                }
                                DB::delete("DELETE FROM `files_forum` WHERE `post_id` IN (".$strtopics.");");
                                // ------ Удаление загруженных файлов -------//

                                DB::delete("DELETE FROM `posts` WHERE `topic_id` IN (".$strtopics.");");
                                DB::delete("DELETE FROM `topics` WHERE `id` IN (".$strtopics.");");
                            }

                            // ------ Удаление сообщений в форуме -------//
                            if (!empty($delpostforum)) {
                                DB::delete("DELETE FROM `posts` WHERE `user`=?;", [$uz]);

                                // ------ Удаление загруженных файлов -------//
                                $queryfiles = DB::select("SELECT * FROM `files_forum` WHERE `user`=?;", [$uz]);
                                $files = $queryfiles->fetchAll();

                                if (!empty($files)){
                                    foreach ($files as $file){
                                        if (file_exists(UPLOADS.'/forum/'.$file['topic_id'].'/'.$file['hash'])){
                                            unlink(UPLOADS.'/forum/'.$file['topic_id'].'/'.$file['hash']);
                                        }
                                    }
                                    DB::delete("DELETE FROM `files_forum` WHERE `user`=?;", [$uz]);
                                }
                                // ------ Удаление загруженных файлов -------//
                            }

                            restatement('forum');
                        }

                        // ------ Удаление коментарий -------//
                        if (!empty($delcommblog)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['blog', $uz]);
                            restatement('blog');
                        }

                        if (!empty($delcommload)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['down', $uz]);
                            restatement('load');
                        }

                        if (!empty($delcommphoto)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", [Photo::class, $uz]);
                            restatement('photo');
                        }

                        if (!empty($delcommnews)) {
                            DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `user`=?;", ['news', $uz]);
                            restatement('news');
                        }
// @TODO: добавит остальные комментарии всего их 6 тут 4
                        // Удаление профиля
                        deleteUser($uz);

                        echo '<i class="fa fa-check"></i> <b>Профиль пользователя успешно удален!</b><br><br>';
                    } else {
                        showError('Ошибка! У вас недостаточно прав для удаления этого профиля!');
                    }
                } else {
                    showError('Ошибка! Пользователя с данным логином не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/users">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
