<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin([101])) {
    //show_title('Сканирование сайта');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            if (file_exists(STORAGE."/temp/checker.dat")) {
                echo 'Последнее сканирование: <b>'.dateFixed(filemtime(STORAGE."/temp/checker.dat")).'</b><br><br>';

                $arr = scan_check('../');
                $arr['files'] = str_replace('..//', '', $arr['files']);

                $arrnewskan = unserialize(file_get_contents(STORAGE."/temp/checker.dat"));

                $arr1 = array_diff($arr['files'], $arrnewskan);
                $arr2 = array_diff($arrnewskan, $arr['files']);

                $count_arr1 = count($arr1);
                $count_arr2 = count($arr2);

                if (($count_arr1 + $count_arr2) > 0) {
                    echo '<b><span style="color:#ff0000">Новые файлы и новые параметры файлов:</span></b><br><br>';
                    if ($count_arr1 > 0) {
                        foreach($arr1 as $val) {
                            echo '<i class="fa fa-plus-circle text-success"></i> '.check($val).'<br>';
                        }
                        echo '<br>';
                    } else {
                        showError('Нет новых изменений!');
                    }

                    echo '<b><span style="color:#ff0000">Удаленные файлы и старые параметры файлов:</span></b><br><br>';
                    if ($count_arr2 > 0) {
                        foreach($arr2 as $val) {
                            echo '<i class="fa fa-minus-circle text-danger"></i> '.check($val).'<br>';
                        }
                        echo '<br>';
                    } else {
                        showError('Нет старых изменений!');
                    }

                    echo 'Всего папок: <b>'.$arr['totaldirs'].'</b><br>';
                    echo 'Всего файлов: <b>'.$arr['totalfiles'].'</b><br><br>';
                } else {
                    showError('Изменений файлов со времени последнего сканирования не обнаружено!');
                }
            } else {
                showError('Необходимо провести начальное сканирование!');
            }

            echo 'Сканирование системы позволяет узнать какие файлы или папки менялись в течение определенного времени<br>';
            echo 'Внимание сервис не учитывает некоторые расширения файлов: '.setting('nocheck').'<br><br>';

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/checker?act=skan&amp;uid='.$_SESSION['token'].'">Сканировать</a><br>';
        break;

        ############################################################################################
        ##                                      Сканирование                                      ##
        ############################################################################################
        case 'skan':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_writeable(STORAGE."/temp")) {
                    $arr = scan_check('../');
                    $arr['files'] = str_replace('..//', '', $arr['files']);

                    file_put_contents(STORAGE."/temp/checker.dat", serialize($arr['files']), LOCK_EX);

                    setFlash('success', 'Сайт успешно отсканирован!');
                    redirect("/admin/checker");
                } else {
                    showError('Ошибка! Директория temp недоступна для записи!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/checker">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
