<?php


    switch ($action):



        break;

        ############################################################################################
        ##                                    Удаление сообщений                                  ##
        ############################################################################################
        case 'delposts':

            $token = check($_GET['token']);
            $del = intar(Request::input('del'));

            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);
                    $del = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    $queryfiles = DB::select("SELECT `hash` FROM `files_forum` WHERE `post_id` IN (".$del.");");
                    $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($files)){
                        foreach ($files as $file){
                            deleteImage('uploads/forum/', $topics['id'].'/'.$file);
                        }
                    }

                    DB::delete("DELETE FROM `files_forum` WHERE `post_id` IN (".$del.");");
                    // ------ Удаление загруженных файлов -------//

                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `id` IN (".$del.") AND `topic_id`=".$tid.";");
                    DB::update("UPDATE `topics` SET posts=posts-? WHERE `id`=?;", [$delposts, $tid]);
                    DB::update("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $topics['forum_id']]);

                    setFlash('success', 'Выбранные сообщения успешно удалены!');
                    redirect("/admin/forum?act=topic&tid=$tid&page=$page");

                } else {
                    showError('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Подготовка к редактированию                          ##
        ############################################################################################
        case 'editpost':

            $pid = abs(intval($_GET['pid']));

            $post = Post::query()->where('id', $pid)->with('files')->first();

            if (!empty($post)) {

                echo '<i class="fa fa-pencil-alt"></i> <b>'.profile($post->user).'</b> <small>('.dateFixed($post['created_at']).')</small><br><br>';

                echo '<div class="form" id="form">';
                echo '<form action="/admin/forum?act=addeditpost&amp;tid='.$post['topic_id'].'&amp;pid='.$pid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br>';
                echo '<textarea class="markItUp" cols="25" rows="10" name="msg">'.$post['text'].'</textarea><br>';

                if ($post->files->isNotEmpty()){
                    echo '<i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br>';
                    foreach ($post->files as $file){
                        echo '<input type="checkbox" name="delfile[]" value="'.$file['id'].'"> ';
                        echo '<a href="/uploads/forum/'.$post['topic_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatSize($file['size']).')<br>';
                    }
                    echo '<br>';
                }

                echo '<input value="Редактировать" name="do" type="submit"></form></div><br>';
            } else {
                showError('Ошибка! Данного сообщения не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Редактирование сообщения                            ##
        ############################################################################################
        case 'addeditpost':

            $pid     = int(Request::input('pid'));
            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));

            if ($token == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) <= setting('forumtextlength')) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `id`=? LIMIT 1;", [$pid]);
                    if (!empty($post)) {

                        DB::update("UPDATE `posts` SET `text`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [$msg, getUser('id'), SITETIME, $pid]);

                        // ------ Удаление загруженных файлов -------//
                        if ($delfile) {
                            $del = implode(',', $delfile);
                            $queryfiles = DB::select("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$pid, Post::class]);
                            $files = $queryfiles->fetchAll();

                            if (!empty($files)){
                                foreach ($files as $file){
                                    deleteImage('uploads/forum/', $post['topic_id'].'/'.$file['hash']);
                                }
                                DB::delete("DELETE FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$pid, Post::class]);
                            }
                        }
                        // ------ Удаление загруженных файлов -------//


                        setFlash('success', 'Сообщение успешно отредактировано!');
                        redirect("/admin/forum?act=topic&tid=$tid&page=$page");

                    } else {
                        showError('Ошибка! Данного сообщения не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;
