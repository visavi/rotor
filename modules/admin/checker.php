<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}

if (is_admin(array(101))) {
    show_title('Сканирование сайта');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            if (file_exists(DATADIR."/temp/checker.dat")) {
                echo 'Последнее сканирование: <b>'.date_fixed(filemtime(DATADIR."/temp/checker.dat")).'</b><br /><br />';

                $arr = scan_check('../');
                $arr['files'] = str_replace('..//', '', $arr['files']);

                $arrnewskan = unserialize(file_get_contents(DATADIR."/temp/checker.dat"));

                $arr1 = array_diff($arr['files'], $arrnewskan);
                $arr2 = array_diff($arrnewskan, $arr['files']);

                $count_arr1 = count($arr1);
                $count_arr2 = count($arr2);

                if (($count_arr1 + $count_arr2) > 0) {
                    echo '<b><span style="color:#ff0000">Новые файлы и новые параметры файлов:</span></b><br /><br />';
                    if ($count_arr1 > 0) {
                        foreach($arr1 as $val) {
                            echo check($val).'<br />';
                        }
                        echo '<br />';
                    } else {
                        show_error('Нет новых изменений!');
                    }

                    echo '<b><span style="color:#ff0000">Удаленные файлы и старые параметры файлов:</span></b><br /><br />';
                    if ($count_arr2 > 0) {
                        foreach($arr2 as $val) {
                            echo check($val).'<br />';
                        }
                        echo '<br />';
                    } else {
                        show_error('Нет старых изменений!');
                    }

                    echo 'Всего папок: <b>'.$arr['totaldirs'].'</b><br />';
                    echo 'Всего файлов: <b>'.$arr['totalfiles'].'</b><br /><br />';
                } else {
                    show_error('Изменений файлов со времени последнего сканирования не обнаружено!');
                }
            } else {
                show_error('Необходимо провести начальное сканирование!');
            }

            echo 'Сканирование системы позволяет узнать какие файлы или папки менялись в течении определенного времени<br />';
            echo 'Внимание сервис не учитывает некоторые расширения файлов: '.$config['nocheck'].'<br /><br />';

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/checker?act=skan&amp;uid='.$_SESSION['token'].'">Сканировать</a><br />';
        break;

        ############################################################################################
        ##                                      Сканирование                                      ##
        ############################################################################################
        case 'skan':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_writeable(DATADIR."/temp")) {
                    $arr = scan_check('../');
                    $arr['files'] = str_replace('..//', '', $arr['files']);

                    file_put_contents(DATADIR."/temp/checker.dat", serialize($arr['files']), LOCK_EX);

                    $_SESSION['note'] = 'Сайт успешно отсканирован!';
                    redirect("/admin/checker");
                } else {
                    show_error('Ошибка! Директория temp недоступна для записи!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/checker">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect('/');
}

App::view($config['themes'].'/foot');
