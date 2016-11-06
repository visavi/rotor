<?php
App::view($config['themes'].'/index');

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
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}

if (is_admin()) {
    show_title('Управление форумом');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $queryforum = DB::run() -> query("SELECT * FROM `forums` ORDER BY `order` ASC;");
            $forums = $queryforum -> fetchAll();

            if (count($forums) > 0) {
                $output = array();

                foreach ($forums as $row) {
                    $id = $row['id'];
                    $fp = $row['parent'];
                    $output[$fp][$id] = $row;
                }

                echo '<a href="/forum">Обзор форума</a><hr />';

                foreach($output[0] as $key => $data) {
                    echo '<div class="b"><i class="fa fa-folder-open"></i> ';
                    echo '<b>'.$data['order'].'. <a href="/admin/forum?act=forum&amp;fid='.$data['id'].'">'.$data['title'].'</a></b> ('.$data['topics'].'/'.$data['posts'].')';

                    if (!empty($data['desc'])) {
                        echo '<br /><small>'.$data['desc'].'</small>';
                    }

                    if (is_admin(array(101))) {
                        echo '<br /><a href="/admin/forum?act=editforum&amp;fid='.$data['id'].'">Редактировать</a> / ';
                        echo '<a href="/admin/forum?act=prodelforum&amp;fid='.$data['id'].'">Удалить</a>';
                    }

                    echo '</div><div>';
                    // ----------------------------------------------------//
                    if (isset($output[$key])) {
                        foreach($output[$key] as $datasub) {
                            echo '<i class="fa fa-angle-right"></i> ';
                            echo '<b>'.$datasub['order'].'. <a href="/admin/forum?act=forum&amp;fid='.$datasub['id'].'">'.$datasub['title'].'</a></b>  ('.$datasub['topics'].'/'.$datasub['posts'].') ';
                            if (is_admin(array(101))) {
                                echo '(<a href="/admin/forum?act=editforum&amp;fid='.$datasub['id'].'">Редактировать</a> / ';
                                echo '<a href="/admin/forum?act=prodelforum&amp;fid='.$datasub['id'].'">Удалить</a>)';
                            }
                            echo '<br />';
                        }
                    }
                    // ----------------------------------------------------//
                    if ($data['last_id'] > 0) {
                        echo 'Тема: <a href="/admin/forum?act=topic&amp;tid='.$data['last_id'].'">'.$data['last_themes'].'</a><br />';
                        echo 'Сообщение: '.nickname($data['last_user']).' ('.date_fixed($data['last_time']).')';
                    } else {
                        echo 'Темы еще не созданы!';
                    }
                    echo '</div>';
                }
            } else {
                show_error('Разделы форума еще не созданы!');
            }

            if (is_admin(array(101))) {
                echo '<hr /><form action="/admin/forum?act=addforum&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Заголовок:<br />';
                echo '<input type="text" name="title" maxlength="50" />';
                echo '<input type="submit" value="Создать раздел" /></form><hr />';

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
            }

        break;

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'restatement':

            $uid = check($_GET['uid']);

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    restatement('forum');

                    notice('Все данные успешно пересчитаны!');
                    redirect("/admin/forum");

                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Добавление разделов                                 ##
        ############################################################################################
        case 'addforum':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    if (utf_strlen($title) >= 3 && utf_strlen($title) <= 50) {
                        $maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(`order`),0)+1 FROM `forums`;");
                        DB::run() -> query("INSERT INTO `forums` (`order`, `title`) VALUES (?, ?);", array($maxorder, $title));

                        notice('Новый раздел успешно добавлен!');
                        redirect("/admin/forum");

                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Добавлять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                          Подготовка к редактированию разделов                          ##
        ############################################################################################
        case 'editforum':

            if (is_admin(array(101))) {
                $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", array($fid));
                if (!empty($forums)) {
                    echo '<b><big>Редактирование</big></b><br /><br />';

                    echo '<div class="form">';
                    echo '<form action="/admin/forum?act=addeditforum&amp;fid='.$fid.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Раздел: <br />';
                    echo '<input type="text" name="title" maxlength="50" value="'.$forums['title'].'" /><br />';

                    $query = DB::run() -> query("SELECT `id`, `title`, `parent` FROM `forums` WHERE `parent`=? ORDER BY `order` ASC;", array(0));
                    $section = $query -> fetchAll();

                    echo 'Родительский форум:<br />';
                    echo '<select name="parent">';
                    echo '<option value="0">Основной форум</option>';

                    foreach ($section as $data) {
                        if ($fid != $data['id']) {
                            $selected = ($forums['parent'] == $data['id']) ? ' selected="selected"' : '';
                            echo '<option value="'.$data['id'].'"'.$selected.'>'.$data['title'].'</option>';
                        }
                    }
                    echo '</select><br />';

                    echo 'Описание: <br />';
                    echo '<input type="text" name="desc" maxlength="100" value="'.$forums['desc'].'" /><br />';

                    echo 'Положение: <br />';
                    echo '<input type="text" name="order" maxlength="2" value="'.$forums['order'].'" /><br />';

                    echo 'Закрыть форум: ';
                    $checked = ($forums['closed'] == 1) ? ' checked="checked"' : '';
                    echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                    echo '<input type="submit" value="Изменить" /></form></div><br />';
                } else {
                    show_error('Ошибка! Данного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Редактирование разделов                                ##
        ############################################################################################
        case 'addeditforum':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $desc = check($_POST['desc']);
            $parent = abs(intval($_POST['parent']));
            $order = abs(intval($_POST['order']));
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    if (utf_strlen($title) >= 3 && utf_strlen($title) <= 50) {
                        if (utf_strlen($desc) <= 100) {
                            if ($fid != $parent) {
                                $forums = DB::run() -> queryFetch("SELECT `id` FROM `forums` WHERE `parent`=? LIMIT 1;", array($fid));

                                if (empty($forums) || empty($parent)) {
                                    DB::run() -> query("UPDATE `forums` SET `order`=?, `parent`=?, `title`=?, `desc`=?, `closed`=? WHERE `id`=?;", array($order, $parent, $title, $desc, $closed, $fid));

                                    notice('Раздел успешно отредактирован!');
                                    redirect("/admin/forum");

                                } else {
                                    show_error('Ошибка! Данный раздел имеет подфорумы!');
                                }
                            } else {
                                show_error('Ошибка! Недопустимый выбор родительского форума!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинный текст описания раздела!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=editforum&amp;fid='.$fid.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">В Форум</a><br />';
        break;

        ############################################################################################
        ##                                  Подтвержение удаления                                 ##
        ############################################################################################
        case 'prodelforum':

            if (is_admin(array(101))) {
                $forums = DB::run() -> queryFetch("SELECT `f1`.*, count(`f2`.`id`) AS `subcnt` FROM `forums` `f1` LEFT JOIN `forums` `f2` ON `f2`.`parent` = `f1`.`id` WHERE `f1`.`id`=? GROUP BY `id` LIMIT 1;", array($fid));

                if (!empty($forums['id'])) {
                    if (empty($forums['subcnt'])) {
                        echo 'Вы уверены что хотите удалить раздел <b>'.$forums['title'].'</b> в форуме?<br />';
                        echo '<i class="fa fa-times"></i> <b><a href="/admin/forum?act=delforum&amp;fid='.$fid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
                    } else {
                        show_error('Ошибка! Данный раздел имеет подфорумы!');
                    }
                } else {
                    show_error('Ошибка! Данного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Удаление раздела                                    ##
        ############################################################################################
        case 'delforum':

            $uid = check($_GET['uid']);

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    $forums = DB::run() -> queryFetch("SELECT `f1`.*, count(`f2`.`id`) AS `subcnt` FROM `forums` `f1` LEFT JOIN `forums` `f2` ON `f2`.`parent` = `f1`.`id` WHERE `f1`.`id`=? GROUP BY `id` LIMIT 1;", array($fid));

                    if (!empty($forums['id'])) {
                        if (empty($forums['subcnt'])) {

                            // ------ Удаление загруженных файлов -------//
                            $querytopics = DB::run() -> query("SELECT `id` FROM `topics` WHERE `forums_id`=?;", array($fid));
                            $topics = $querytopics->fetchAll(PDO::FETCH_COLUMN);

                            if (!empty($topics)){
                                $delId = implode(',', $topics);

                                foreach($topics as $delDir){
                                    removeDir(HOME.'/upload/forum/'.$delDir);
                                    array_map('unlink', glob(HOME.'/upload/thumbnail/upload_forum_'.$delDir.'_*.{jpg,jpeg,png,gif}', GLOB_BRACE));
                                }
                                DB::run() -> query("DELETE FROM `files_forum` WHERE `topics_id` IN (".$delId.");");
                            }
                            // ------ Удаление загруженных файлов -------//

                            DB::run() -> query("DELETE FROM `posts` WHERE `posts_forums_id`=?;", array($fid));
                            DB::run() -> query("DELETE FROM `topics` WHERE `forums_id`=?;", array($fid));
                            DB::run() -> query("DELETE FROM `forums` WHERE `id`=?;", array($fid));
                            DB::run() -> query("DELETE FROM `bookmarks` WHERE `forum`=?;", array($fid));

                            notice('Раздел успешно удален!');
                            redirect("/admin/forum");

                        } else {
                            show_error('Ошибка! Данный раздел имеет подфорумы!');
                        }
                    } else {
                        show_error('Ошибка! Данного раздела не существует!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Просмотр тем в разделе                              ##
        ############################################################################################
        case 'forum':

            $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", array($fid));

            if (!empty($forums)) {
                echo '<a href="/admin/forum">Форум</a> / ';
                echo '<a href="/forum/'.$fid.'?start='.$start.'">Обзор раздела</a><br /><br />';

                echo '<i class="fa fa-forumbee fa-lg text-muted"></i> <b>'.$forums['title'].'</b><hr />';

                $total = DB::run() -> querySingle("SELECT count(*) FROM `topics` WHERE `forums_id`=?;", array($fid));

                if ($total > 0) {
                    if ($start >= $total) {
                        $start = 0;
                    }

                    $querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `forums_id`=? ORDER BY `locked` DESC, `last_time` DESC LIMIT ".$start.", ".$config['forumtem'].";", array($fid));

                    echo '<form action="/admin/forum?act=deltopics&amp;fid='.$fid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo '<div class="form">';
                    echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b>';
                    echo '</div>';

                    while ($data = $querytopic -> fetch()) {
                        echo '<div class="b">';

                        if ($data['locked'] == 1) {
                            echo '<i class="fa fa-thumb-tack"></i> ';
                        } elseif ($data['closed'] == 1) {
                            echo '<i class="fa fa-lock"></i> ';
                        } else {
                            echo '<i class="fa folder-open"></i> ';
                        }

                        echo '<b><a href="/admin/forum?act=topic&amp;tid='.$data['id'].'">'.$data['title'].'</a></b> ('.$data['posts'].')<br />';

                        echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';

                        echo '<a href="/admin/forum?act=edittopic&amp;tid='.$data['id'].'&amp;start='.$start.'">Редактировать</a> / ';
                        echo '<a href="/admin/forum?act=movetopic&amp;tid='.$data['id'].'&amp;start='.$start.'">Переместить</a></div>';

                        echo '<div>Страницы: ';
                        forum_navigation('/admin/forum?act=topic&amp;tid='.$data['id'].'&amp;', $config['forumpost'], $data['posts']);
                        echo 'Сообщение: '.nickname($data['last_user']).' ('.date_fixed($data['last_time']).')</div>';
                    }

                    echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                    page_strnavigation('/admin/forum?act=forum&amp;fid='.$fid.'&amp;', $config['forumtem'], $start, $total);
                } else {
                    if (empty($forums['closed'])) {
                        show_error('Тем еще нет, будь первым!');
                    }
                }

                if (!empty($forums['closed'])) {
                    show_error('В данном разделе запрещено создавать темы!');
                }
            } else {
                show_error('Ошибка! Данного раздела не существует!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br />';
        break;

        ############################################################################################
        ##                            Подготовка к редактированию темы                            ##
        ############################################################################################
        case 'edittopic':

            $topics = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`id` FROM `topics` LEFT JOIN `forums` ON `topics`.`forums_id`=`forums`.`id` WHERE `id`=? LIMIT 1;", array($tid));

            if (!empty($topics)) {

                echo '<b><big>Редактирование</big></b><br /><br />';

                echo '<div class="form">';
                echo '<form action="/admin/forum?act=addedittopic&amp;fid='.$topics['id'].'&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Тема: <br />';
                echo '<input type="text" name="title" size="50" maxlength="50" value="'.$topics['title'].'" /><br />';
                echo 'Кураторы темы: <br />';
                echo '<input type="text" name="mod" size="50" maxlength="100" value="'.$topics['mod'].'" /><br />';

                echo 'Объявление:<br />';
                echo '<textarea id="markItUp" cols="25" rows="5" name="note">'.$topics['note'].'</textarea><br />';

                echo 'Закрепить тему: ';
                $checked = ($topics['locked'] == 1) ? ' checked="checked"' : '';
                echo '<input name="locked" type="checkbox" value="1"'.$checked.' /><br />';

                echo 'Закрыть тему: ';
                $checked = ($topics['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topics['id'].'&amp;start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                     Редактирование темы                                ##
        ############################################################################################
        case 'addedittopic':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $mod = check($_POST['mod']);
            $note = check($_POST['note']);
            $locked = (empty($_POST['locked'])) ? 0 : 1;
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($note) <= 250) {

                        $mod = implode(',', preg_split('/[\s]*[,][\s]*/', $mod));

                        DB::run() -> query("UPDATE `topics` SET `title`=?, `closed`=?, `locked`=?, `mod`=?, `note`=? WHERE `id`=?;", array($title, $closed, $locked, $mod, $note, $tid));

                        if ($locked == 1) {
                            $start = 0;
                        }
                        notice('Тема успешно отредактирована!');
                        redirect("/admin/forum?act=forum&fid=$fid&start=$start");

                    } else {
                        show_error('Ошибка! Слишком длинное объявление (Не более 250 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое название темы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;start='.$start.'">К темам</a><br />';
        break;

        ############################################################################################
        ##                               Подготовка к перемещению темы                            ##
        ############################################################################################
        case 'movetopic':

            $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", array($tid));
            if (!empty($topics)) {
                echo '<i class="fa fa-folder-open"></i> <b>'.$topics['title'].'</b> (Автор темы: '.nickname($topics['author']).')<br /><br />';

                $queryforum = DB::run() -> query("SELECT * FROM `forums` ORDER BY `order` ASC;");
                $forums = $queryforum -> fetchAll();

                if (count($forums) > 0) {
                    $output = array();
                    foreach ($forums as $row) {
                        $i = $row['id'];
                        $p = $row['parent'];
                        $output[$p][$i] = $row;
                    }

                    echo '<div class="form"><form action="/admin/forum?act=addmovetopic&amp;fid='.$topics['forums_id'].'&amp;tid='.$tid.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo 'Выберите раздел для перемещения:<br />';
                    echo '<select name="section">';

                    foreach ($output[0] as $key => $data) {
                        if ($topics['forums_id'] != $data['id']) {
                            $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
                            echo '<option value="'.$data['id'].'"'.$disabled.'>'.$data['title'].'</option>';
                        }

                        if (isset($output[$key])) {
                            foreach($output[$key] as $datasub) {
                                if ($topics['id'] != $datasub['id']) {
                                    $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                                    echo '<option value="'.$datasub['id'].'"'.$disabled.'>– '.$datasub['title'].'</option>';
                                }
                            }
                        }
                    }

                    echo '</select>';

                    echo '<input type="submit" value="Переместить" /></form></div><br />';
                } else {
                    show_error('Разделы форума еще не созданы!');
                }
            } else {
                show_error('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topics['forums_id'].'&amp;start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Перемещение темы                                    ##
        ############################################################################################
        case 'addmovetopic':

            $uid = check($_GET['uid']);
            $section = abs(intval($_POST['section']));

            if ($uid == $_SESSION['token']) {
                $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", array($section));
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", array($tid));

                if (!empty($forums)) {
                    if (empty($forums['closed'])) {
                        // Обновление номера раздела
                        DB::run() -> query("UPDATE `topics` SET `forums_id`=? WHERE `id`=?;", array($section, $tid));
                        DB::run() -> query("UPDATE `posts` SET `posts_forums_id`=? WHERE `posts_topics_id`=?;", array($section, $tid));

                        // Ищем последние темы в форумах для обновления списка последних тем
                        $oldlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forums_id`=? ORDER BY `last_time` DESC LIMIT 1;", array($topics['forums_id']));
                        $newlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forums_id`=? ORDER BY `last_time` DESC LIMIT 1;", array($section));

                        DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", array($oldlast['id'], $oldlast['title'], $oldlast['last_user'], $oldlast['last_time'], $oldlast['forums_id']));

                        DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", array($newlast['id'], $newlast['title'], $newlast['last_user'], $newlast['last_time'], $newlast['forums_id']));
                        // Обновление закладок
                        DB::run() -> query("UPDATE `bookmarks` SET `forum`=? WHERE `topic`=?;", array($section, $tid));

                        notice('Тема успешно перемещена!');
                        redirect("/admin/forum?act=forum&fid=$section");

                    } else {
                        show_error('Ошибка! В закрытый раздел запрещено перемещать темы!');
                    }
                } else {
                    show_error('Ошибка! Выбранного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'">К темам</a><br />';
        break;

        ############################################################################################
        ##                                     Удаление тем                                       ##
        ############################################################################################
        case 'deltopics':

            $uid = isset($_GET['uid']) ? check($_GET['uid']) : '';
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } elseif (isset($_GET['del'])) {
                $del = array(abs(intval($_GET['del'])));
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $delId = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    foreach($del as $delDir){
                        removeDir(HOME.'/upload/forum/'.$delDir);
                        array_map('unlink', glob(HOME.'/upload/thumbnail/upload_forum_'.$delDir.'_*.{jpg,jpeg,png,gif}', GLOB_BRACE));
                    }
                    DB::run() -> query("DELETE FROM `files_forum` WHERE `topics_id` IN (".$delId.");");
                    // ------ Удаление загруженных файлов -------//

                    $deltopics = DB::run() -> exec("DELETE FROM `topics` WHERE `id` IN (".$delId.");");
                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `posts_topics_id` IN (".$delId.");");

                    // Удаление закладок
                    DB::run() -> query("DELETE FROM `bookmarks` WHERE `topic` IN (".$delId.");");

                    // Обновление счетчиков
                    DB::run() -> query("UPDATE `forums` SET `topics`=`topics`-?, `posts`=`posts`-? WHERE `id`=?;", array($deltopics, $delposts, $fid));

                    // ------------------------------------------------------------//
                    $oldlast = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`forums_id`=`forums`.`id` WHERE `topics`.`forums_id`=? ORDER BY `topics`.`last_time` DESC LIMIT 1;", array($fid));

                    if (empty($oldlast['id'])) {
                        $oldlast['id'] = 0;
                        $oldlast['title'] = '';
                        $oldlast['last_user'] = '';
                        $oldlast['last_time'] = 0;
                    }

                    DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", array($oldlast['id'], $oldlast['title'], $oldlast['last_user'], $oldlast['last_time'], $fid));

                    // Обновление родительского форума
                    if ($oldlast['parent'] > 0) {
                        DB::run() -> query("UPDATE `forums` SET `last_id`=?, `last_themes`=?, `last_user`=?, `last_time`=? WHERE `id`=?;", array($oldlast['id'], $oldlast['title'], $oldlast['last_user'], $oldlast['last_time'], $oldlast['parent']));
                    }

                    notice('Выбранные темы успешно удалены!');
                    redirect("/admin/forum?act=forum&fid=$fid&start=$start");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные темы форума!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Закрытие - Закрепление темы                           ##
        ############################################################################################
        case 'acttopic':

            $uid = check($_GET['uid']);
            $do = check($_GET['do']);

            if ($uid == $_SESSION['token']) {
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", array($tid));

                if (!empty($topics)) {
                    switch ($do):
                        case 'closed':
                            DB::run() -> query("UPDATE `topics` SET `closed`=? WHERE `id`=?;", array(1, $tid));
                            notice('Тема успешно закрыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&start=$start");
                            break;

                        case 'open':
                            DB::run() -> query("UPDATE `topics` SET `closed`=? WHERE `id`=?;", array(0, $tid));
                            notice('Тема успешно открыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&start=$start");
                            break;

                        case 'locked':
                            DB::run() -> query("UPDATE `topics` SET `locked`=? WHERE `id`=?;", array(1, $tid));
                            notice('Тема успешно закреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&start=$start");
                            break;

                        case 'unlocked':
                            DB::run() -> query("UPDATE `topics` SET `locked`=? WHERE `id`=?;", array(0, $tid));
                            notice('Тема успешно откреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&start=$start");
                            break;

                        default:
                            show_error('Ошибка! Не выбрано действие для темы!');
                            endswitch;
                    } else {
                        show_error('Ошибка! Данной темы не существует!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br />';
            break;

        ############################################################################################
        ##                                     Просмотр сообщений                                 ##
        ############################################################################################
        case 'topic':
            if (!empty($tid)) {
                $topic = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`id`, `forums`.`title`, `forums`.`parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`forums_id`=`forums`.`id` WHERE `id`=? LIMIT 1;", array($tid));

                if (!empty($topic)) {
                    echo '<a href="/admin/forum">Форум</a> / ';

                    if (!empty($topic['parent'])) {
                        $forums = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", array($topic['parent']));
                        echo '<a href="/admin/forum?fid='.$forums['id'].'">'.$forums['title'].'</a> / ';
                    }

                    echo '<a href="/admin/forum?act=forum&amp;fid='.$topic['id'].'">'.$topic['title'].'</a> / ';
                    echo '<a href="/topic/'.$tid.'?start='.$start.'">Обзор темы</a><br /><br />';

                    echo '<i class="fa fa-forumbee fa-lg text-muted"></i> <b>'.$topic['title'].'</b>';

                    if (!empty($topic['mod'])) {
                        $mod = explode(',', $topic['mod']);

                        echo '<br />Кураторы темы: ';
                        foreach ($mod as $mkey => $mval) {
                            $comma = (empty($mkey)) ? '' : ', ';
                            echo $comma . profile($mval);
                        }
                    }

                    if (!empty($topic['note'])){
                        echo '<div class="info">'.bb_code($topic['note']).'</div>';
                    }

                    echo '<hr />';

                    if (empty($topic['closed'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Закрыть</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=open&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Открыть</a> / ';
                    }

                    if (empty($topic['locked'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Закрепить</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Открепить</a> / ';
                    }

                    echo '<a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;start='.$start.'">Изменить</a> / ';
                    echo '<a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Переместить</a> / ';
                    echo '<a href="/admin/forum?act=deltopics&amp;fid='.$topic['id'].'&amp;del='.$tid.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную тему?\')">Удалить</a><br />';

                    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `posts_topics_id`=?;", array($tid));

                    if ($total > 0) {
                        if ($start >= $total) {
                            $start = last_page($total, $config['forumpost']);
                        }

                        $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC LIMIT ".$start.", ".$config['forumpost'].";", array($tid));
                        $posts = $querypost->fetchAll();

                        // ----- Получение массива файлов ----- //
                        $ipdpost = array();
                        foreach ($posts as $val) {
                            $ipdpost[] = $val['posts_id'];
                        }

                        $ipdpost = implode(',', $ipdpost);

                        $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `posts_id` IN (".$ipdpost.");");
                        $files = $queryfiles->fetchAll();

                        if (!empty($files)){
                            $forumfiles = array();
                            foreach ($files as $file){
                                $forumfiles[$file['posts_id']][] = $file;
                            }
                        }
                        // ------------------------------------- //

                        echo '<form action="/admin/forum?act=delposts&amp;tid='.$tid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                        echo '<div align="right" class="form">';
                        echo '<b><label for="all">Отметить все</label></b> <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" />&nbsp;';
                        echo '</div>';

                        foreach ($posts as $key=>$data){
                            $num = ($start + $key + 1);

                            echo '<div class="b">';

                            echo '<div class="img">'.user_avatars($data['posts_user']).'</div>';
                            echo '<span class="imgright"><a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$data['posts_id'].'&amp;start='.$start.'">Ред.</a> <input type="checkbox" name="del[]" value="'.$data['posts_id'].'" /></span>';


                            echo $num.'. <b>'.profile($data['posts_user']).'</b>  <small>('.date_fixed($data['posts_time']).')</small><br />';
                            echo user_title($data['posts_user']).' '.user_online($data['posts_user']).'</div>';

                            echo '<div>'.bb_code($data['posts_text']).'<br />';

                            // -- Прикрепленные файлы -- //
                            if (!empty($forumfiles)) {
                                if (isset($forumfiles[$data['posts_id']])){
                                    echo '<div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br />';
                                    foreach ($forumfiles[$data['posts_id']] as $file){
                                        $ext = getExtension($file['hash']);
                                        echo icons($ext).' ';

                                        echo '<a href="/upload/forum/'.$file['topics_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatsize($file['size']).')<br />';
                                    }
                                    echo '</div>';
                                }
                            }
                            // --------------------------//

                            if (!empty($data['posts_edit'])) {
                                echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.nickname($data['posts_edit']).' ('.date_fixed($data['posts_edit_time']).')</small><br />';
                            }

                            echo '<span class="data">('.$data['posts_brow'].', '.$data['posts_ip'].')</span></div>';
                        }

                        echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';

                        page_strnavigation('/admin/forum?act=topic&amp;tid='.$tid.'&amp;', $config['forumpost'], $start, $total);
                    } else {
                        show_error('Сообщений еще нет, будь первым!');
                    }

                    if (empty($topic['closed'])) {
                        echo '<div class="form" id="form">';
                        echo '<form action="/topic/'.$tid.'/create" method="post">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo 'Сообщение:<br />';
                        echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';

                        echo '<div class="js-attach-form" style="display: none;">
                            Прикрепить файл:<br /><input type="file" name="file" /><br />
                            <div class="info">
                                Максимальный вес файла: <b>'.round($config['forumloadsize']/1024).'</b> Kb<br />
                                Допустимые расширения: '.str_replace(',', ', ', $config['forumextload']).'
                            </div><br />
                        </div>';

                        echo '<span class="imgright js-attach-button"><a href="#" onclick="return showAttachForm();">Загрузить файл</a></span>';

                        echo '<input type="submit" value="Написать" />';
                        echo '</form></div><br />';

                    } else {
                        show_error('Данная тема закрыта для обсуждения!');
                    }
                } else {
                    show_error('Ошибка! Данной темы не существует!');
                }
            } else {
                show_error('Ошибка! Не выбрана тема!');
            }
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br />';
        break;

        ############################################################################################
        ##                                    Удаление сообщений                                  ##
        ############################################################################################
        case 'delposts':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", array($tid));
                    $del = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    $queryfiles = DB::run() -> query("SELECT `hash` FROM `files_forum` WHERE `posts_id` IN (".$del.");");
                    $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($files)){
                        foreach ($files as $file){
                            if (file_exists(HOME.'/upload/forum/'.$topics['id'].'/'.$file)){
                                unlink_image('upload/forum/', $topics['id'].'/'.$file);
                            }
                        }
                        DB::run() -> query("DELETE FROM `files_forum` WHERE `posts_id` IN (".$del.");");
                    }
                    // ------ Удаление загруженных файлов -------//

                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `posts_id` IN (".$del.") AND `posts_id`=".$tid.";");
                    DB::run() -> query("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", array($delposts, $tid));
                    DB::run() -> query("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", array($delposts, $topics['forums_id']));

                    notice('Выбранные сообщения успешно удалены!');
                    redirect("/admin/forum?act=topic&tid=$tid&start=$start");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Подготовка к редактированию                          ##
        ############################################################################################
        case 'editpost':

            $pid = abs(intval($_GET['pid']));

            $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($pid));
            if (!empty($post)) {

                echo '<i class="fa fa-pencil"></i> <b>'.nickname($post['posts_user']).'</b> <small>('.date_fixed($post['posts_time']).')</small><br /><br />';

                echo '<div class="form" id="form">';
                echo '<form action="/admin/forum?act=addeditpost&amp;tid='.$post['posts_topics_id'].'&amp;pid='.$pid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br />';
                echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$post['posts_text'].'</textarea><br />';

                $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `posts_id`=?;", array($pid));
                $files = $queryfiles->fetchAll();

                if (!empty($files)){
                    echo '<i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br />';
                    foreach ($files as $file){
                        echo '<input type="checkbox" name="delfile[]" value="'.$file['id'].'" /> ';
                        echo '<a href="/upload/forum/'.$file['topics_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatsize($file['size']).')<br />';
                    }
                    echo '<br />';
                }

                echo '<input value="Редактировать" name="do" type="submit" /></form></div><br />';
            } else {
                show_error('Ошибка! Данного сообщения не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Редактирование сообщения                            ##
        ############################################################################################
        case 'addeditpost':

            $uid = check($_GET['uid']);
            $pid = abs(intval($_GET['pid']));
            $msg = check($_POST['msg']);

            if (isset($_POST['delfile'])) {
                $del = intar($_POST['delfile']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= $config['forumtextlength']) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `posts_id`=? LIMIT 1;", array($pid));
                    if (!empty($post)) {

                        DB::run() -> query("UPDATE `posts` SET `posts_text`=?, `posts_edit`=?, `posts_edit_time`=? WHERE `posts_id`=?;", array($msg, $log, SITETIME, $pid));

                        // ------ Удаление загруженных файлов -------//
                        if (!empty($del)) {
                            $del = implode(',', $del);

                            $queryfiles = DB::run() -> query("SELECT * FROM `files_forum` WHERE `posts_id`=? AND `id` IN (".$del.");", array($pid));
                            $files = $queryfiles->fetchAll();

                            if (!empty($files)){
                                foreach ($files as $file){
                                    if (file_exists(HOME.'/upload/forum/'.$file['topics_id'].'/'.$file['hash'])){
                                        unlink_image('upload/forum/', $file['topics_id'].'/'.$file['hash']);
                                    }
                                }
                                DB::run() -> query("DELETE FROM `files_forum` WHERE `posts_id`=? AND `id` IN (".$del.");", array($pid));
                            }
                        }
                        // ------ Удаление загруженных файлов -------//


                        notice('Сообщение успешно отредактировано!');
                        redirect("/admin/forum?act=topic&tid=$tid&start=$start");

                    } else {
                        show_error('Ошибка! Данного сообщения не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;start='.$start.'">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
