<?php







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

                        deleteFile(UPLOADS . '/screen/' . $delfile['screen']);
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

