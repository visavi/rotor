<?php


############################################################################################
##                                    Добавление файла                                    ##
############################################################################################
case 'newfile':
    //setting('newtitle') = 'Публикация нового файла';

    $querydown = DB::select("SELECT * FROM `cats` ORDER BY sort ASC;");
    $downs = $querydown -> fetchAll();

    if (count($downs) > 0) {
        echo '<div class="form">';
        echo '<form action="/admin/load?act=addfile&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Категория*:<br>';

        $output = [];

        foreach ($downs as $row) {
            $i = $row['id'];
            $p = $row['parent'];
            $output[$p][$i] = $row;
        }

        echo '<select name="cid">';
        echo '<option value="0">Выберите категорию</option>';

        foreach ($output[0] as $key => $data) {
            $selected = $cid == $data['id'] ? ' selected' : '';
            $disabled = ! empty($data['closed']) ? ' disabled' : '';
            echo '<option value="'.$data['id'].'"'.$selected.$disabled.'>'.$data['name'].'</option>';

            if (isset($output[$key])) {
                foreach($output[$key] as $datasub) {
                    $selected = $cid == $datasub['id'] ? ' selected' : '';
                    $disabled = ! empty($datasub['closed']) ? ' disabled' : '';
                    echo '<option value="'.$datasub['id'].'"'.$selected.$disabled.'>– '.$datasub['name'].'</option>';
                }
            }
        }

        echo '</select><br>';

        echo 'Название*:<br>';
        echo '<input type="text" name="title" size="50" maxlength="50"><br>';
        echo 'Описание*:<br>';
        echo '<textarea cols="25" rows="10" name="text"></textarea><br>';
        echo 'Автор файла:<br>';
        echo '<input type="text" name="author" maxlength="50"><br>';
        echo 'Сайт автора:<br>';
        echo '<input type="text" name="site" maxlength="50" value="http://"><br>';

        echo '<input value="Продолжить" type="submit"></form></div><br>';

        echo 'Все поля отмеченные знаком *, обязательны для заполнения<br>';
        echo 'Файл и скриншот вы сможете загрузить после добавления описания<br>';
        echo 'Если вы ошиблись в названии или описании файла, вы всегда можете его отредактировать<br><br>';
    } else {
        showError('Категории файлов еще не созданы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load">Вернуться</a><br>';
break;

############################################################################################
##                                  Публикация файла                                      ##
############################################################################################
case 'addfile':

    //setting('newtitle') = 'Публикация нового файла';

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
    $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

    if ($uid == $_SESSION['token']) {
        if (!empty($cid)) {
            if (utfStrlen($title) >= 5 && utfStrlen($title) < 50) {
                if (utfStrlen($text) >= 10 && utfStrlen($text) < 5000) {
                    if (utfStrlen($author) <= 50) {
                        if (utfStrlen($site) <= 50) {
                            if (empty($site) || preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                                $downs = Load::query()->find($cid);
                                if (!empty($downs)) {
                                    if (empty($downs['closed'])) {
                                        $downtitle = DB::run() -> querySingle("SELECT `title` FROM `downs` WHERE `title`=? LIMIT 1;", [$title]);
                                        if (empty($downtitle)) {

                                            DB::update("UPDATE `cats` SET `count`=`count`+1 WHERE `id`=?", [$cid]);
                                            DB::insert("INSERT INTO `downs` (`category_id`, `title`, `text`, `link`, `user`, `author`, `site`, `screen`, `time`, `active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", [$cid, $title, $text, '', getUser('login'), $author, $site, '', SITETIME, 1]);

                                            $lastid = DB::run() -> lastInsertId();

                                            setFlash('success', 'Данные успешно добавлены!');
                                            redirect("/admin/load?act=editdown&id=$lastid");
                                        } else {
                                            showError('Ошибка! Название '.$title.' уже имеется в файлах!');
                                        }
                                    } else {
                                        showError('Ошибка! В данный раздел запрещена загрузка файлов!');
                                    }
                                } else {
                                    showError('Ошибка! Выбранный вами раздел не существует!');
                                }
                            } else {
                                showError('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                            }
                        } else {
                            showError('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                        }
                    } else {
                        showError('Ошибка! Слишком длинный ник (логин) автора (не более 50 символов)!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий текст описания (от 10 до 5000 символов)!');
                }
            } else {
                showError('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
            }
        } else {
            showError('Ошибка! Вы не выбрали категорию для добавления файла!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=newfile&amp;cid='.$cid.'">Вернуться</a><br>';
break;



############################################################################################
##                                   Загрузка файла                                       ##
############################################################################################
case 'loadfile':
    //show_title('Загрузка файла');

    $down = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($down)) {
        if (empty($down['link'])) {

            $folder = $down['folder'] ? $down['folder'].'/' : '';

            if (is_writable(UPLOADS.'/files/'.$folder)) {
            if (is_uploaded_file($_FILES['loadfile']['tmp_name'])) {
                $filename = check(strtolower($_FILES['loadfile']['name']));


                if (strlen($filename) <= 50) {
                    if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {

                        $ext = getExtension($filename);

                        if (in_array($ext, explode(',', setting('allowextload')), true)) {
                            if ($_FILES['loadfile']['size'] > 0 && $_FILES['loadfile']['size'] <= setting('fileupload')) {
                                $downlink = DB::run() -> querySingle("SELECT `link` FROM `downs` WHERE `link`=? LIMIT 1;", [$filename]);
                                if (empty($downlink)) {

                                    move_uploaded_file($_FILES['loadfile']['tmp_name'], UPLOADS.'/files/'.$folder.$filename);
                                    @chmod(UPLOADS.'/files/'.$folder.$filename, 0666);

                                    DB::update("UPDATE `downs` SET `link`=? WHERE `id`=?;", [$filename, $id]);

                                    setFlash('success', 'Файл успешно загружен!');
                                    redirect("/admin/load?act=editdown&id=$id");
                                } else {
                                    showError('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
                                }
                            } else {
                                showError('Ошибка! Максимальный размер загружаемого файла '.formatSize(setting('fileupload')).'!');
                            }
                        } else {
                            showError('Ошибка! Недопустимое расширение файла!');
                        }
                    } else {
                        showError('Ошибка! В названии файла присутствуют недопустимые символы!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
                }
            } else {
                showError('Ошибка! Не удалось загрузить файл!');
            }
            } else {
                showError('Директория uploads/files/'.$folder.' недоступна для записи!');
            }
        } else {
            showError('Ошибка! Файл уже загружен!');
        }
    } else {
        showError('Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                   Загрузка скриншота                                   ##
############################################################################################
case 'loadscreen':
    //show_title('Загрузка скриншота');

    $down = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($down)) {
        if (empty($down['screen'])) {
            if (is_uploaded_file($_FILES['screen']['tmp_name'])) {

                // ------------------------------------------------------//
                $handle = uploadImage($_FILES['screen'], setting('screenupload'), setting('screenupsize'), $down['link']);
                if ($handle) {
                    $folder = $down['folder'] ? $down['folder'].'/' : '';

                    $handle -> process(UPLOADS.'/screen/'.$folder);
                    if ($handle -> processed) {

                        DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", [$handle -> file_dst_name, $id]);

                        $handle -> clean();

                        setFlash('success', 'Скриншот успешно загружен!');
                        redirect("/admin/load?act=editdown&id=$id");
                    } else {
                        showError($handle -> error);
                    }
                } else {
                    showError('Ошибка! Не удалось загрузить скриншот!');
                }
            } else {
                showError('Ошибка! Вы не загрузили скриншот!');
            }
        } else {
            showError('Ошибка! Скриншот уже загружен!');
        }
    } else {
        showError('Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                   Удаление файла                                       ##
############################################################################################
case 'delfile':

    $link = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);
    if (!empty($link)) {
        $folder = $link['folder'] ? $link['folder'].'/' : '';

        if (!empty($link['link']) && file_exists(UPLOADS.'/files/'.$folder.$link['link'])) {
            unlink(UPLOADS.'/files/'.$folder.$link['link']);
        }

        deleteImage('uploads/screen/'.$folder, $link['screen']);

        DB::update("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", ['', '', $id]);

        setFlash('success', 'Файл успешно удален!');
        redirect("/admin/load?act=editdown&id=$id");
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                                    Удаление скриншота                                  ##
############################################################################################
case 'delscreen':

    $screen = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);
    if (!empty($screen)) {
        $folder = $screen['folder'] ? $screen['folder'].'/' : '';

        deleteImage('uploads/screen/'.$folder, $screen['screen']);

        DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", ['', $id]);

        setFlash('success', 'Скриншот успешно удален!');
        redirect("/admin/load?act=editdown&id=$id");
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br>';
break;

############################################################################################
##                               Подготовка к перемещению файла                           ##
############################################################################################
case 'movedown':

    $downs = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($downs)) {
        $folder = $downs['folder'] ? $downs['folder'].'/' : '';

        echo '<i class="fa fa-download"></i> <b>'.$downs['title'].'</b> ('.formatFileSize(UPLOADS.'/files/'.$folder.$downs['link']).')<br><br>';

        $querycats = DB::select("SELECT * FROM `cats` ORDER BY sort ASC;");
        $cats = $querycats -> fetchAll();

        if (count($cats) > 1) {
            $output = [];
            foreach ($cats as $row) {
                $i = $row['id'];
                $p = $row['parent'];
                $output[$p][$i] = $row;
            }

            echo '<div class="form"><form action="/admin/load?act=addmovedown&amp;cid='.$downs['category_id'].'&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Выберите раздел для перемещения:<br>';
            echo '<select name="section">';
            echo '<option value="0">Список разделов</option>';

            foreach ($output[0] as $key => $data) {
                if ($downs['category_id'] != $data['id']) {
                    $disabled = ! empty($data['closed']) ? ' disabled' : '';
                    echo '<option value="'.$data['id'].'"'.$disabled.'>'.$data['name'].'</option>';
                }

                if (isset($output[$key])) {
                    foreach($output[$key] as $datasub) {
                        if ($downs['category_id'] != $datasub['id']) {
                            $disabled = ! empty($datasub['closed']) ? ' disabled' : '';
                            echo '<option value="'.$datasub['id'].'"'.$disabled.'>– '.$datasub['name'].'</option>';
                        }
                    }
                }
            }

            echo '</select>';

            echo '<input type="submit" value="Переместить"></form></div><br>';

        } elseif(count($cats) == 1) {
            showError('Нет доступных разделов для перемещения!');
        } else {
            showError('Разделы загрузок еще не созданы!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=down&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                    Перемещение файла                                   ##
############################################################################################
case 'addmovedown':

    $uid = check($_GET['uid']);
    $section = abs(intval($_POST['section']));

    if ($uid == $_SESSION['token']) {

        $catsTo = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `id`=? LIMIT 1;", [$section]);

        if (! empty($catsTo)) {

            $downFrom  = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);
            if (!empty($downFrom)) {

                $folderFrom = $downFrom['folder'] ? $downFrom['folder'].'/' : '';
                $folderTo = $catsTo['folder'] ? $catsTo['folder'].'/' : '';

                rename(UPLOADS.'/files/'.$folderFrom.$downFrom['link'], UPLOADS.'/files/'.$folderTo.$downFrom['link']);

                DB::update("UPDATE `downs` SET `category_id`=? WHERE `id`=?;", [$section, $id]);
                DB::update("UPDATE `comments` SET `relate_category_id`=? WHERE relate_type=? AND `relate_id`=?;", ['down', $section, $id]);
                // Обновление счетчиков
                DB::update("UPDATE `cats` SET `count`=`count`+1 WHERE `id`=?", [$section]);
                DB::update("UPDATE `cats` SET `count`=`count`-1 WHERE `id`=?", [$cid]);

                setFlash('success', 'Файл успешно перемещен!');
                redirect("/admin/load?act=down&cid=$section");
            } else {
                showError('Ошибка! Файла для перемещения не существует!');
            }
        } else {
            showError('Ошибка! Выбранного раздела не существует!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=movedown&amp;id='.$id.'">Вернуться</a><br>';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/load?act=down&amp;cid='.$cid.'">К разделам</a><br>';
break;

############################################################################################
##                                   Удаление файлов                                      ##
############################################################################################
case 'deldown':

    $uid = check($_GET['uid']);
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if (isAdmin([101]) && getUser('login') == env('SITE_ADMIN')) {
        if ($uid == $_SESSION['token']) {
            if ($del > 0) {
                $del = implode(',', $del);

                if (is_writable(UPLOADS.'/files')) {

                    $querydel = DB::select("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id` IN (".$del.");");
                    $arr_script = $querydel -> fetchAll();

                    DB::delete("DELETE FROM `comments` WHERE relate_type=? AND `relate_id` IN (".$del.");", ['down']);
                    $deldowns = DB::run() -> exec("DELETE FROM `downs` WHERE `id` IN (".$del.");");
                    // Обновление счетчиков
                    DB::update("UPDATE `cats` SET `count`=`count`-? WHERE `id`=?", [$deldowns, $cid]);

                    foreach ($arr_script as $delfile) {
                        $folder = $delfile['folder'] ? $delfile['folder'].'/' : '';
                        if (!empty($delfile['link']) && file_exists(UPLOADS.'/files/'.$folder.$delfile['link'])) {
                            unlink(UPLOADS.'/files/'.$folder.$delfile['link']);
                        }

                        deleteImage('uploads/screen/'.$folder, $delfile['screen']);
                    }

                    setFlash('success', 'Выбранные файлы успешно удалены!');
                    redirect("/admin/load?act=down&cid=$cid&page=$page");
                } else {
                    showError('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
                }
            } else {
                showError('Ошибка! Отсутствуют выбранные файлы!');
            }
        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Ошибка! Удалять файлы могут только суперадмины!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/load?act=down&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br>';
break;

