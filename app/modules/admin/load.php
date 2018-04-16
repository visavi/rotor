<?php









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

