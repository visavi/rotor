<?php
App::view($config['themes'].'/index');

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;
$cid = isset($_GET['cid']) ? abs(intval($_GET['cid'])) : 0;
$act = isset($_GET['act']) ? check($_GET['act']) : 'index';
$start = isset($_GET['start']) ? abs(intval($_GET['start'])) : 0;

if (is_admin(array(101, 102))) {
show_title('Управление загрузками');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $querydown = DB::run() -> query("SELECT `c`.*, (SELECT SUM(`cats_count`) FROM `cats` WHERE `cats_parent`=`c`.`cats_id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `downs_cats_id`=`cats_id` AND `downs_active`=? AND `downs_time` > ?) AS `new` FROM `cats` `c` ORDER BY `cats_order` ASC;", array(1, SITETIME-86400 * 5));
    $downs = $querydown -> fetchAll();

    if (count($downs) > 0) {
        $output = array();

        foreach ($downs as $row) {
            $id = $row['cats_id'];
            $fp = $row['cats_parent'];
            $output[$fp][$id] = $row;
        }

        foreach($output[0] as $key => $data) {
            echo '<img src="/images/img/dir.gif" alt="image" /> ';
            echo $data['cats_order'].'. <b><a href="/admin/load?act=down&amp;cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

            $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
            $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

            echo '('.$data['cats_count'] . $subcnt . $new.')<br />';

            if (is_admin(array(101))) {
                echo '<a href="/admin/load?act=editcats&amp;cid='.$data['cats_id'].'">Редактировать</a> / ';
                echo '<a href="/admin/load?act=prodelcats&amp;cid='.$data['cats_id'].'">Удалить</a><br />';
            }
            // ----------------------------------------------------//
            if (isset($output[$key])) {
                foreach($output[$key] as $data) {
                    echo '<img src="/images/img/right.gif" alt="image" /> ';
                    echo $data['cats_order'].'. <b><a href="/admin/load?act=down&amp;cid='.$data['cats_id'].'">'.$data['cats_name'].'</a></b> ';

                    $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
                    $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';

                    echo '('.$data['cats_count'] . $subcnt . $new.')';

                    if (is_admin(array(101))) {
                        echo ' (<a href="/admin/load?act=editcats&amp;cid='.$data['cats_id'].'">Редактировать</a> / ';
                        echo '<a href="/admin/load?act=prodelcats&amp;cid='.$data['cats_id'].'">Удалить</a>)';
                    }
                    echo '<br />';
                }
            }
        }
    } else {
        show_error('Разделы загрузок еще не созданы!');
    }

    if (is_admin(array(101))) {
        echo '<br /><div class="form">';
        echo '<form action="/admin/load?act=addcats&amp;uid='.$_SESSION['token'].'" method="post">';
        echo '<b>Раздел:</b><br />';
        echo '<input type="text" name="name" maxlength="50" />';
        echo '<input type="submit" value="Создать раздел" /></form></div><br />';

        echo '<img src="/images/img/circle.gif" alt="image" /> <a href="/admin/load?act=newimport">FTP-импорт</a><br />';
        echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/admin/load?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
    }

    echo '<img src="/images/img/open.gif" alt="image" /> <a href="/admin/load?act=newfile">Добавить</a><br />';
break;

############################################################################################
##                                      FTP-импорт                                        ##
############################################################################################
case 'newimport':
    $config['newtitle'] = 'FTP-импорт';

    if (is_admin(array(101))) {
        if (file_exists(BASEDIR.'/upload/loader')) {
            $querydown = DB::run() -> query("SELECT * FROM `cats` ORDER BY `cats_order` ASC;");
            $downs = $querydown -> fetchAll();

            if (count($downs) > 0) {
                echo 'Для импорта необходимо загрузить файлы через FTP в папку load/loader, после этого здесь вам нужно выбрать категорию в которую переместить файлы, отметить нужные файлы и нажать импортировать<br /><br />';

                $files = array_diff(scandir(BASEDIR.'/upload/loader'), array('.', '..', '.htaccess'));

                $total = count($files);
                if ($total > 0) {
                    echo '<div class="form">';
                    echo '<form action="/admin/load?act=addimport&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Категория:<br />';

                    $output = array();

                    foreach ($downs as $row) {
                        $i = $row['cats_id'];
                        $p = $row['cats_parent'];
                        $output[$p][$i] = $row;
                    }

                    echo '<select name="cid">';
                    echo '<option value="0">Выберите категорию</option>';

                    foreach ($output[0] as $key => $data) {
                        $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
                        echo '<option value="'.$data['cats_id'].'"'.$disabled.'>'.$data['cats_name'].'</option>';

                        if (isset($output[$key])) {
                            foreach($output[$key] as $datasub) {
                                $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                                echo '<option value="'.$datasub['cats_id'].'"'.$disabled.'>– '.$datasub['cats_name'].'</option>';
                            }
                        }
                    }

                    echo '</select><br /><br />';

                    echo '<input type="checkbox" name="all" onchange="for (i in this.form.elements) this.form.elements[i].checked = this.checked" /> <b>Отметить все</b><br />';

                    foreach ($files as $file) {
                        $ext = getExtension($file);
                        echo '<input type="checkbox" name="files[]" value="'.$file.'" /> <img src="/images/icons/'.icons($ext).'" alt="image" /> '.$file.'<br />';
                    }

                    echo '<input value="Импортировать" type="submit" /></form></div><br />';

                    echo 'Всего файлов: '.$total.'<br /><br />';
                } else {
                    show_error('В директории нет файлов для импорта!');
                }
            } else {
                show_error('Категории файлов еще не созданы!');
            }
        } else {
            show_error('Директория для импорта файлов не создана!');
        }
    } else {
        show_error('Импортировать файлы могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                      FTP-импорт                                        ##
############################################################################################
case 'addimport':

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $files = (!empty($_POST['files'])) ? check($_POST['files']) : array();

    if ($uid == $_SESSION['token']) {
        if (!empty($cid)) {
            if (is_writeable(BASEDIR.'/upload/files')) {
                $total = count($files);
                if ($total > 0) {
                    $downs = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));
                    if (!empty($downs)) {

                        $count = 0;
                        foreach ($files as $file) {
                            $filename = strtolower($file);
                            if (strlen($filename) <= 50) {
                                if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {
                                    $ext = getExtension($filename);
                                    if (in_array($ext, explode(',', $config['allowextload']), true)) {
                                        if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $filename)) {
                                            if (filesize(BASEDIR.'/upload/loader/'.$file) > 0 && filesize(BASEDIR.'/upload/loader/'.$file) <= $config['fileupload']) {

                                                $folder = $downs['folder'] ? $downs['folder'].'/' : '';

                                                $downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? LIMIT 1;", array($file));
                                                if (empty($downlink)) {

                                                    if (file_exists(BASEDIR.'/upload/loader/'.$file.'.txt')) {
                                                        $text = file_get_contents(BASEDIR.'/upload/loader/'.$file.'.txt');
                                                    } else {
                                                        $text = 'Нет описания';
                                                    }

                                                    if (file_exists(BASEDIR.'/upload/loader/'.$file.'.JPG')) {
                                                        rename(BASEDIR.'/upload/loader/'.$file.'.JPG', BASEDIR.'/upload/screen/'.$folder.$filename.'.jpg');
                                                        $screen = $filename.'.jpg';
                                                    } elseif (file_exists(BASEDIR.'/upload/loader/'.$file.'.GIF')) {
                                                        rename(BASEDIR.'/upload/loader/'.$file.'.GIF', BASEDIR.'/upload/screen/'.$folder.$filename.'.gif');
                                                        $screen = $filename.'.gif';
                                                    } else {
                                                        $screen = '';
                                                    }

                                                    rename(BASEDIR.'/upload/loader/'.$file, BASEDIR.'/upload/files/'.$folder.$filename);

                                                    DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cid));
                                                    DB::run() -> query("INSERT INTO `downs` (`downs_cats_id`, `downs_title`, `downs_text`, `downs_link`, `downs_user`, `downs_screen`, `downs_time`, `downs_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);", array($cid, $file, $text, $filename, $log, $screen, SITETIME, 1));

                                                    $count++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if ($count > 0) {
                            echo '<img src="/images/img/open.gif" alt="image" /> <b>Выбранные файлы успешно импортированы</b><br /><br />';
                        }

                        if ($total != $count) {
                            echo 'Не удалось импортировать некоторые файлы!<br />';
                            echo 'Возможные причины: недопустимое расширение файлов, большой вес, недопустимое имя файлов или в имени файла присутствуют недопустимые расширения<br /><br />';
                        }
                    } else {
                        show_error('Ошибка! Выбранный вами раздел не существует!');
                    }
                } else {
                    show_error('Ошибка! Вы не выбрали файлы для импорта!');
                }
            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
            }
        } else {
            show_error('Ошибка! Вы не выбрали категорию для импорта файлов!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=newimport">Вернуться</a><br />';
break;

############################################################################################
##                                    Добавление файла                                    ##
############################################################################################
case 'newfile':
    $config['newtitle'] = 'Публикация нового файла';

    $querydown = DB::run() -> query("SELECT * FROM `cats` ORDER BY `cats_order` ASC;");
    $downs = $querydown -> fetchAll();

    if (count($downs) > 0) {
        echo '<div class="form">';
        echo '<form action="/admin/load?act=addfile&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Категория*:<br />';

        $output = array();

        foreach ($downs as $row) {
            $i = $row['cats_id'];
            $p = $row['cats_parent'];
            $output[$p][$i] = $row;
        }

        echo '<select name="cid">';
        echo '<option value="0">Выберите категорию</option>';

        foreach ($output[0] as $key => $data) {
            $selected = $cid == $data['cats_id'] ? ' selected="selected"' : '';
            $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
            echo '<option value="'.$data['cats_id'].'"'.$selected.$disabled.'>'.$data['cats_name'].'</option>';

            if (isset($output[$key])) {
                foreach($output[$key] as $datasub) {
                    $selected = $cid == $datasub['cats_id'] ? ' selected="selected"' : '';
                    $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                    echo '<option value="'.$datasub['cats_id'].'"'.$selected.$disabled.'>– '.$datasub['cats_name'].'</option>';
                }
            }
        }

        echo '</select><br />';

        echo 'Название*:<br />';
        echo '<input type="text" name="title" size="50" maxlength="50" /><br />';
        echo 'Описание*:<br />';
        echo '<textarea cols="25" rows="10" name="text"></textarea><br />';
        echo 'Автор файла:<br />';
        echo '<input type="text" name="author" maxlength="50" /><br />';
        echo 'Сайт автора:<br />';
        echo '<input type="text" name="site" maxlength="50" value="http://" /><br />';

        echo '<input value="Продолжить" type="submit" /></form></div><br />';

        echo 'Все поля отмеченные знаком *, обязательны для заполнения<br />';
        echo 'Файл и скриншот вы сможете загрузить после добавления описания<br />';
        echo 'Если вы ошиблись в названии или описании файла, вы всегда можете его отредактировать<br /><br />';
    } else {
        show_error('Категории файлов еще не созданы!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                  Публикация файла                                      ##
############################################################################################
case 'addfile':

    $config['newtitle'] = 'Публикация нового файла';

    $uid = check($_GET['uid']);
    $cid = abs(intval($_POST['cid']));
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
    $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';

    if ($uid == $_SESSION['token']) {
        if (!empty($cid)) {
            if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
                if (utf_strlen($text) >= 10 && utf_strlen($text) < 5000) {
                    if (utf_strlen($author) <= 50) {
                        if (utf_strlen($site) <= 50) {
                            if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                                $downs = DBM::run()->selectFirst('cats', array('cats_id' => $cid));
                                if (!empty($downs)) {
                                    if (empty($downs['closed'])) {
                                        $downtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? LIMIT 1;", array($title));
                                        if (empty($downtitle)) {

                                            DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($cid));
                                            DB::run() -> query("INSERT INTO `downs` (`downs_cats_id`, `downs_title`, `downs_text`, `downs_link`, `downs_user`, `downs_author`, `downs_site`, `downs_screen`, `downs_time`, `downs_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);", array($cid, $title, $text, '', $log, $author, $site, '', SITETIME, 1));

                                            $lastid = DB::run() -> lastInsertId();

                                            notice('Данные успешно добавлены!');
                                            redirect("/admin/load?act=editdown&id=$lastid");
                                        } else {
                                            show_error('Ошибка! Название '.$title.' уже имеется в файлах!');
                                        }
                                    } else {
                                        show_error('Ошибка! В данный раздел запрещена загрузка файлов!');
                                    }
                                } else {
                                    show_error('Ошибка! Выбранный вами раздел не существует!');
                                }
                            } else {
                                show_error('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный ник (логин) автора (не более 50 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий текст описания (от 10 до 5000 символов)!');
                }
            } else {
                show_error('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
            }
        } else {
            show_error('Ошибка! Вы не выбрали категорию для добавления файла!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=newfile&amp;cid='.$cid.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Пересчет счетчиков                                  ##
############################################################################################
case 'restatement':

    $uid = check($_GET['uid']);

    if (is_admin(array(101))) {
        if ($uid == $_SESSION['token']) {
            restatement('load');

            notice('Все данные успешно пересчитаны!');
            redirect("/admin/load");
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                    Добавление разделов                                 ##
############################################################################################
case 'addcats':

    $uid = check($_GET['uid']);
    $name = check($_POST['name']);

    if (is_admin(array(101))) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($name) >= 4 && utf_strlen($name) < 50) {
                $maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(`cats_order`),0)+1 FROM `cats`;");
                DB::run() -> query("INSERT INTO `cats` (`cats_order`, `cats_name`) VALUES (?, ?);", array($maxorder, $name));

                notice('Новый раздел успешно добавлен!');
                redirect("/admin/load");
            } else {
                show_error('Ошибка! Слишком длинное или короткое название раздела!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Добавлять разделы могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                          Подготовка к редактированию разделов                          ##
############################################################################################
case 'editcats':

    if (is_admin(array(101))) {
        $downs = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

        if (!empty($downs)) {
            echo '<b><big>Редактирование</big></b><br /><br />';

            echo '<div class="form">';
            echo '<form action="/admin/load?act=addeditcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Раздел: <br />';
            echo '<input type="text" name="name" maxlength="50" value="'.$downs['cats_name'].'" /><br />';

            $query = DB::run() -> query("SELECT `cats_id`, `cats_name`, `cats_parent` FROM `cats` WHERE `cats_parent`=0 ORDER BY `cats_order` ASC;");
            $section = $query -> fetchAll();

            echo 'Родительский раздел:<br />';
            echo '<select name="parent">';
            echo '<option value="0">Основной раздел</option>';

            foreach ($section as $data) {
                if ($cid != $data['cats_id']) {
                    $selected = ($downs['cats_parent'] == $data['cats_id']) ? ' selected="selected"' : '';
                    echo '<option value="'.$data['cats_id'].'"'.$selected.'>'.$data['cats_name'].'</option>';
                }
            }
            echo '</select><br />';

            echo 'Положение: <br />';
            echo '<input type="text" name="order" maxlength="2" value="'.$downs['cats_order'].'" /><br />';

            echo 'Директория: <br />';
            echo '<input type="text" name="folder" maxlength="50" value="'.$downs['folder'].'" /><br />';

            echo 'При создании директории, загруженные ранее файлы будут автоматически перемещены<br /><br />';

            echo 'Закрыть раздел: ';
            $checked = ($downs['closed'] == 1) ? ' checked="checked"' : '';
            echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

            echo '<input type="submit" value="Изменить" /></form></div><br />';
        } else {
            show_error('Ошибка! Данного раздела не существует!');
        }
    } else {
        show_error('Ошибка! Изменять разделы могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                 Редактирование разделов                                ##
############################################################################################
case 'addeditcats':

    $uid = check($_GET['uid']);
    $name = check($_POST['name']);
    $parent = abs(intval($_POST['parent']));
    $order = abs(intval($_POST['order']));
    $folder = strtolower(check($_POST['folder']));
    $closed = (empty($_POST['closed'])) ? 0 : 1;

    if (is_admin(array(101))) {
        if ($uid == $_SESSION['token']) {
            if (utf_strlen($name) >= 4 && utf_strlen($name) < 50) {
            if (preg_match('/^[\w\-]{0,50}$/', $folder)) {
                if ($cid != $parent) {

                    $catParent = DB::run() -> queryFetch("SELECT `cats_id` FROM `cats` WHERE `cats_parent`=? LIMIT 1;", array($cid));

                    if (empty($catParent) || $parent == 0) {
                        DB::run() -> query("UPDATE `cats` SET `cats_order`=?, `cats_parent`=?, `cats_name`=?, `closed`=? WHERE `cats_id`=?;", array($order, $parent, $name, $closed, $cid));

                        $cat = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

                        $query = DB::run() -> query("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_cats_id`=?;", array($cid));
                        $downs = $query -> fetchAll();

                        // Перенос файлов
                        if (empty($folder) && ! empty($cat['folder'])) {

                            foreach ($downs as $down) {

                                if (! empty($down['downs_link']) && file_exists(BASEDIR.'/upload/files/'.$cat['folder'].'/'.$down['downs_link'])) {
                                    rename(BASEDIR.'/upload/files/'.$cat['folder'].'/'.$down['downs_link'], BASEDIR.'/upload/files/'.$down['downs_link']);
                                }

                                if (! empty($down['downs_screen']) && file_exists(BASEDIR.'/upload/screen/'.$cat['folder'].'/'.$down['downs_screen'])) {

                                    rename(BASEDIR.'/upload/screen/'.$cat['folder'].'/'.$down['downs_screen'], BASEDIR.'/upload/screen/'.$down['downs_screen']);
                                    unlink_image('upload/screen/'.$cat['folder'], $down['downs_screen']);
                                }
                            }

                            removeDir(BASEDIR.'/upload/files/'.$cat['folder']);
                            removeDir(BASEDIR.'/upload/screen/'.$cat['folder']);
                            $renameDir = true;
                        }

                        if (! empty($folder) && ! file_exists(BASEDIR.'/upload/files/'.$folder) && $cat['folder'] != $folder) {

                            if (! empty($cat['folder']) && file_exists(BASEDIR.'/upload/files/'.$cat['folder'])){
                                rename(BASEDIR.'/upload/files/'.$cat['folder'], BASEDIR.'/upload/files/'.$folder);
                                rename(BASEDIR.'/upload/screen/'.$cat['folder'], BASEDIR.'/upload/screen/'.$folder);
                            } else {
                                $old = umask(0);
                                mkdir(BASEDIR.'/upload/files/'.$folder, 0777, true);
                                mkdir(BASEDIR.'/upload/screen/'.$folder, 0777, true);
                                umask($old);

                                foreach ($downs as $down) {

                                    if (! empty($down['downs_link']) && file_exists(BASEDIR.'/upload/files/'.$down['downs_link'])) {

                                        rename(BASEDIR.'/upload/files/'.$down['downs_link'], BASEDIR.'/upload/files/'.$folder.'/'.$down['downs_link']);
                                    }

                                    if (! empty($down['downs_screen']) && file_exists(BASEDIR.'/upload/screen/'.$down['downs_screen'])) {

                                        rename(BASEDIR.'/upload/screen/'.$down['downs_screen'], BASEDIR.'/upload/screen/'.$folder.'/'.$down['downs_screen']);
                                        unlink_image('upload/screen/', $down['downs_screen']);
                                    }
                                }

                            }
                            $renameDir = true;
                        }

                        if (!empty($renameDir)) {
                            DB::run() -> query("UPDATE `cats` SET `folder`=? WHERE `cats_id`=?;", array($folder, $cid));
                            notice('Директория изменена!');
                        }

                        notice('Раздел успешно отредактирован!');
                        redirect("/admin/load");
                    } else {
                        show_error('Ошибка! Данный раздел имеет подкатегории!');
                    }
                } else {
                    show_error('Ошибка! Недопустимый выбор родительского раздела!');
                }
            } else {
                show_error('Ошибка! Название дирекории должно состоять из лат. (до 50) символов!');
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

    echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/admin/load?act=editcats&amp;cid='.$cid.'">Вернуться</a><br />';
    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Категории</a><br />';
break;

############################################################################################
##                                  Подтвержение удаления                                 ##
############################################################################################
case 'prodelcats':

    if (is_admin(array(101))) {
        $downs = DB::run() -> queryFetch("SELECT `c1`.*, count(`c2`.`cats_id`) AS `subcnt` FROM `cats` `c1` LEFT JOIN `cats` `c2` ON `c2`.`cats_parent` = `c1`.`cats_id` WHERE `c1`.`cats_id`=? GROUP BY `cats_id` LIMIT 1;", array($cid));

        if (!empty($downs['cats_id'])) {
            if (empty($downs['subcnt'])) {
                echo 'Вы уверены что хотите удалить раздел <b>'.$downs['cats_name'].'</b> в загрузках?<br />';
                echo '<img src="/images/img/error.gif" alt="image" /> <b><a href="/admin/load?act=delcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
            } else {
                show_error('Ошибка! Данный раздел имеет подкатегории!');
            }
        } else {
            show_error('Ошибка! Данного раздела не существует!');
        }
    } else {
        show_error('Ошибка! Удалять разделы могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление раздела                                    ##
############################################################################################
case 'delcats':

    $uid = check($_GET['uid']);

    if (is_admin(array(101)) && $log == $config['nickname']) {
        if ($uid == $_SESSION['token']) {
            $downs = DB::run() -> queryFetch("SELECT `c1`.*, count(`c2`.`cats_id`) AS `subcnt` FROM `cats` `c1` LEFT JOIN `cats` `c2` ON `c2`.`cats_parent` = `c1`.`cats_id` WHERE `c1`.`cats_id`=? GROUP BY `cats_id` LIMIT 1;", array($cid));

            if (!empty($downs['cats_id'])) {
                if (empty($downs['subcnt'])) {
                    if (is_writeable(BASEDIR.'/upload/files')) {
                        $folder = $downs['folder'] ? $downs['folder'].'/' : '';

                        $querydel = DB::run() -> query("SELECT `downs_link`, `downs_screen` FROM `downs` WHERE `downs_cats_id`=?;", array($cid));
                        $arr_script = $querydel -> fetchAll();

                        DB::run() -> query("DELETE FROM `commload` WHERE `commload_cats`=?;", array($cid));
                        DB::run() -> query("DELETE FROM `downs` WHERE `downs_cats_id`=?;", array($cid));
                        DB::run() -> query("DELETE FROM `cats` WHERE `cats_id`=?;", array($cid));

                        foreach ($arr_script as $delfile) {
                            if (!empty($delfile['downs_link']) && file_exists(BASEDIR.'/upload/files/'.$folder.$delfile['downs_link'])) {
                                unlink(BASEDIR.'/upload/files/'.$folder.$delfile['downs_link']);
                            }

                            unlink_image('upload/screen/'.$folder, $delfile['downs_screen']);
                        }

                        if (! empty($folder)) {
                            removeDir(BASEDIR.'/upload/files/'.$folder);
                            removeDir(BASEDIR.'/upload/screen/'.$folder);
                        }

                        notice('Раздел успешно удален!');
                        redirect("/admin/load");
                    } else {
                        show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
                    }
                } else {
                    show_error('Ошибка! Данный раздел имеет подкатегории!');
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

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load">Вернуться</a><br />';
break;

############################################################################################
##                                       Просмотр файлов                                  ##
############################################################################################
case 'down':

    $cats = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cid));

    echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> <a href="/admin/load">Категории</a>';

    if (!empty($cats['cats_parent'])) {
        $podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($cats['cats_parent']));
        echo ' / <a href="/admin/load?act=down&amp;cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a>';
    }

    if (empty($cats['closed'])) {
        echo ' / <a href="/admin/load?act=newfile&amp;cid='.$cid.'">Загрузить файл</a>';
    }

    echo '<br /><br />';
    if ($cats > 0) {
        $config['newtitle'] = $cats['cats_name'];

        echo '<img src="/images/img/open_dir.gif" alt="image" /> <b>'.$cats['cats_name'].'</b> (Файлов: '.$cats['cats_count'].')';
        echo ' (<a href="/load/down?cid='.$cid.'&amp;start='.$start.'">Обзор</a>)';
        echo '<hr />';

        $querysub = DB::run() -> query("SELECT * FROM `cats` WHERE `cats_parent`=?;", array($cid));
        $sub = $querysub -> fetchAll();

        if (count($sub) > 0 && $start == 0) {
            foreach($sub as $subdata) {
                echo '<div class="b"><img src="/images/img/dir.gif" alt="image" /> ';
                echo '<b><a href="/admin/load?act=down&amp;cid='.$subdata['cats_id'].'">'.$subdata['cats_name'].'</a></b> ('.$subdata['cats_count'].')</div>';
            }
            echo '<hr />';
        }

        $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=?;", array($cid, 1));

        if ($total > 0) {
            if ($start >= $total) {
                $start = 0;
            }

            $querydown = DB::run() -> query("SELECT * FROM `downs` WHERE `downs_cats_id`=? AND `downs_active`=? ORDER BY `downs_time` DESC LIMIT ".$start.", ".$config['downlist'].";", array($cid, 1));

            $folder = $cats['folder'] ? $cats['folder'].'/' : '';

            $is_admin = (is_admin(array(101)) && $log == $config['nickname']);

            if ($is_admin) {
                echo '<form action="/admin/load?act=deldown&amp;cid='.$cid.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
            }

             while ($data = $querydown -> fetch()) {
                $filesize = (!empty($data['downs_link'])) ? read_file(BASEDIR.'/upload/files/'.$folder.$data['downs_link']) : 0;

                echo '<div class="b">';
                echo '<img src="/images/img/zip.gif" alt="image" /> ';
                echo '<b><a href="/load/down?act=view&amp;id='.$data['downs_id'].'">'.$data['downs_title'].'</a></b> ('.$filesize.')<br />';

                if ($is_admin) {
                    echo '<input type="checkbox" name="del[]" value="'.$data['downs_id'].'" /> ';
                }

                echo '<a href="/admin/load?act=editdown&amp;cid='.$cid.'&amp;id='.$data['downs_id'].'&amp;start='.$start.'">Редактировать</a> / ';
                echo '<a href="/admin/load?act=movedown&amp;cid='.$cid.'&amp;id='.$data['downs_id'].'&amp;start='.$start.'">Переместить</a></div>';

                echo '<div>';

                echo 'Скачиваний: '.$data['downs_load'].'<br />';

                $raiting = (!empty($data['downs_rated'])) ? round($data['downs_raiting'] / $data['downs_rated'], 1) : 0;

                echo 'Рейтинг: <b>'.$raiting.'</b> (Голосов: '.$data['downs_rated'].')<br />';
                echo '<a href="/load/down?act=comments&amp;id='.$data['downs_id'].'">Комментарии</a> ('.$data['downs_comments'].')</div>';
            }

            if ($is_admin) {
                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';
            }

            page_strnavigation('/admin/load?act=down&amp;cid='.$cid.'&amp;', $config['downlist'], $start, $total);
        } else {
            if (empty($cats['closed'])) {
                show_error('В данном разделе еще нет файлов!');
            }
        }
        if (!empty($cats['closed'])) {
            show_error('В данном разделе запрещена загрузка файлов!');
        }

    } else {
        show_error('Ошибка! Данного раздела не существует!');
    }
    if (empty($cats['closed'])) {
        echo '<img src="/images/img/open.gif" alt="image" /> <a href="/admin/load?act=newfile&amp;cid='.$cid.'">Добавить</a><br />';
    }
    echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/admin/load">Категории</a><br />';
break;

############################################################################################
##                            Подготовка к редактированию файла                           ##
############################################################################################
case 'editdown':

    $config['newtitle'] = 'Редактирование файла';

    $new = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (! empty($new)) {
        echo '<a href="#down"><img src="/images/img/downs.gif" alt="Вниз" /></a> <a href="/admin/load">Категории</a> / ';

        if (! empty($new['cats_parent'])) {
            $podcats = DB::run() -> queryFetch("SELECT `cats_id`, `cats_name` FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($new['cats_parent']));
            echo '<a href="/admin/load?act=down&amp;cid='.$podcats['cats_id'].'">'.$podcats['cats_name'].'</a> / ';
        }

        echo '<a href="/load/down?act=view&amp;id='.$id.'">Обзор файла</a><br /><br />';

        if (empty($new['downs_link'])) {
            echo '<b><big>Загрузка файла</big></b><br /><br />';

            echo '<div class="form">';
            echo '<form action="/admin/load?act=loadfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
            echo 'Прикрепить файл* ('.$config['allowextload'].'):<br /><input type="file" name="loadfile" /><br />';
            echo '<input value="Загрузить" type="submit" /></form></div><br />';

            echo '<div class="form">';
            echo '<form action="/admin/load?act=copyfile&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
            echo 'Импорт файла*:<br /><input type="text" name="loadfile" value="http://" /><br />';
            echo '<input value="Импортировать" type="submit" /></form></div><br />';

        } else {
            $folder = $new['folder'] ? $new['folder'].'/' : '';

            echo '<img src="/images/img/download.gif" alt="image" /> <b><a href="/upload/files/'.$folder.$new['downs_link'].'">'.$new['downs_link'].'</a></b> ('.read_file(BASEDIR.'/upload/files/'.$folder.$new['downs_link']).') (<a href="/admin/load?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br />';

            $ext = getExtension($new['downs_link']);
            if (! in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

                if (empty($new['downs_screen'])) {
                    echo '<br /><b><big>Загрузка скриншота</big></b><br /><br />';
                    echo '<div class="form">';
                    echo '<form action="/admin/load?act=loadscreen&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
                    echo 'Прикрепить скрин (jpg,jpeg,gif,png):<br /><input type="file" name="screen" /><br />';
                    echo '<input value="Загрузить" type="submit" /></form></div><br />';
                } else {
                    echo '<img src="/images/img/gallery.gif" alt="image" /> <b><a href="/upload/screen/'.$folder.$new['downs_screen'].'">'.$new['downs_screen'].'</a></b> ('.read_file(BASEDIR.'/upload/screen/'.$folder.$new['downs_screen']).') (<a href="/admin/load?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br /><br />';
                    echo resize_image('upload/screen/'.$folder, $new['downs_screen'], $config['previewsize']).'<br />';
                }
            }
        }

        echo '<br />';

        echo '<b><big>Редактирование</big></b><br /><br />';
        echo '<div class="form">';
        echo '<form action="/admin/load?act=changedown&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

        echo 'Название*:<br />';
        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['downs_title'].'" /><br />';
        echo 'Описание*:<br />';
        echo '<textarea cols="25" rows="5" name="text">'.$new['downs_text'].'</textarea><br />';
        echo 'Автор файла:<br />';
        echo '<input type="text" name="author" maxlength="50" value="'.$new['downs_author'].'" /><br />';
        echo 'Сайт автора:<br />';
        echo '<input type="text" name="site" maxlength="50" value="'.$new['downs_site'].'" /><br />';
        echo 'Имя файла*:<br />';
        echo '<input type="text" name="loadfile" maxlength="50" value="'.$new['downs_link'].'" /><br />';

        echo '<input value="Изменить" type="submit" /></form></div><br />';
    } else {
        show_error('Данного файла не существует!');
    }

    echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/admin/load">Категории</a><br />';
break;

############################################################################################
##                                  Редактирование файла                                  ##
############################################################################################
case 'changedown':

    $uid = check($_GET['uid']);
    $title = check($_POST['title']);
    $text = check($_POST['text']);
    $author = (!empty($_POST['author'])) ? check($_POST['author']) : '';
    $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';
    $loadfile = check(strtolower($_POST['loadfile']));

    if ($uid == $_SESSION['token']) {
        if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
            if (utf_strlen($text) >= 10 && utf_strlen($text) <= 5000) {
                if (utf_strlen($author) <= 50) {
                    if (utf_strlen($site) <= 50) {
                        if (empty($site) || preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                            if (strlen($loadfile) <= 50) {
                                if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $loadfile)) {

                                    $new = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

                                    if (! empty($new)) {

                                        $folder = $new['folder'] ? $new['folder'].'/' : '';

                                        $downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? AND `downs_id`<>? LIMIT 1;", array($loadfile, $id));
                                        if (empty($downlink)) {

                                            $downtitle = DB::run() -> querySingle("SELECT `downs_title` FROM `downs` WHERE `downs_title`=? AND `downs_id`<>? LIMIT 1;", array($title, $id));
                                            if (empty($downtitle)) {

                                                if (!empty($loadfile) && $loadfile != $new['downs_link'] && file_exists(BASEDIR.'/upload/files/'.$folder.$new['downs_link'])) {

                                                    $oldext = getExtension($new['downs_link']);
                                                    $newext = getExtension($loadfile);

                                                    if ($oldext == $newext) {

                                                        $screen = $new['downs_screen'];
                                                        rename(BASEDIR.'/upload/files/'.$folder.$new['downs_link'], BASEDIR.'/upload/files/'.$folder.$loadfile);

                                                        if (!empty($new['downs_screen']) && file_exists(BASEDIR.'/upload/screen/'.$folder.$new['downs_screen'])) {

                                                            $screen = $loadfile.'.'.getExtension($new['downs_screen']);
                                                            rename(BASEDIR.'/upload/screen/'.$folder.$new['downs_screen'], BASEDIR.'/upload/screen/'.$folder.$screen);
                                                            unlink_image('upload/screen/'.$folder, $new['downs_screen']);
                                                        }
                                                        DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array($loadfile, $screen, $id));
                                                    }
                                                }

                                                DB::run() -> query("UPDATE `downs` SET `downs_title`=?, `downs_text`=?, `downs_author`=?, `downs_site`=?, `downs_time`=? WHERE `downs_id`=?;", array($title, $text, $author, $site, $new['downs_time'], $id));

                                                notice('Данные успешно изменены!');
                                                redirect("/admin/load?act=editdown&id=$id");

                                            } else {
                                                show_error('Ошибка! Название '.$title.' уже имеется в общих файлах!');
                                            }
                                        } else {
                                            show_error('Ошибка! Файл '.$loadfile.' уже присутствует в общих файлах!');
                                        }
                                    } else {
                                        show_error('Данного файла не существует!');
                                    }
                                } else {
                                    show_error('Ошибка! В названии файла присутствуют недопустимые расширения!');
                                }
                            } else {
                                show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
                            }
                        } else {
                            show_error('Ошибка! Недопустимый адрес сайта, необходим формат http://site.domen!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный адрес сайта (не более 50 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный ник (логин) автора (до 50 символов)!');
                }
            } else {
                show_error('Ошибка! Слишком длинный или короткий текст описания (от 10 до 5000 символов)!');
            }
        } else {
            show_error('Ошибка! Слишком длинное или короткое название (от 5 до 50 символов)!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Импорт файла                                         ##
############################################################################################
case 'copyfile':
    show_title('Импорт файла');

    $loadfile = check($_POST['loadfile']);

    $down = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (! empty($down)) {
        if (empty($down['downs_link'])) {
            if (!empty($loadfile)) {
                $filename = strtolower(basename($loadfile));
                $folder = $down['folder'] ? $down['folder'].'/' : '';

                if (strlen($filename) <= 50) {
                    if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {

                        $ext = getExtension($filename);

                        if (in_array($ext, explode(',', $config['allowextload']), true)) {

                            $downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? LIMIT 1;", array($filename));
                            if (empty($downlink)) {
                                if (@copy($loadfile, BASEDIR.'/upload/files/'.$folder.$filename)) {
                                    @chmod(BASEDIR.'/upload/files/'.$folder.$filename, 0666);

                                    copyright_archive(BASEDIR.'/upload/files/'.$folder.$filename);

                                    DB::run() -> query("UPDATE `downs` SET `downs_link`=? WHERE `downs_id`=?;", array($filename, $id));

                                    notice('Файл успешно импортирован!');
                                    redirect("/admin/load?act=editdown&id=$id");
                                } else {
                                    show_error('Ошибка! Не удалось импортировать файл!');
                                }
                            } else {
                                show_error('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
                            }
                        } else {
                            show_error('Ошибка! Недопустимое расширение файла!');
                        }
                    } else {
                        show_error('Ошибка! В названии файла присутствуют недопустимые символы!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
                }
            } else {
                show_error('Ошибка! Не указан путь для импорта файла');
            }
        } else {
            show_error('Ошибка! Файл уже загружен!');
        }
    } else {
        show_error('Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Загрузка файла                                       ##
############################################################################################
case 'loadfile':
    show_title('Загрузка файла');

    $down = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($down)) {
        if (empty($down['downs_link'])) {
            if (is_uploaded_file($_FILES['loadfile']['tmp_name'])) {
                $filename = check(strtolower($_FILES['loadfile']['name']));

                $folder = $down['folder'] ? $down['folder'].'/' : '';

                if (strlen($filename) <= 50) {
                    if (preg_match('|^[a-z0-9_\.\-]+$|i', $filename)) {

                        $ext = getExtension($filename);

                        if (in_array($ext, explode(',', $config['allowextload']), true)) {
                            if ($_FILES['loadfile']['size'] > 0 && $_FILES['loadfile']['size'] <= $config['fileupload']) {
                                $downlink = DB::run() -> querySingle("SELECT `downs_link` FROM `downs` WHERE `downs_link`=? LIMIT 1;", array($filename));
                                if (empty($downlink)) {

                                    move_uploaded_file($_FILES['loadfile']['tmp_name'], BASEDIR.'/upload/files/'.$folder.$filename);
                                    @chmod(BASEDIR.'/upload/files/'.$folder.$filename, 0666);

                                    copyright_archive(BASEDIR.'/upload/files/'.$folder.$filename);

                                    DB::run() -> query("UPDATE `downs` SET `downs_link`=? WHERE `downs_id`=?;", array($filename, $id));

                                    notice('Файл успешно загружен!');
                                    redirect("/admin/load?act=editdown&id=$id");
                                } else {
                                    show_error('Ошибка! Файл '.$filename.' уже имеется в общих файлах!');
                                }
                            } else {
                                show_error('Ошибка! Максимальный размер загружаемого файла '.formatsize($config['fileupload']).'!');
                            }
                        } else {
                            show_error('Ошибка! Недопустимое расширение файла!');
                        }
                    } else {
                        show_error('Ошибка! В названии файла присутствуют недопустимые символы!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное имя файла (не более 50 символов)!');
                }
            } else {
                show_error('Ошибка! Не удалось загрузить файл!');
            }
        } else {
            show_error('Ошибка! Файл уже загружен!');
        }
    } else {
        show_error('Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Загрузка скриншота                                   ##
############################################################################################
case 'loadscreen':
    show_title('Загрузка скриншота');

    $down = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($down)) {
        if (empty($down['downs_screen'])) {
            if (is_uploaded_file($_FILES['screen']['tmp_name'])) {

                // ------------------------------------------------------//
                $handle = upload_image($_FILES['screen'], $config['screenupload'], $config['screenupsize'], $down['downs_link']);
                if ($handle) {
                    $folder = $down['folder'] ? $down['folder'].'/' : '';

                    $handle -> process(BASEDIR.'/upload/screen/'.$folder);
                    if ($handle -> processed) {

                        DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array($handle -> file_dst_name, $id));

                        $handle -> clean();

                        $_SESSION['note'] = 'Скриншот успешно загружен!';
                        redirect("/admin/load?act=editdown&id=$id");
                    } else {
                        show_error($handle -> error);
                    }
                } else {
                    show_error('Ошибка! Не удалось загрузить скриншот!');
                }
            } else {
                show_error('Ошибка! Вы не загрузили скриншот!');
            }
        } else {
            show_error('Ошибка! Скриншот уже загружен!');
        }
    } else {
        show_error('Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                   Удаление файла                                       ##
############################################################################################
case 'delfile':

    $link = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($link)) {
        $folder = $link['folder'] ? $link['folder'].'/' : '';

        if (!empty($link['downs_link']) && file_exists(BASEDIR.'/upload/files/'.$folder.$link['downs_link'])) {
            unlink(BASEDIR.'/upload/files/'.$folder.$link['downs_link']);
        }

        unlink_image('upload/screen/'.$folder, $link['downs_screen']);

        DB::run() -> query("UPDATE `downs` SET `downs_link`=?, `downs_screen`=? WHERE `downs_id`=?;", array('', '', $id));

        notice('Файл успешно удален!');
        redirect("/admin/load?act=editdown&id=$id");
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление скриншота                                  ##
############################################################################################
case 'delscreen':

    $screen = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($screen)) {
        $folder = $screen['folder'] ? $screen['folder'].'/' : '';

        unlink_image('upload/screen/'.$folder, $screen['downs_screen']);

        DB::run() -> query("UPDATE `downs` SET `downs_screen`=? WHERE `downs_id`=?;", array('', $id));

        notice('Скриншот успешно удален!');
        redirect("/admin/load?act=editdown&id=$id");
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=editdown&amp;id='.$id.'">Вернуться</a><br />';
break;

############################################################################################
##                               Подготовка к перемещению файла                           ##
############################################################################################
case 'movedown':

    $downs = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));

    if (!empty($downs)) {
        $folder = $downs['folder'] ? $downs['folder'].'/' : '';

        echo '<img src="/images/img/download.gif" alt="image" /> <b>'.$downs['downs_title'].'</b> ('.read_file(BASEDIR.'/upload/files/'.$folder.$downs['downs_link']).')<br /><br />';

        $querycats = DB::run() -> query("SELECT * FROM `cats` ORDER BY `cats_order` ASC;");
        $cats = $querycats -> fetchAll();

        if (count($cats) > 0) {
            $output = array();
            foreach ($cats as $row) {
                $i = $row['cats_id'];
                $p = $row['cats_parent'];
                $output[$p][$i] = $row;
            }

            echo '<div class="form"><form action="/admin/load?act=addmovedown&amp;cid='.$downs['downs_cats_id'].'&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Выберите раздел для перемещения:<br />';
            echo '<select name="section">';
            echo '<option value="0">Список разделов</option>';

            foreach ($output[0] as $key => $data) {
                if ($downs['downs_cats_id'] != $data['cats_id']) {
                    $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
                    echo '<option value="'.$data['cats_id'].'"'.$disabled.'>'.$data['cats_name'].'</option>';
                }

                if (isset($output[$key])) {
                    foreach($output[$key] as $datasub) {
                        if ($downs['downs_cats_id'] != $datasub['cats_id']) {
                            $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                            echo '<option value="'.$datasub['cats_id'].'"'.$disabled.'>– '.$datasub['cats_name'].'</option>';
                        }
                    }
                }
            }

            echo '</select>';

            echo '<input type="submit" value="Переместить" /></form></div><br />';
        } else {
            show_error('Разделы загрузок еще не созданы!');
        }
    } else {
        show_error('Ошибка! Данного файла не существует!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=down&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
break;

############################################################################################
##                                    Перемещение файла                                   ##
############################################################################################
case 'addmovedown':

    $uid = check($_GET['uid']);
    $section = abs(intval($_POST['section']));

    if ($uid == $_SESSION['token']) {

        $catsTo = DB::run() -> queryFetch("SELECT * FROM `cats` WHERE `cats_id`=? LIMIT 1;", array($section));

        if (! empty($catsTo)) {

            $downFrom = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id`=? LIMIT 1;", array($id));
            if (!empty($downFrom)) {

                $folderFrom = $downFrom['folder'] ? $downFrom['folder'].'/' : '';
                $folderTo = $catsTo['folder'] ? $catsTo['folder'].'/' : '';

                rename(BASEDIR.'/upload/files/'.$folderFrom.$downFrom['downs_link'], BASEDIR.'/upload/files/'.$folderTo.$downFrom['downs_link']);

                DB::run() -> query("UPDATE `downs` SET `downs_cats_id`=? WHERE `downs_id`=?;", array($section, $id));
                DB::run() -> query("UPDATE `commload` SET `commload_cats`=? WHERE `commload_down`=?;", array($section, $id));
                // Обновление счетчиков
                DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`+1 WHERE `cats_id`=?", array($section));
                DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`-1 WHERE `cats_id`=?", array($cid));

                notice('Файл успешно перемещен!');
                redirect("/admin/load?act=down&cid=$section");
            } else {
                show_error('Ошибка! Файла для перемещения не существует!');
            }
        } else {
            show_error('Ошибка! Выбранного раздела не существует!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=movedown&amp;id='.$id.'">Вернуться</a><br />';
    echo '<img src="/images/img/reload.gif" alt="image" /> <a href="/admin/load?act=down&amp;cid='.$cid.'">К разделам</a><br />';
break;

############################################################################################
##                                   Удаление файлов                                      ##
############################################################################################
case 'deldown':

    $uid = check($_GET['uid']);
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if (is_admin(array(101)) && $log == $config['nickname']) {
        if ($uid == $_SESSION['token']) {
            if ($del > 0) {
                $del = implode(',', $del);

                if (is_writeable(BASEDIR.'/upload/files')) {

                    $querydel = DB::run() -> query("SELECT `downs`.*, `cats`.* FROM `downs` LEFT JOIN `cats` ON `downs`.`downs_cats_id`=`cats`.`cats_id` WHERE `downs_id` IN (".$del.");");
                    $arr_script = $querydel -> fetchAll();

                    DB::run() -> query("DELETE FROM `commload` WHERE `commload_down` IN (".$del.");");
                    $deldowns = DB::run() -> exec("DELETE FROM `downs` WHERE `downs_id` IN (".$del.");");
                    // Обновление счетчиков
                    DB::run() -> query("UPDATE `cats` SET `cats_count`=`cats_count`-? WHERE `cats_id`=?", array($deldowns, $cid));

                    foreach ($arr_script as $delfile) {
                        $folder = $delfile['folder'] ? $delfile['folder'].'/' : '';
                        if (!empty($delfile['downs_link']) && file_exists(BASEDIR.'/upload/files/'.$folder.$delfile['downs_link'])) {
                            unlink(BASEDIR.'/upload/files/'.$folder.$delfile['downs_link']);
                        }

                        unlink_image('upload/screen/'.$folder, $delfile['downs_screen']);
                    }

                    notice('Выбранные файлы успешно удалены!');
                    //redirect("/admin/load?act=down&cid=$cid&start=$start");
                } else {
                    show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с файлами!');
                }
            } else {
                show_error('Ошибка! Отсутствуют выбранные файлы!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять файлы могут только суперадмины!');
    }

    echo '<img src="/images/img/back.gif" alt="image" /> <a href="/admin/load?act=down&amp;cid='.$cid.'&amp;start='.$start.'">Вернуться</a><br />';
break;

endswitch;

echo '<img src="/images/img/panel.gif" alt="image" /> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
