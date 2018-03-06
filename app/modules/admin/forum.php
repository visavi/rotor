<?php


    switch ($action):

        ############################################################################################
        ##                                     Просмотр сообщений                                 ##
        ############################################################################################
        case 'topic':
            if (!empty($tid)) {
                $topic = DB::run() -> queryFetch("SELECT `t`.*, `f`.`title` forum_title, `f`.parent_id FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` WHERE t.`id`=? LIMIT 1;", [$tid]);

                if (!empty($topic)) {
                    echo '<a href="/admin/forum">Форум</a> / ';

                    if (!empty($topic['parent'])) {
                        $forums = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", [$topic['parent']]);
                        echo '<a href="/admin/forum?fid='.$forums['id'].'">'.$forums['title'].'</a> / ';
                    }

                    echo '<a href="/admin/forum?act=forum&amp;fid='.$topic['forum_id'].'">'.$topic['forum_title'].'</a> / ';
                    echo '<a href="/topic/'.$tid.'?page='.$page.'">Обзор темы</a><br><br>';

                    echo '<i class="fab fa-forumbee fa-lg text-muted"></i> <b>'.$topic['title'].'</b>';

                    if (!empty($topic['moderators'])) {
                        $moderators = User::query()->whereIn('id', explode(',', $topic['moderators']))->get();

                        echo '<br>Кураторы темы: ';
                        foreach ($moderators as $mkey => $mval) {
                            $comma = (empty($mkey)) ? '' : ', ';
                            echo $comma . profile($mval);
                        }
                    }

                    if (!empty($topic['note'])){
                        echo '<div class="info">'.bbCode($topic['note']).'</div>';
                    }

                    echo '<hr>';

                    if (empty($topic['closed'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Закрыть</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=open&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Открыть</a> / ';
                    }

                    if (empty($topic['locked'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Закрепить</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Открепить</a> / ';
                    }

                    echo '<a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;page='.$page.'">Изменить</a> / ';
                    echo '<a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Переместить</a> / ';
                    echo '<a href="/admin/forum?act=deltopics&amp;fid='.$topic['id'].'&amp;del='.$tid.'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную тему?\')">Удалить</a><br>';

                    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `topic_id`=?;", [$tid]);

                    if ($total > 0) {
                        $page = paginate(setting('forumpost'), $total);



                        $posts = Post::select('posts.*', 'pollings.vote')
                            ->where('topic_id', $tid)
                            ->leftJoin ('pollings', function($join) {
                                $join->on('posts.id', '=', 'pollings.relate_id')
                                    ->where('pollings.relate_type', '=', Post::class);
                            })
                            ->with('files', 'user', 'editUser')
                            ->offset($page['offset'])
                            ->limit($page['limit'])
                            ->orderBy('created_at', 'asc')
                            ->get();

                        echo '<form action="/admin/forum?act=delposts&amp;tid='.$tid.'&amp;page='.$page['current'].'&amp;token='.$_SESSION['token'].'" method="post">';

                        echo '<div align="right" class="form">';
                        echo '<b><label for="all">Отметить все</label></b> <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">&nbsp;';
                        echo '</div>';

                        foreach ($posts as $key=>$data){
                            $num = ($page['offset'] + $key + 1);

                            echo '<div class="b">';

                            echo '<div class="img">'.userAvatar($data->user).'</div>';
                            echo '<span class="imgright"><a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Ред.</a> <input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';


                            echo $num.'. <b>'.profile($data['user']).'</b>  <small>('.dateFixed($data['created_at']).')</small><br>';
                            echo userStatus($data->user).' '.userOnline($data->user).'</div>';

                            echo '<div>'.bbCode($data['text']).'<br>';

                            // -- Прикрепленные файлы -- //
                            if ($data->files->isNotEmpty()) {
                                echo '<div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br>';
                                foreach ($data->files as $file){
                                    $ext = getExtension($file['hash']);
                                    echo icons($ext).' ';

                                    echo '<a href="/uploads/forum/'.$data['topic_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatSize($file['size']).')<br>';
                                }
                                echo '</div>';

                            }
                            // --------------------------//

                            if (!empty($data['updated_at'])) {
                                echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.$data->editUser->login.' ('.dateFixed($data['updated_at']).')</small><br>';
                            }

                            echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span></div>';
                        }

                        echo '<span class="imgright"><input type="submit" value="Удалить выбранное"></span></form>';

                        pagination($page);

                    } else {
                        showError('Сообщений еще нет, будь первым!');
                    }

                    if (empty($topic['closed'])) {
                        echo '<div class="form" id="form">';
                        echo '<form action="/topic/create/'.$tid.'" method="post" enctype="multipart/form-data">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo 'Сообщение:<br>';
                        echo '<textarea class="markItUp" cols="25" rows="5" name="msg"></textarea><br>';

                        echo '<div class="js-attach-form" style="display: none;">
                            Прикрепить файл:<br><input type="file" name="file"><br>
                            <div class="info">
                                Максимальный вес файла: <b>'.round(setting('forumloadsize')/1024).'</b> Kb<br>
                                Допустимые расширения: '.str_replace(',', ', ', setting('forumextload')).'
                            </div><br>
                        </div>';

                        echo '<span class="imgright js-attach-button"><a href="#" onclick="return showAttachForm();">Загрузить файл</a></span>';

                        echo '<input type="submit" value="Написать">';
                        echo '</form></div><br>';

                    } else {
                        showError('Данная тема закрыта для обсуждения!');
                    }
                } else {
                    showError('Ошибка! Данной темы не существует!');
                }
            } else {
                showError('Ошибка! Не выбрана тема!');
            }
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
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
                    DB::update("UPDATE `topics` SET posts2=posts2-? WHERE `id`=?;", [$delposts, $tid]);
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
