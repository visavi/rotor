<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$cid = (isset($_GET['cid'])) ? abs(intval($_GET['cid'])) : 0;
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

if (is_user()) {
if (is_admin() || Setting::get('downupload') == 1) {

switch ($action):
/**
 * Главная страница
 */
case 'index':

    //show_title('Публикация нового файла');

    echo '<i class="fa fa-book"></i> <b>Публикация</b> / ';
    echo '<a href="/load/add?act=waiting">Ожидающие</a> / ';
    echo '<a href="/load/active">Проверенные</a><hr>';

    if (Setting::get('home') == 'http://visavi.net') {
        echo '<div class="info">';
        echo '<i class="fa fa-question-circle"></i> Перед публикацией скрипта настоятельно рекомендуем ознакомиться с <a href="/load/add?act=rules&amp;cid='.$cid.'">правилами оформления скриптов</a><br>';
        echo 'Чем лучше вы оформите свой скрипт, тем быстрее он будет опубликован и добавлен в общий каталог</div><br>';
    }

    $querydown = DB::run() -> query("SELECT * FROM `cats` ORDER BY `sort` ASC;");
    $downs = $querydown -> fetchAll();

    if (count($downs) > 0) {
        echo '<div class="form">';
        echo '<form action="/load/add?act=add&amp;uid='.$_SESSION['token'].'" method="post">';
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
            $selected = $cid == $data['id'] ? ' selected="selected"' : '';
            $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
            echo '<option value="'.$data['id'].'"'.$selected.$disabled.'>'.$data['name'].'</option>';

            if (isset($output[$key])) {
                foreach($output[$key] as $datasub) {
                    $selected = ($cid == $datasub['id']) ? ' selected="selected"' : '';
                    $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
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
        App::showError('Категории файлов еще не созданы!');
    }
break;

/**
 * Просмотр ожидающих модерации
 */
case 'waiting':

    //show_title('Список ожидающих модерации файлов');

    echo '<i class="fa fa-book"></i> <a href="/load/add">Публикация</a> / ';
    echo '<b>Ожидающие</b> / ';
    echo '<a href="/load/active">Проверенные</a><hr>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `user`=?;", [0, App::getUsername()]);

    if ($total > 0) {
        $querynew = DB::run() -> query("SELECT `downs`.*, `name` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? AND `user`=? ORDER BY `time` DESC;", [0, App::getUsername()]);

        while ($data = $querynew -> fetch()) {
            echo '<div class="b">';

            echo '<i class="fa fa-download"></i> ';

            echo '<b><a href="/load/add?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.date_fixed($data['time']).')</div>';
            echo '<div>';
            echo 'Категория: '.$data['name'].'<br>';
            if (!empty($data['link'])) {
                echo 'Файл: '.$data['link'].' ('.read_file(HOME.'/uploads/files/'.$data['link']).')<br>';
            } else {
                echo 'Файл: <span style="color:#ff0000">Не загружен</span><br>';
            }
            if (!empty($data['screen'])) {
                echo 'Скрин: '.$data['screen'].' ('.read_file(HOME.'/uploads/files/'.$data['screen']).')<br>';
            } else {
                echo 'Скрин: <span style="color:#ff0000">Не загружен</span><br>';
            }
            echo '</div>';
        }

        echo '<br>';
    } else {
        App::showError('Ожидающих модерации файлов еще нет!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add">Вернуться</a><br>';
break;

/**
 * Публикация файла
 */
case 'add':

    //show_title('Публикация нового файла');

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
    $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

    if ($uid == $_SESSION['token']) {
        if (!empty($cid)) {
            if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                if (utf_strlen($text) >= 50 && utf_strlen($text) <= 5000) {
                    if (utf_strlen($author) <= 50) {
                        if (utf_strlen($site) <= 50) {
                            if (empty($site) || preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {

                                $downs = Category::find_one($cid);

                                if (!empty($downs)) {
                                    if (empty($downs['closed'])) {
                                        $downtitle = DB::run() -> querySingle("SELECT `title` FROM `downs` WHERE `title`=? LIMIT 1;", [$title]);
                                        if (empty($downtitle)) {

                                            //DB::run() -> query("UPDATE `cats` SET `count`=`count`+1 WHERE `category_id`=?", array($cid));
                                            DB::run() -> query("INSERT INTO `downs` (`category_id`, `title`, `text`, `link`, `user`, `author`, `site`, `screen`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);", [$cid, $title, $text, '', App::getUsername(), $author, $site, '', SITETIME]);

                                            $lastid = DB::run() -> lastInsertId();

                                            App::setFlash('success', 'Данные успешно добавлены!');
                                            App::redirect("/load/add?act=view&id=$lastid");

                                        } else {
                                            App::showError('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
                                        }
                                    } else {
                                        App::showError('Ошибка! В данный раздел запрещена загрузка файлов!');
                                    }
                                } else {
                                    App::showError('Ошибка! Выбранный вами раздел не существует!');
                                }
                            } else {
                                App::showError('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                            }
                        } else {
                            App::showError('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                        }
                    } else {
                        App::showError('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
                    }
                } else {
                    App::showError('Ошибка! Слишком длинный или короткий текст описания (от 50 до 5000 символов)!');
                }
            } else {
                App::showError('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
            }
        } else {
            App::showError('Ошибка! Вы не выбрали категорию для добавления файла!');
        }
    } else {
        App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=newfile&amp;cid='.$cid.'">Вернуться</a><br>';
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
        $downs = DB::run() -> query("SELECT `id`, `parent`, `name` FROM `cats` WHERE `closed`=0 ORDER BY `sort` ASC;") -> fetchAll();
        if (count($downs) > 0) {
            if ($new['user'] == App::getUsername()) {
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
                        echo 'Прикрепить файл* ('.Setting::get('allowextload').'):<br><input type="file" name="loadfile"><br>';
                        echo '<input value="Загрузить" type="submit"></form><br>';

                        echo 'Максимальный вес файла: '.formatsize(Setting::get('fileupload')).'</div><br>';

                    } else {

                        echo '<i class="fa fa-download"></i> <b><a href="/uploads/files/'.$folder.$new['link'].'">'.$new['link'].'</a></b> ('.read_file(HOME.'/uploads/files/'.$folder.$new['link']).') (<a href="/load/add?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br>';

                        $ext = getExtension($new['link']);
                        if ($ext != 'jpg' && $ext != 'jpeg' && $ext != 'gif' && $ext != 'png') {
                            if (empty($new['screen'])) {
                                echo '<br><b><big>Загрузка скриншота</big></b><br><br>';
                                echo '<div class="info">';
                                echo '<form action="/load/add?act=loadscreen&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
                                echo 'Прикрепить скрин (jpg,jpeg,gif,png):<br><input type="file" name="screen"><br>';
                                echo '<input value="Загрузить" type="submit"></form><br>';

                                echo 'Максимальный вес скриншота: '.formatsize(Setting::get('screenupload')).'<br>';
                                echo 'Требуемый размер скриншота: от 100 до '.Setting::get('screenupsize').' px</div><br><br>';

                            } else {
                                echo '<i class="fa fa-picture-o"></i> <b><a href="/uploads/screen/'.$folder.$new['screen'].'">'.$new['screen'].'</a></b> ('.read_file(HOME.'/uploads/screen/'.$folder.$new['screen']).') (<a href="/load/add?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br><br>';
                                echo resize_image('uploads/screen/'.$folder, $new['screen'], Setting::get('previewsize')).'<br>';
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
                        $selected = ($new['id'] == $data['id']) ? ' selected="selected"' : '';
                        echo '<option value="'.$data['id'].'"'.$selected.'>'.$data['name'].'</option>';

                        if (isset($output[$key])) {
                            foreach($output[$key] as $datasub) {
                                $selected = ($new['id'] == $datasub['id']) ? ' selected="selected"' : '';
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
                    App::showError('Ошибка! Данный файл уже проверен модератором!');
                }
            } else {
                App::showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
            }
        } else {
            App::showError('Категории файлов еще не созданы!');
        }
    } else {
        App::showError('Данного файла не существует!');
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
        if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
            if (utf_strlen($text) >= 50 && utf_strlen($text) <= 5000) {
                if (utf_strlen($author) <= 50) {
                    if (utf_strlen($site) <= 50) {
                        if (empty($site) || preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                            $new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);
                            if (!empty($new)) {
                                if (empty($downs['closed'])) {
                                    if ($new['user'] == App::getUsername()) {
                                        if (empty($new['active'])) {

                                            $categories = DB::run() -> querySingle("SELECT `id` FROM `cats` WHERE `id`=? LIMIT 1;", [$cid]);
                                            if (!empty($categories)) {

                                                $newtitle = DB::run() -> querySingle("SELECT `title` FROM `downs` WHERE `title`=? AND `id`<>? LIMIT 1;", [$title, $id]);
                                                if (empty($newtitle)) {

                                                    DB::run() -> query("UPDATE `downs` SET `category_id`=?, `title`=?, `text`=?, `author`=?, `site`=?, `time`=? WHERE `id`=?;", [$cid, $title, $text, $author, $site, $new['time'], $id]);

                                                    App::setFlash('success', 'Данные успешно изменены!');
                                                    App::redirect("/load/add?act=view&id=$id");

                                                } else {
                                                    App::showError('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
                                                }
                                            } else {
                                                App::showError('Ошибка! Выбранный вами раздел не существует!');
                                            }
                                        } else {
                                            App::showError('Ошибка! Данный файл уже проверен модератором!');
                                        }
                                    } else {
                                        App::showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
                                    }
                                } else {
                                    App::showError('Ошибка! В данный раздел запрещена загрузка файлов!');
                                }
                            } else {
                                App::showError('Данного файла не существует!');
                            }
                        } else {
                            App::showError('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                        }
                    } else {
                        App::showError('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                    }
                } else {
                    App::showError('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
                }
            } else {
                App::showError('Ошибка! Слишком длинный или короткий текст описания (от 50 до 5000 символов)!');
            }
        } else {
            App::showError('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
        }
    } else {
        App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Загрузка файла
 */
case 'loadfile':
    //show_title('Загрузка файла');

    $down = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    $folder = $down['folder'] ? $down['folder'].'/' : '';

    if (!empty($down)) {
        if ($down['user'] == App::getUsername()) {
            if (empty($down['active'])) {
                if (empty($down['link'])) {
                    if (is_writeable(HOME.'/uploads/files/'.$folder)) {
                    if (isset($_FILES['loadfile']) && is_uploaded_file($_FILES['loadfile']['tmp_name'])) {

                        $filename = check(strtolower($_FILES['loadfile']['name']));
                        $isVideo  = strstr($_FILES['loadfile']['type'], "video/") ? true : false;

                        if (strlen($filename) <= 50) {
                            if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {

                                $ext = getExtension($filename);

                                if (in_array($ext, explode(',', Setting::get('allowextload')), true)) {
                                    if ($_FILES['loadfile']['size'] > 0 && $_FILES['loadfile']['size'] <= Setting::get('fileupload')) {
                                        $downlink = DB::run() -> querySingle("SELECT `link` FROM `downs` WHERE `link`=? LIMIT 1;", [$filename]);
                                        if (empty($downlink)) {

                                            move_uploaded_file($_FILES['loadfile']['tmp_name'], HOME.'/uploads/files/'.$folder.$filename);
                                            @chmod(HOME.'/uploads/files/'.$folder.$filename, 0666);

                                            copyright_archive(HOME.'/uploads/files/'.$folder.$filename);

                                            DB::run() -> query("UPDATE `downs` SET `link`=? WHERE `id`=?;", [$filename, $id]);

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
                                                    DB::run()->query("UPDATE `downs` SET `screen`=? WHERE `id`=?;", [$filename . '.jpg', $id]);
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

                                            App::setFlash('success', 'Файл успешно загружен!');
                                            App::redirect("/load/add?act=view&id=$id");

                                        } else {
                                            App::showError('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
                                        }
                                    } else {
                                        App::showError('Ошибка! Максимальный размер загружаемого файла '.formatsize(Setting::get('fileupload')).'!');
                                    }
                                } else {
                                    App::showError('Ошибка! Недопустимое расширение файла!');
                                }
                            } else {
                                App::showError('Ошибка! В названии файла присутствуют недопустимые символы!');
                            }
                        } else {
                            App::showError('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
                        }
                    } else {
                        App::showError('Ошибка! Не удалось загрузить файл!');
                    }
                    } else {
                        App::showError('Ошибка! Директория для файлов недоступна для записи!');
                    }
                } else {
                    App::showError('Ошибка! Файл уже загружен!');
                }
            } else {
                App::showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            App::showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
        }
    } else {
        App::showError('Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Загрузка скриншота
 */
case 'loadscreen':
    //show_title('Загрузка скриншота');

    $down = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($down)) {
        if ($down['user'] == App::getUsername()) {
            if (empty($down['active'])) {
                if (empty($down['screen'])) {
                    if (is_uploaded_file($_FILES['screen']['tmp_name'])) {

                        // ------------------------------------------------------//
                        $handle = upload_image($_FILES['screen'], Setting::get('screenupload'), Setting::get('screenupsize'),  $down['link']);
                        if ($handle) {
                            $folder = $down['folder'] ? $down['folder'].'/' : '';

                            $handle -> process(HOME.'/uploads/screen/'.$folder);
                            if ($handle -> processed) {

                                DB::run() -> query("UPDATE `downs` SET `screen`=? WHERE `id`=?;", [$handle -> file_dst_name, $id]);
                                $handle -> clean();

                                App::setFlash('success', 'Скриншот успешно загружен!');
                                App::redirect("/load/add?act=view&id=$id");

                            } else {
                                App::showError($handle -> error);
                            }
                        } else {
                            App::showError('Ошибка! Не удалось загрузить изображение!');
                        }
                    } else {
                        App::showError('Ошибка! Вы не загрузили скриншот!');
                    }
                } else {
                    App::showError('Ошибка! Скриншот уже загружен!');
                }
            } else {
                App::showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            App::showError('Ошибка! Изменение невозможно, вы не автор данного файла!');
        }
    } else {
        App::showError('Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление файла
 */
case 'delfile':

    $link = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($link)) {
        if ($link['user'] == App::getUsername()) {
            if (empty($link['active'])) {
                $folder = $link['folder'] ? $link['folder'].'/' : '';

                if (!empty($link['link']) && file_exists(HOME.'/uploads/files/'.$folder.$link['link'])) {
                    unlink(HOME.'/uploads/files/'.$folder.$link['link']);
                }

                unlink_image('uploads/files/'.$folder, $link['link']);
                unlink_image('uploads/screen/'.$folder, $link['screen']);

                DB::run() -> query("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", ['', '', $id]);

                App::setFlash('success', 'Файл успешно удален!');
                App::redirect("/load/add?act=view&id=$id");

            } else {
                App::showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            App::showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        App::showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление скриншота
 */
case 'delscreen':

    $screen = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

    if (!empty($screen)) {
        if ($screen['user'] == App::getUsername()) {
            if (empty($screen['active'])) {
                $folder = $screen['folder'] ? $screen['folder'].'/' : '';

                unlink_image('uploads/screen/'.$folder, $screen['screen']);

                DB::run() -> query("UPDATE `downs` SET `screen`=? WHERE `id`=?;", ['', $id]);

                App::setFlash('success', 'Скриншот успешно удален!');
                App::redirect("/load/add?act=view&id=$id");

            } else {
                App::showError('Ошибка! Данный файл уже проверен модератором!');
            }
        } else {
            App::showError('Ошибка! Удаление невозможно, вы не автор данного файла!');
        }
    } else {
        App::showError('Ошибка! Данного файла не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?act=view&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Правила
 */
case 'rules':
    if (Setting::get('home') == 'http://visavi.net') {

        //show_title('Правила оформления скриптов');

        echo '<b><span style="color:#ff0000">Внимание! Запрещено выкладывать платные скрипты или скрипты не предназначенные для свободного распространения.<br>
Запрещено размещать скрипты накрутчиков, скрипты для спама, взлома или любые вредоносные скрипты</span></b><br><br>';

        echo 'Чтобы не превращать архив скриптов в свалку мусора, все скрипты на нашем сайте проходят ручную обработку<br>';
        echo 'Если вы хотите добавить скрипт, НЕ обязательно быть его автором, но вы обязательно должны указать данные и контакты автора скрипта<br>';
        echo 'Также если вы автор модификации или небольшой переделки, то можете указать и свои данные в описании к скрипту<br><br>';

        echo '<b>Авторские права</b><br>';
        echo 'Для размещения скрипта в нашем архиве вы должны быть автором этого скрипта, у вас должны быть эсклюзивные права для размещения этого скрипта или лицензия на распространения скрипта<br>';
        echo 'Не рекомендуется публиковать скрипт если вы не уверены, что он распространяется свободно или автор не против этого<br>';
        echo 'Все скрипты размещенные у нас не могут быть удалены с нашего сайта, исключением является публикация скрипта без согласия автора или выложенных с нарушением текущих правил и только по требованию автора<br>';
        echo 'Размещая скрипт у нас вы автоматически соглашаетесь со всеми правилами<br><br>';

        echo '<b>В архиве со скриптом должны быть следующие, обязательные файлы:</b><br>';

        echo '<b>1.</b> Сам скрипт. Все файлы необходимые для нормальной работы<br>';
        echo '<b>2.</b> Инструкция по установке. Как правильно установить скрипт<br>';
        echo '<b>3.</b> Полное описание скрипта. Какие функции имеются в этом скрипте, возможности и т.д.<br>';
        echo '<b>4.</b> Требования для работы. (К примеру PHP4, HTML, библиотека ICONV)<br>';
        echo '<b>5.</b> Автор скрипта и(или) автор модификации<br>';
        echo '<b>6.</b> Контакты авторов (адрес сайта)<br>';
        echo '<b>7.</b> Красивое и уникальное название архива и скрипта<br><br>';

        echo '<b>Примеры описания скриптов</b><br>';
        echo 'Название: <b>cat_skor</b><br>';
        echo 'Каталог мобильных сайтов в трех версиях: wml xhtml и html.<br>
Возможности<br>
- Полная статистика каталога: переходы по дням, по месяцам и за все время.<br>
- Полная статистика по каждому сайту: переходы по дням, месяцам, переходы за все время, описание.<br>
- Автоудаление неактивных сайтов.<br>
- Отчет на email за каждый день ....... (и т.д.)
<br>
Требования: PHP4, MySQL, WML, (X)HTML, CRON<br>
Автор cкрипта: skor<br>
Сайт автора http://xwap.org<br><br>';

        echo '<b>Ограничения:</b><br>';
        echo 'К загрузке допускаются архивы в формате zip, скриншоты можно загружать в форматах jpg, jpeg, gif и png<br>';
        echo 'Максимальный вес архива: '.formatsize(Setting::get('fileupload')).'<br>';
        echo 'Максимальный вес скриншота: '.formatsize(Setting::get('screenupload')).'<br>';
        echo 'Требуемый размер скриншота: от 100 до '.Setting::get('screenupsize').' px<br><br>';

        echo '<b>Рекомендации:</b><br>';
        echo 'Чем лучше вы оформите скрипт при публикации, тем быстрее он будет проверен и размещен в архиве<br>';
        echo 'Рекомендуем самостоятельно подготовить хорошее и граммотное описание скрипта, а не просто скопировать и вставить текст<br>';
        echo 'Важным моментом является выбор названия и имени архива со скриптом, они должны быть уникальными, нельзя добавлять к примеру gb.zip, forum.zip и т.д. так как эти названия не уникальные и подходят под большинство скриптов выбранной категории<br>';
        echo 'Название и имя архива не должны быть слишком короткими или длинными, не должны быть чересчур информативными<br><br>';

        echo 'После проверки ваш скрипт будет размещен в нашем архиве и станет доступным для скачивания, добавления оценок и комментариев<br><br>';

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/load/add?cid='.$cid.'">Вернуться</a><br>';
    }
break;

endswitch;

} else {
    App::showError('Возможность добавление файлов запрещена администрацией сайта');
}

} else {
    App::showError('Вы не авторизованы, для добавления файла, необходимо');
}

App::view('includes/back', ['link' => '/load/', 'title' => 'Категории']);

App::view(Setting::get('themes').'/foot');
