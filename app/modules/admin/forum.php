<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['fid'])) {
    $fid = abs(intval($_GET['fid']));
} else {
    $fid = 0;
}
if (isset($_GET['tid'])) {
    $tid = abs(intval($_GET['tid']));
} else {
    $tid = 0;
}
$page = abs(intval(Request::input('page', 1)));

if (isAdmin()) {
    //show_title('Управление форумом');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $forums = Forum::where('parent_id', 0)
                ->with('lastTopic.lastPost.user')
                ->with('children')
                ->orderBy('sort')
                ->get();

            if (count($forums) > 0) {

                echo '<a href="/forum">Обзор форума</a><hr>';

                foreach($forums as $data) {
                    echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                    echo '<b>'.$data['sort'].'. <a href="/admin/forum?act=forum&amp;fid='.$data['id'].'">'.$data['title'].'</a></b> ('.$data['topics'].'/'.$data['posts'].')';

                    if (!empty($data['desc'])) {
                        echo '<br><small>'.$data['desc'].'</small>';
                    }

                    if (isAdmin([101])) {
                        echo '<br><a href="/admin/forum?act=editforum&amp;fid='.$data['id'].'">Редактировать</a> / ';
                        echo '<a href="/admin/forum?act=delforum&amp;fid='.$data['id'].'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы уверены что хотите удалить данный раздел? \')">Удалить</a>';
                    }

                    echo '</div><div>';
                    // ----------------------------------------------------//
                    if ($data->children->isNotEmpty()) {
                        foreach($data->children as $datasub) {
                            echo '<i class="fa fa-angle-right"></i> ';
                            echo '<b>'.$datasub['sort'].'. <a href="/admin/forum?act=forum&amp;fid='.$datasub['id'].'">'.$datasub['title'].'</a></b>  ('.$datasub['topics'].'/'.$datasub['posts'].') ';
                            if (isAdmin([101])) {
                                echo '(<a href="/admin/forum?act=editforum&amp;fid='.$datasub['id'].'">Редактировать</a> / ';
                                echo '<a href="/admin/forum?act=delforum&amp;fid='.$datasub['id'].'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы уверены что хотите удалить данный раздел? \')">Удалить</a>)';
                            }
                            echo '<br>';
                        }
                    } ?>

                    <?php if ($data->lastTopic->lastPost): ?>

                        Тема: <a href="/topic/<?= $data->lastTopic->id ?>/end"><?= $data->lastTopic->title ?></a>
                        <br/>
                        Сообщение: <?php $data->lastTopic->lastPost->user->login ?> (<?= dateFixed($data->lastTopic->lastPost->created_at) ?>)
                    <?php else: ?>
                        Темы еще не созданы!
                    <?php endif ?>
                    <?php
                    echo '</div>';
                }
            } else {
                showError('Разделы форума еще не созданы!');
            }

            if (isAdmin([101])) {
                echo '<hr><form action="/admin/forum?act=addforum" method="post">';
                echo '<input type="hidden" name="token" value="'. $_SESSION['token'] .'">';
                echo 'Заголовок:<br>';
                echo '<input type="text" name="title" maxlength="50">';
                echo '<input type="submit" value="Создать раздел"></form><hr>';

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=restatement&amp;token='.$_SESSION['token'].'">Пересчитать</a><br>';
            }

        break;

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'restatement':

            $token = check($_GET['token']);

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {
                    restatement('forum');

                    setFlash('success', 'Все данные успешно пересчитаны!');
                    redirect("/admin/forum");

                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Пересчитывать сообщения могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Добавление разделов                                 ##
        ############################################################################################
        case 'addforum':

            $token = check($_POST['token']);
            $title = check($_POST['title']);

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {
                    if (utfStrlen($title) >= 3 && utfStrlen($title) <= 50) {
                        $maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(sort),0)+1 FROM `forums`;");
                        DB::insert("INSERT INTO `forums` (sort, `title`) VALUES (?, ?);", [$maxorder, $title]);

                        setFlash('success', 'Новый раздел успешно добавлен!');
                        redirect("/admin/forum");

                    } else {
                        showError('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Добавлять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                          Подготовка к редактированию разделов                          ##
        ############################################################################################
        case 'editforum':

            if (isAdmin([101])) {
                $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", [$fid]);
                if (!empty($forums)) {
                    echo '<b><big>Редактирование</big></b><br><br>';

                    echo '<div class="form">';
                    echo '<form action="/admin/forum?act=addeditforum&amp;fid='.$fid.'&amp;token='.$_SESSION['token'].'" method="post">';
                    echo 'Раздел: <br>';
                    echo '<input type="text" name="title" maxlength="50" value="'.$forums['title'].'"><br>';

                    $query = DB::select("SELECT `id`, `title`, `parent_id` FROM `forums` WHERE `parent_id`=? ORDER BY sort ASC;", [0]);
                    $section = $query -> fetchAll();

                    echo 'Родительский форум:<br>';
                    echo '<select name="parent">';
                    echo '<option value="0">Основной форум</option>';

                    foreach ($section as $data) {
                        if ($fid != $data['id']) {
                            $selected = ($forums['parent_id'] == $data['id']) ? ' selected="selected"' : '';
                            echo '<option value="'.$data['id'].'"'.$selected.'>'.$data['title'].'</option>';
                        }
                    }
                    echo '</select><br>';

                    echo 'Описание: <br>';
                    echo '<input type="text" name="desc" maxlength="100" value="'.$forums['desc'].'"><br>';

                    echo 'Положение: <br>';
                    echo '<input type="text" name="order" maxlength="2" value="'.$forums['sort'].'"><br>';

                    echo 'Закрыть форум: ';
                    $checked = ($forums['closed'] == 1) ? ' checked="checked"' : '';
                    echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                    echo '<input type="submit" value="Изменить"></form></div><br>';
                } else {
                    showError('Ошибка! Данного раздела не существует!');
                }
            } else {
                showError('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Редактирование разделов                                ##
        ############################################################################################
        case 'addeditforum':

            $token = check($_GET['token']);
            $title = check($_POST['title']);
            $desc = check($_POST['desc']);
            $parent = abs(intval($_POST['parent']));
            $order = abs(intval($_POST['order']));
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {
                    if (utfStrlen($title) >= 3 && utfStrlen($title) <= 50) {
                        if (utfStrlen($desc) <= 100) {
                            if ($fid != $parent) {
                                $forums = DB::run() -> queryFetch("SELECT `id` FROM `forums` WHERE `parent_id`=? LIMIT 1;", [$fid]);

                                if (empty($forums) || empty($parent_id)) {
                                    DB::update("UPDATE `forums` SET sort=?, parent_id=?, `title`=?, `desc`=?, `closed`=? WHERE `id`=?;", [$order, $parent, $title, $desc, $closed, $fid]);

                                    setFlash('success', 'Раздел успешно отредактирован!');
                                    redirect("/admin/forum");

                                } else {
                                    showError('Ошибка! Данный раздел имеет подфорумы!');
                                }
                            } else {
                                showError('Ошибка! Недопустимый выбор родительского форума!');
                            }
                        } else {
                            showError('Ошибка! Слишком длинный текст описания раздела!');
                        }
                    } else {
                        showError('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=editforum&amp;fid='.$fid.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">В Форум</a><br>';
        break;

        ############################################################################################
        ##                                    Удаление раздела                                    ##
        ############################################################################################
        case 'delforum':

            $token = check($_GET['token']);

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {

                    $forum = Forum::where('id', $fid)
                        ->with('children')
                        ->first();

                    if ($forum) {
                        if ($forum->children->isEmpty()) {

                            $topic = Topic::where('forum_id', $fid)->first();
                            if (! $topic) {

                                $forum->delete();

                                setFlash('success', 'Раздел успешно удален!');
                                redirect("/admin/forum");

                            } else {
                                showError('Ошибка! В данном разделе имеются темы!');
                            }
                        } else {
                            showError('Ошибка! Данный раздел имеет подфорумы!');
                        }
                    } else {
                        showError('Ошибка! Данного раздела не существует!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Просмотр тем в разделе                              ##
        ############################################################################################
        case 'forum':

            $forum = Forum::with('parent')->find($fid);

            if (!$forum) {
                abort('default', 'Данного раздела не существует!');
            }

            $forum->children = Forum::where('parent_id', $forum->id)
                ->with('lastTopic.lastPost.user')
                ->get();

            echo '<a href="/admin/forum">Форум</a> / ';
            echo '<a href="/forum/'.$fid.'?page='.$page.'">Обзор раздела</a><br><br>';

            echo '<i class="fa fa-forumbee fa-lg text-muted"></i> <b>'.$forum['title'].'</b><hr>';

            $total = Topic::where('forum_id', $fid)->count();

            if ($total > 0) {

                $page = paginate(setting('forumtem'), $total);

                $topics = Topic::where('forum_id', $fid)
                    ->orderBy('locked', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->limit(setting('forumtem'))
                    ->offset($page['offset'])
                    ->with('lastPost.user')
                    ->get();

                echo '<form action="/admin/forum?act=deltopics&amp;fid='.$fid.'&amp;page='.$page['current'].'&amp;token='.$_SESSION['token'].'" method="post">';

                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                foreach ($topics as $topic) {
                    echo '<div class="b">';

                    echo '<i class="fa '.$topic->getIcon().'"></i> ';

                    echo '<b><a href="/admin/forum?act=topic&amp;tid='.$topic['id'].'">'.$topic['title'].'</a></b> ('.$topic['posts'].')<br>';

                    echo '<input type="checkbox" name="del[]" value="'.$topic['id'].'"> ';

                    echo '<a href="/admin/forum?act=edittopic&amp;tid='.$topic['id'].'&amp;page='.$topic['current'].'">Редактировать</a> / ';
                    echo '<a href="/admin/forum?act=movetopic&amp;tid='.$topic['id'].'&amp;page='.$topic['current'].'">Переместить</a></div>';

                    echo '<div>';
                    /*Forum::pagination($topic);*/

                    echo 'Сообщение: '.$topic->lastPost->user->login.' ('.dateFixed($topic->lastPost->created_at).')</div>';
                }

                echo '<br><input type="submit" value="Удалить выбранное"></form>';

                pagination($page);
            } else {
                if (empty($forum['closed'])) {
                    showError('Тем еще нет, будь первым!');
                }
            }

            if (!empty($forum['closed'])) {
                showError('В данном разделе запрещено создавать темы!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
        break;

        ############################################################################################
        ##                            Подготовка к редактированию темы                            ##
        ############################################################################################
        case 'edittopic':

            $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

            if (!empty($topics)) {

                echo '<b><big>Редактирование</big></b><br><br>';

                echo '<div class="form">';
                echo '<form action="/admin/forum?act=addedittopic&amp;fid='.$topics['forum_id'].'&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'" method="post">';
                echo 'Тема: <br>';
                echo '<input type="text" name="title" size="50" maxlength="50" value="'.$topics['title'].'"><br>';
                echo 'Кураторы темы: <br>';
                echo '<input type="text" name="moderators" size="50" maxlength="100" value="'.$topics['moderators'].'"><br>';

                echo 'Объявление:<br>';
                echo '<textarea id="markItUp" cols="25" rows="5" name="note">'.$topics['note'].'</textarea><br>';

                echo 'Закрепить тему: ';
                $checked = ($topics['locked'] == 1) ? ' checked="checked"' : '';
                echo '<input name="locked" type="checkbox" value="1"'.$checked.'><br>';

                echo 'Закрыть тему: ';
                $checked = ($topics['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                echo '<br><input type="submit" value="Изменить"></form></div><br>';
            } else {
                showError('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topics['forum_id'].'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Редактирование темы                                ##
        ############################################################################################
        case 'addedittopic':

            $token = check($_GET['token']);
            $title = check($_POST['title']);
            $moderators = check($_POST['moderators']);
            $note = check($_POST['note']);
            $locked = (empty($_POST['locked'])) ? 0 : 1;
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if ($token == $_SESSION['token']) {
                if (utfStrlen($title) >= 5 && utfStrlen($title) <= 50) {
                    if (utfStrlen($note) <= 250) {

                        $moderators = implode(',', preg_split('/[\s]*[,][\s]*/', $moderators));

                        DB::update("UPDATE `topics` SET `title`=?, `closed`=?, `locked`=?, `moderators`=?, `note`=? WHERE `id`=?;", [$title, $closed, $locked, $moderators, $note, $tid]);

                        if ($locked == 1) {
                            $page = 1;
                        }
                        setFlash('success', 'Тема успешно отредактирована!');
                        redirect("/admin/forum?act=forum&fid=$fid&page=$page");

                    } else {
                        showError('Ошибка! Слишком длинное объявление (Не более 250 символов)!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое название темы!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;page='.$page.'">К темам</a><br>';
        break;

        ############################################################################################
        ##                               Подготовка к перемещению темы                            ##
        ############################################################################################
        case 'movetopic':

            $topic = Topic::find($tid);

            if (!empty($topic)) {
                echo '<i class="fa fa-folder-open"></i> <b>'.$topic['title'].'</b> (Автор темы: '.$topic->user->login.')<br><br>';

                $forums = Forum::where('parent_id', 0)
                    ->with('children')
                    ->orderBy('sort')
                    ->get();

                if (count($forums) > 1) {

                    echo '<div class="form">';
                    echo '<form action="/admin/forum?act=addmovetopic&amp;fid='.$topic['forum_id'].'&amp;tid='.$tid.'&amp;token='.$_SESSION['token'].'" method="post">';


                    echo '<label for="inputSection">Раздел</label>';
                    echo '<select class="form-control" id="inputSection" name="section">';

                    foreach ($forums as $forum) {
                        if ($topic['forum_id'] != $forum['id']) {
                            $disabled = ! empty($forum['closed']) ? ' disabled="disabled"' : '';
                            echo '<option value="'.$forum['id'].'"'.$disabled.'>'.$forum['title'].'</option>';
                        }

                        if ($forum->children->isNotEmpty()) {
                            foreach($forum->children as $datasub) {
                                if ($topic['forum_id'] != $datasub['id']) {
                                    $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                                    echo '<option value="'.$datasub['id'].'"'.$disabled.'>– '.$datasub['title'].'</option>';
                                }
                            }
                        }
                    }

                    echo '</select>';

                    echo '<button class="btn btn-primary">Переместить</button></form></div><br>';
                } elseif(count($forums) == 1) {
                    showError('Нет разделов для перемещения!');
                }else {
                    showError('Разделы форума еще не созданы!');
                }
            } else {
                showError('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topic['forum_id'].'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Перемещение темы                                    ##
        ############################################################################################
        case 'addmovetopic':

            $token = check($_GET['token']);
            $section = abs(intval($_POST['section']));

            if ($token == $_SESSION['token']) {
                $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", [$section]);
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

                if (!empty($forums)) {
                    if (empty($forums['closed'])) {
                        // Обновление номера раздела
                        DB::update("UPDATE `topics` SET `forum_id`=? WHERE `id`=?;", [$section, $tid]);

                        // Ищем последние темы в форумах для обновления списка последних тем
                        $oldlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forum_id`=? ORDER BY `updated_at` DESC LIMIT 1;", [$topics['forum_id']]);
                        $newlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forum_id`=? ORDER BY `updated_at` DESC LIMIT 1;", [$section]);

                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $oldlast['forum_id']]);

                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$newlast['id'], $newlast['forum_id']]);

                        setFlash('success', 'Тема успешно перемещена!');
                        redirect("/admin/forum?act=forum&fid=$section");

                    } else {
                        showError('Ошибка! В закрытый раздел запрещено перемещать темы!');
                    }
                } else {
                    showError('Ошибка! Выбранного раздела не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'">К темам</a><br>';
        break;

        ############################################################################################
        ##                                     Удаление тем                                       ##
        ############################################################################################
        case 'deltopics':

            $token = isset($_GET['token']) ? check($_GET['token']) : '';
            $del = intar(Request::input('del'));
            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $delId = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    foreach($del as $topicId){
                        removeDir(HOME.'/uploads/forum/'.$topicId);
                        array_map('unlink', glob(HOME.'/uploads/thumbnail/uploads_forum_'.$topicId.'_*.{jpg,jpeg,png,gif}', GLOB_BRACE));

                        // Выбирает files.id только если они есть в posts
                        $delPosts = Post::where('topic_id', $topicId)
                            ->join('files', function($join){
                                $join->on('posts.id', '=', 'files.relate_id')
                                    ->where('files.relate_type', '=', Post::class);
                            })
                            ->pluck('files.id')
                            ->all();

                        if ($delPosts) {
                            $delFilesIds = implode(',', $delPosts);
                            DB::delete("DELETE FROM `files` WHERE `id` IN (" . $delFilesIds . ");");
                        }
                    }
                    // ------ Удаление загруженных файлов -------//

                    $votesIds = Vote::whereIn('topic_id', $del)->pluck('id')->all();

                    if ($votesIds) {
                        Vote::whereIn('id', $votesIds)->delete();
                        VoteAnswer::whereIn('vote_id', $votesIds)->delete();
                        VotePoll::whereIn('vote_id', $votesIds)->delete();
                    }

                    $deltopics = DB::run() -> exec("DELETE FROM `topics` WHERE `id` IN (".$delId.");");
                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `topic_id` IN (".$delId.");");

                    // Удаление закладок
                    DB::delete("DELETE FROM `bookmarks` WHERE `topic_id` IN (".$delId.");");

                    // Обновление счетчиков
                    DB::update("UPDATE `forums` SET `topics`=`topics`-?, `posts`=`posts`-? WHERE `id`=?;", [$deltopics, $delposts, $fid]);

                    // ------------------------------------------------------------//
                    $oldlast = DB::run() -> queryFetch("SELECT `t`.id, `f`.parent_id FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` WHERE `t`.`forum_id`=? ORDER BY `t`.`updated_at` DESC LIMIT 1;", [$fid]);

                    if (empty($oldlast['id'])) {
                        $oldlast['id'] = 0;
                    }

                    DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $fid]);

                    // Обновление родительского форума
                    if (! empty($oldlast['parent_id'])) {
                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $oldlast['parent_id']]);
                    }

                    setFlash('success', 'Выбранные темы успешно удалены!');
                    redirect("/admin/forum?act=forum&fid=$fid&page=$page");

                } else {
                    showError('Ошибка! Отсутствуют выбранные темы форума!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Закрытие - Закрепление темы                           ##
        ############################################################################################
        case 'acttopic':

            $token = check($_GET['token']);
            $do = check($_GET['do']);

            if ($token == $_SESSION['token']) {
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

                if (!empty($topics)) {
                    switch ($do):
                        case 'closed':
                            DB::update("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [1, $tid]);

                            $vote = Vote::where('topic_id', $tid)->first();
                            if ($vote) {
                                $vote->closed = 1;
                                $vote->save();

                                VotePoll::where('vote_id', $vote['id'])->delete();
                            }

                            setFlash('success', 'Тема успешно закрыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'open':
                            DB::update("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [0, $tid]);

                            $vote = Vote::where('topic_id', $tid)->first();
                            if ($vote) {
                                $vote->closed = 0;
                                $vote->save();
                            }

                            setFlash('success', 'Тема успешно открыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'locked':
                            DB::update("UPDATE `topics` SET `locked`=? WHERE `id`=?;", [1, $tid]);
                            setFlash('success', 'Тема успешно закреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'unlocked':
                            DB::update("UPDATE `topics` SET `locked`=? WHERE `id`=?;", [0, $tid]);
                            setFlash('success', 'Тема успешно откреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        default:
                            showError('Ошибка! Не выбрано действие для темы!');
                            endswitch;
                    } else {
                        showError('Ошибка! Данной темы не существует!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
            break;

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

                    echo '<i class="fa fa-forumbee fa-lg text-muted"></i> <b>'.$topic['title'].'</b>';

                    if (!empty($topic['moderators'])) {
                        $moderators = User::whereIn('id', explode(',', $topic['moderators']))->get();

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
                            ->limit(setting('forumpost'))
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
                        echo '<form action="/topic/'.$tid.'/create" method="post" enctype="multipart/form-data">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo 'Сообщение:<br>';
                        echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>';

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
                    DB::update("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $tid]);
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

            $post = Post::where('id', $pid)->with('files')->first();

            if (!empty($post)) {

                echo '<i class="fa fa-pencil"></i> <b>'.profile($post->user).'</b> <small>('.dateFixed($post['created_at']).')</small><br><br>';

                echo '<div class="form" id="form">';
                echo '<form action="/admin/forum?act=addeditpost&amp;tid='.$post['topic_id'].'&amp;pid='.$pid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br>';
                echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$post['text'].'</textarea><br>';

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

            $pid     = abs(intval(Request::input('pid')));
            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));

            if ($token == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) <= setting('forumtextlength')) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `id`=? LIMIT 1;", [$pid]);
                    if (!empty($post)) {

                        DB::update("UPDATE `posts` SET `text`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [$msg, user('id'), SITETIME, $pid]);

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

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
