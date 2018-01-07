<?php

/**
 * Просмотр ожидающих модерации
 */
case 'waiting':

    //show_title('Список ожидающих модерации файлов');

    echo '<i class="fa fa-book"></i> <a href="/load/add">Публикация</a> / ';
    echo '<b>Ожидающие</b> / ';
    echo '<a href="/load/active">Проверенные</a><hr>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `user`=?;", [0, getUser('login')]);

    if ($total > 0) {
        $querynew = DB::select("SELECT `downs`.*, `name` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? AND `user`=? ORDER BY `time` DESC;", [0, getUser('login')]);

        while ($data = $querynew -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-download"></i> ';

            echo '<b><a href="/load/add?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.dateFixed($data['time']).')</div>';
            echo '<div>';
            echo 'Категория: '.$data['name'].'<br>';
            if (!empty($data['link'])) {
                echo 'Файл: '.$data['link'].' ('.formatFileSize(UPLOADS.'/files/'.$data['link']).')<br>';
            } else {
                echo 'Файл: <span style="color:#ff0000">Не загружен</span><br>';
            }
            if (!empty($data['screen'])) {
                echo 'Скрин: '.$data['screen'].' ('.formatFileSize(UPLOADS.'/files/'.$data['screen']).')<br>';
            } else {
                echo 'Скрин: <span style="color:#ff0000">Не загружен</span><br>';
            }
            echo '</div>';
        }

        echo '<br>';
    } else {
        showError('Ожидающих модерации файлов еще нет!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add">Вернуться</a><br>';
break;

/**
 * Подготовка к редактированию файла
 */
case 'view':

    //show_title('Редактирование ожидающего файла');

    echo '<i class="fa fa-book"></i> <a href="/load/add">Публикация</a> / ';
    echo '<b><a href="/load/add?act=waiting">Ожидающие</a></b> / ';
    echo '<a href="/load/active?act=files">Проверенные</a><hr>';

    $new = DB::run() -> queryFetch("SELECT * FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($new)) {
        $downs = DB::select("SELECT `id`, `parent`, `name` FROM `cats` WHERE `closed`=0 ORDER BY `sort` ASC;") -> fetchAll();
        if (count($downs) > 0) {
            if ($new['user'] == getUser('login')) {
                if (empty($new['active'])) {

                    $folder = $new['folder'] ? $new['folder'].'/' : '';

                    echo '<a href="/load">Категории</a> / ';

                    if (!empty($new['parent'])) {
                        $podcats = DB::run() -> queryFetch("SELECT `id`, `name` FROM `cats` WHERE `id`=? LIMIT 1;", [$new['parent']]);
                        echo '<a href="/load/down?cid='.$podcats['id'].'">'.$podcats['name'].'</a> / ';
                    }

                    echo '<a href="/load/down?act=view&amp;id='.$id.'">Обзор файла</a><br><br>';

                    echo '<div class="info"><b>Внимание!</b> Данная загрузка опубликована, но еще требует модераторской проверки<br>После проверки вы не сможете отредактировать описание и загрузить файл или скриншот</div><br>';

                    if (empty($new['link'])) {

                        echo '<b><big>Загрузка файла</big></b><br><br>';
                        echo '<div class="info">';
                        echo '<form action="/load/add?act=loadfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
                        echo 'Прикрепить файл* ('.setting('allowextload').'):<br><input type="file" name="loadfile"><br>';
                        echo '<input value="Загрузить" type="submit"></form><br>';

                        echo 'Максимальный вес файла: '.formatSize(setting('fileupload')).'</div><br>';

                    } else {

                        echo '<i class="fa fa-download"></i> <b><a href="/uploads/files/'.$folder.$new['link'].'">'.$new['link'].'</a></b> ('.formatFileSize(UPLOADS.'/files/'.$folder.$new['link']).') (<a href="/load/add?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br>';

                        $ext = getExtension($new['link']);
                        if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
                            if (empty($new['screen'])) {
                                echo '<br><b><big>Загрузка скриншота</big></b><br><br>';
                                echo '<div class="info">';
                                echo '<form action="/load/add?act=loadscreen&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
                                echo 'Прикрепить скрин (jpg,jpeg,gif,png):<br><input type="file" name="screen"><br>';
                                echo '<input value="Загрузить" type="submit"></form><br>';

                                echo 'Максимальный вес скриншота: '.formatSize(setting('screenupload')).'<br>';
                                echo 'Требуемый размер скриншота: от 100 до '.setting('screenupsize').' px</div><br><br>';

                            } else {
                                echo '<i class="fa fa-image"></i> <b><a href="/uploads/screen/'.$folder.$new['screen'].'">'.$new['screen'].'</a></b> ('.formatFileSize(UPLOADS.'/screen/'.$folder.$new['screen']).') (<a href="/load/add?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br><br>';
                                echo resizeImage('uploads/screen/'.$folder, $new['screen']).'<br>';
                            }
                        }
                    }

                    echo '<br>';
                    echo '<b><big>Редактирование</big></b><br><br>';
                    echo '<div class="form">';
                    echo '<form action="/load/add?act=edit&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo 'Категория*:<br>';

                    $output = [];

                    foreach ($downs as $row) {
                        $i = $row['id'];
                        $p = $row['parent'];
                        $output[$p][$i] = $row;
                    }

                    echo '<select name="cid">';

                    foreach ($output[0] as $key => $data) {
                        $selected = ($new['id'] == $data['id']) ? ' selected' : '';
                        echo '<option value="'.$data['id'].'"'.$selected.'>'.$data['name'].'</option>';

                        if (isset($output[$key])) {
                            foreach($output[$key] as $datasub) {
                                $selected = ($new['id'] == $datasub['id']) ? ' selected' : '';
                                echo '<option value="'.$datasub['id'].'"'.$selected.'>– '.$datasub['name'].'</option>';
                            }
                        }
                    }

                    echo '</select><br>';

                    echo 'Название*:<br>';
                    echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['title'].'"><br>';
                    echo 'Описание*:<br>';
                    echo '<textarea cols="25" rows="5" name="text">'.$new['text'].'</textarea><br>';
                    echo 'Автор файла:<br>';
                    echo '<input type="text" name="author" maxlength="50" value="'.$new['author'].'"><br>';
                    echo 'Сайт автора:<br>';
                    echo '<input type="text" name="site" maxlength="50" value="'.$new['site'].'"><br>';

                    echo '<input value="Изменить" type="submit"></form></div><br>';
                    echo 'Все поля отмеченные знаком *, обязательны для заполнения<br><br>';

                } else {
                    showError('Ошибка! Данный файл уже проверен модератором!');
                }
            } else {
                showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
            }
        } else {
            showError('Категории файлов еще не созданы!');
        }
    } else {
        showError('Данного файла не существует!');
    }

break;

/**
 * Редактирование файла
 */
case 'edit':

    //show_title('Редактирование ожидающего файла');

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
    $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

    if ($uid == $_SESSION['token']) {
        if (utfStrlen($title) >= 5 && utfStrlen($title) <= 50) {
            if (utfStrlen($text) >= 50 && utfStrlen($text) <= 5000) {
                if (utfStrlen($author) <= 50) {
                    if (utfStrlen($site) <= 50) {
                        if (empty($site) || preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                            $new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);
                            if (!empty($new)) {
                                if (empty($downs['closed'])) {
                                    if ($new['user'] == getUser('login')) {
                                        if (empty($new['active'])) {

                                            $categories = DB::run() -> querySingle("SELECT `id` FROM `cats` WHERE `id`=? LIMIT 1;", [$cid]);
                                            if (!empty($categories)) {

                                                $newtitle = DB::run() -> querySingle("SELECT `title` FROM `downs` WHERE `title`=? AND `id`<>? LIMIT 1;", [$title, $id]);
                                                if (empty($newtitle)) {

                                                    DB::update("UPDATE `downs` SET `category_id`=?, `title`=?, `text`=?, `author`=?, `site`=?, `time`=? WHERE `id`=?;", [$cid, $title, $text, $author, $site, $new['time'], $id]);

                                                    setFlash('success', 'Данные успешно изменены!');
                                                    redirect("/load/add?act=view&id=$id");

                                                } else {
                                                    showError('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
                                                }
                                            } else {
                                                showError('Ошибка! Выбранный вами раздел не существует!');
                                            }
                                        } else {
                                            showError('Ошибка! Данный файл уже проверен модератором!');
                                        }
                                    } else {
                                        showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
                                    }
                                } else {
                                    showError('Ошибка! В данный раздел запрещена загрузка файлов!');
                                }
                            } else {
                                showError('Данного файла не существует!');
                            }
                        } else {
                            showError('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                        }
                    } else {
                        showError('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
                }
            } else {
                showError('Ошибка! Слишком длинный или короткий текст описания (от 50 до 5000 символов)!');
            }
        } else {
            showError('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;


case 'loadfile':


                        $filename = check(strtolower($_FILES['loadfile']['name']));
                        $isVideo  = strstr($_FILES['loadfile']['type'], "video/") ? true : false;



                                            // Обработка видео
                                            if ($isVideo && env('FFMPEG_ENABLED')) {

                                                $ffconfig = [
                                                    'ffmpeg.binaries'  => env('FFMPEG_PATH'),
                                                    'ffprobe.binaries' => env('FFPROBE_PATH'),
                                                    'timeout'          => 3600,
                                                    'ffmpeg.threads'   => 4,
                                                ];

                                                $ffmpeg = FFMpeg\FFMpeg::create($ffconfig);

                                                $video = $ffmpeg->open(HOME . '/uploads/files/' . $folder . $filename);

                                                // Сохраняем скрин с 5 секунды
                                                $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(5));
                                                $frame->save(HOME . '/uploads/screen/' . $folder . '/' . $filename . '.jpg');

                                                if (file_exists(HOME . '/uploads/screen/' . $folder . '/' . $filename . '.jpg')) {
                                                    DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", [$filename . '.jpg', $id]);
                                                }

                                                // Перекодируем видео в h264
                                                $ffprobe = FFMpeg\FFProbe::create($ffconfig);
                                                $codec = $ffprobe
                                                    ->streams(HOME . '/uploads/files/' . $folder . $filename)
                                                    ->videos()
                                                    ->first()
                                                    ->get('codec_name');

                                                if ($ext == 'mp4' && $codec != 'h264') {
                                                    $format = new FFMpeg\Format\Video\X264('libmp3lame', 'libx264');
                                                    $video->save($format, HOME . '/uploads/files/' . $folder . '/convert-' . $filename);
                                                    rename(
                                                        HOME . '/uploads/files/' . $folder . '/convert-' . $filename,
                                                        HOME . '/uploads/files/' . $folder . '/' . $filename
                                                    );
                                                }
                                            }


    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление файла
 */
case 'delfile':

    $link = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($link)) {
        if ($link['user'] == getUser('login')) {
            if (empty($link['active'])) {
                $folder = $link['folder'] ? $link['folder'].'/' : '';

                if (!empty($link['link']) && file_exists(UPLOADS.'/files/'.$folder.$link['link'])) {
                    unlink(UPLOADS.'/files/'.$folder.$link['link']);
                }

                deleteImage('uploads/files/'.$folder, $link['link']);
                deleteImage('uploads/screen/'.$folder, $link['screen']);

                DB::update("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", ['', '', $id]);

                setFlash('success', 'Файл успешно удален!');
                redirect("/load/add?act=view&id=$id");

            } else {
                showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление скриншота
 */
case 'delscreen':

    $screen = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($screen)) {
        if ($screen['user'] == getUser('login')) {
            if (empty($screen['active'])) {
                $folder = $screen['folder'] ? $screen['folder'].'/' : '';

                deleteImage('uploads/screen/'.$folder, $screen['screen']);

                DB::update("UPDATE `downs` SET `screen`=? WHERE `id`=?;", ['', $id]);

                setFlash('success', 'Скриншот успешно удален!');
                redirect("/load/add?act=view&id=$id");

            } else {
                showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;
