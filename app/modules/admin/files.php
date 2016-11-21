<?php
App::view($config['themes'].'/index');

$act = check(Request::input('act', 'index'));
$file = check(Request::input('file'));
$path = check(Request::input('path'));

if (
    !file_exists(APP.'/views/'.$path) ||
    !is_dir(APP.'/views/'.$path) ||
    strpos($path, '.') !== false
) {
    $path = '';
}

if (is_admin([101]) && $log == $config['nickname']) {
    show_title('Редактирование страниц');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $files = preg_grep('/^([^.])/', scandir(APP.'/views/'.$path.$file));

            usort($files, function($a, $b) {
                if (is_file(APP.'/views/'.$a) && is_file(APP.'/views/'.$b)) {
                    return 0;
                }
                return (is_dir(APP.'/views/'.$a)) ? -1 : 1;
            });

            $total = count($files);

            if ($total > 0) {

                show_title($path ? $path : 'Редактирование страниц');

                echo '<ul class="list-group">';
                foreach ($files as $file) {

                    if (is_dir(APP.'/views/'.$path.$file)) {
                        echo '<li class="list-group-item"><i class="fa fa-folder-o"></i> <b><a href="/admin/files?path='.$path.$file.'/">'.$file.'</a></b><br />';
                        echo 'Объектов: '.count(array_diff(scandir(APP.'/views/'.$path.$file), ['.', '..'])).'</li>';
                    } else {

                        $size = formatsize(filesize(APP.'/views/'.$path.$file));
                        $strok = count(file(APP.'/views/'.$path.$file));

                        echo '<li class="list-group-item"><div class="pull-right">';
                        echo '<a href="/admin/files?act=del&amp;path='.$path.'&amp;file='.$file.'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить этот файл\')"><i class="fa fa-remove"></i></a></div>';

                        echo '<i class="fa fa-file-o"></i> ';
                        echo '<b><a href="/admin/files?act=edit&amp;path='.$path.'&amp;file='.basename($file, '.blade.php').'">'.$file.'</a></b> (' . $size . ')<br />';
                        echo 'Строк: ' . $strok . ' / ';
                        echo 'Изменен: ' . date_fixed(filemtime(APP.'/views/'.$path.$file)) . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                show_error('Файлов нет!');
            }

            if ($path) {
                echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
            }
            echo'<i class="fa fa-file-o"></i> <a href="/admin/files?act=new">Создать</a><br />';
        break;

        ############################################################################################
        ##                             Подготовка к редактированию                                ##
        ############################################################################################
        case 'edit':

            if (preg_match('|^[a-z0-9_\-/]+$|i', $path) && preg_match('|^[a-z0-9_\-/]+$|i', $file)) {
                if (file_exists(APP.'/views/'.$path.$file.'.blade.php')) {
                    if (is_writeable(APP.'/views/'.$path.$file.'.blade.php')) {

                        if (Request::isMethod('post')) {
                            $token = check(Request::input('token'));
                            $msg = Request::input('msg');

                            if ($token == $_SESSION['token']) {

                                file_put_contents(APP.'/views/'.$path.$file.'.blade.php', $msg);

                                notice('Файл успешно сохранен!');
                                redirect ("/admin/files?act=edit&path=$path&file=$file");

                            } else {
                                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                            }
                        }

                        $mainfile = file_get_contents(APP.'/views/'.$path.$file.'.blade.php');

                        echo '<div class="form" id="form">';
                        echo '<b>Редактирование файла '.$file.'</b><br />';

                        echo '<form method="post">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo '<textarea id="markItUpHtml" cols="90" rows="30" name="msg">'.check($mainfile).'</textarea><br />';
                        echo '<input type="submit" value="Редактировать" /></form></div><br />';

                    } else {
                        show_error('Ошибка! Файл недоступен для записи!');
                    }
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Недопустимое название страницы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path='.$path.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                             Подготовка к созданию файла                                ##
        ############################################################################################
        case 'new':

            echo '<b>Создание нового файла</b><br /><br />';

            if (is_writeable(APP.'/views')) {
                echo '<div class="form"><form action="/admin/files?act=addnew&amp;token='.$_SESSION['token'].'" method="post">';

                echo 'Директория:<br />';
                echo '<select name="dir">';
                echo '<option>Корневая директория</option>';
                $dirnames = glob(APP."/views/*", GLOB_ONLYDIR);
                foreach ($dirnames as $dirname) {
                    $selected = ($dir == basename($dirname)) ? ' selected="selected"' : '';
                    echo '<option value="'.basename($dirname).'"'.$selected.'>'.basename($dirname).'</option>';
                }
                echo '</select><br />';

                echo 'Название файла (без расширения):<br />';
                echo '<input type="text" name="newfile" maxlength="30" /><br /><br />';
                echo '<input value="Создать файл" type="submit" /></form></div>';
                echo '<br />Разрешены латинские символы и цифры, а также знаки дефис и нижнее подчеркивание<br /><br />';
            } else {
                show_error('Директория недоступна для создания файлов!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?dir='.$dir.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                      Создание файла                                    ##
        ############################################################################################
        case 'addnew':

            $token = check(Request::input('token'));
            $newfile = check(Request::input('newfile'));

            $subdir = !empty($dir) ? $dir.'/' : '';

            if ($token == $_SESSION['token']) {
                if (is_writeable(APP.'/views/'.$subdir)) {
                    if (preg_match('|^[a-z0-9_\-]+$|i', $newfile)) {
                        if (!file_exists(APP.'/views/'.$subdir.$newfile.'.blade.php')) {

                            file_put_contents(APP.'/views/'.$subdir.$newfile.'.blade.php', '');
                            chmod(APP.'/views/'.$subdir.$newfile.'.blade.php', 0666);

                            notice('Новый файл успешно создан!');
                            redirect ('/admin/files?act=edit&file='.$newfile.'.blade.php&dir='.$dir);

                        } else {
                            show_error('Ошибка! Файл с данным названием уже существует!');
                        }
                    } else {
                        show_error('Ошибка! Недопустимое название файла!');
                    }
                } else {
                    show_error('Директория недоступна для создания файлов!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?act=new">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                     Удаление файла                                     ##
        ############################################################################################
        case 'del':

            $token = check($_GET['token']);

            $subdir = !empty($dir) ? $dir.'/' : '';

            if ($token == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                    if (file_exists(APP.'/views/'.$subdir.$file)) {

                        if (unlink(APP.'/views/'.$subdir.$file)) {
                            notice('Файл успешно удален!');
                            redirect ('/admin/files?dir='.$dir);

                        } else {
                            show_error('Ошибка! Не удалось удалить файл!');
                        }
                    } else {
                        show_error('Ошибка! Данного файла не существует!');
                    }
                } else {
                    show_error('Ошибка! Недопустимое название страницы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect('/');
}

App::view($config['themes'].'/foot');
