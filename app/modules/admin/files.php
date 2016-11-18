<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['start'])) {
    $start = abs(intval($_GET['start']));
} else {
    $start = 0;
}
if (isset($_GET['file'])) {
    $file = check($_GET['file']);
} else {
    $file = '';
}

if (is_admin([101]) && $log == $config['nickname']) {
    show_title('Редактирование страниц');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':
            $dir = Request::input('dir');

            $files = preg_grep('/^([^.])/', scandir(APP.'/views/'.$dir));

            usort($files, function($a, $b) {
                if (is_file(APP.'/views/'.$a) && is_file(APP.'/views/'.$b)) {
                    return 0;
                }
                return (is_dir(APP.'/views/'.$a)) ? -1 : 1;
            });

            $total = count($files);

            if ($total > 0) {

                show_title($dir ? $dir : 'Редактирование страниц');

                if(!empty($dir)) {
                    $dir .= '/';
                }

                echo '<ul class="list-group">';
                foreach ($files as $file) {

                    if (is_dir(APP.'/views/'.$file)) {
                        echo '<li class="list-group-item"><i class="fa fa-folder-o"></i> <b><a href="/admin/files?dir='.$file.'">'.$file.'</a></b><br />';
                        echo 'Файлов: '.count(array_diff(scandir(APP.'/views/'.$file), ['.', '..'])).'</li>';
                    } else {

                        $size = formatsize(filesize(APP.'/views/'.$dir.$file));
                        $strok = count(file(APP.'/views/'.$dir.$file));

                        echo '<li class="list-group-item"><div class="pull-right">';
                        echo '<a href="/admin/files?act=del&amp;file='.$file.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить этот файл\')"><i class="fa fa-remove"></i></a></div>';

                        echo '<i class="fa fa-file-o"></i> ';
                        echo '<b><a href="/admin/files?act=edit&amp;file=' . $file . '">'.$file.'</a></b> (' . $size . ')<br />';
                        echo 'Строк: ' . $strok . ' / ';
                        echo 'Изменен: ' . date_fixed(filemtime(APP.'/views/'.$dir.$file)) . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                show_error('Файлов нет!');
            }

            if ($dir) {
                echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
            }
            echo'<i class="fa fa-file-o"></i> <a href="/admin/files?act=new">Создать</a><br />';
        break;

        ############################################################################################
        ##                             Подготовка к редактированию                                ##
        ############################################################################################
        case 'edit':

            if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                if (file_exists(APP.'/views/'.$file)) {

                    if (is_writeable(APP.'/views/'.$file)) {
                        $mainfile = file_get_contents(APP.'/views/'.$file);

                        echo '<div class="form" id="form">';
                        echo '<b>Редактирование файла '.$file.'</b><br />';

                        echo '<form action="/admin/files?act=editfile&amp;file='.$file.'&amp;uid='.$_SESSION['token'].'" name="form" method="post">';

                        echo '<textarea id="markItUpHtml" cols="90" rows="20" name="msg">'.check($mainfile).'</textarea><br />';
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

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Редактирование файла                                  ##
        ############################################################################################
        case 'editfile':

            $uid = check($_GET['uid']);
            $msg = $_POST['msg'];

            if ($uid == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                    if (file_exists(APP.'/views/'.$file)) {
/*                        $msg = str_replace('&', '&amp;', $msg);
                        $msg = str_replace('&amp;&amp;', '&&', $msg);*/

                        file_put_contents(APP.'/views/'.$file, $msg);

                        notice('Файл успешно отредактирован!');
                        //redirect ("/admin/files?act=edit&file=$file");

                    } else {
                        show_error('Ошибка! Данного файла не существует!');
                    }
                } else {
                    show_error('Ошибка! Недопустимое название страницы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?act=edit&amp;file='.$file.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                             Подготовка к созданию файла                                ##
        ############################################################################################
        case 'new':

            echo '<b>Создание нового файла</b><br /><br />';

            if (is_writeable(APP.'/views')) {
                echo '<div class="form"><form action="/admin/files?act=addnew&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Название файла:<br />';
                echo '<input type="text" name="newfile" maxlength="30" /><br /><br />';
                echo '<input value="Создать файл" type="submit" /></form></div>';
                echo '<br />Разрешены латинские символы и цифры, а также знаки дефис и нижнее подчеркивание<br /><br />';
            } else {
                show_error('Директория недоступна для создания файлов!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                      Создание файла                                    ##
        ############################################################################################
        case 'addnew':

            $uid = check($_GET['uid']);
            $newfile = check($_POST['newfile']);

            if ($uid == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\-]+$|i', $newfile)) {
                    if (!file_exists(APP.'/views/'.$newfile.'.blade.php')) {

                        file_put_contents(APP.'/views/'.$newfile.'.blade.php', '');
                        chmod(APP.'/views/'.$newfile.'.blade.php', 0666);

                        notice('Новый файл успешно создан!');
                        redirect ('/admin/files?act=edit&file='.$newfile.'.blade.php');

                    } else {
                        show_error('Ошибка! Файл с данным названием уже существует!');
                    }
                } else {
                    show_error('Ошибка! Недопустимое название файла!');
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

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                    if (file_exists(APP.'/views/'.$file)) {

                        if (unlink(APP.'/views/'.$file)) {
                            notice('Файл успешно удален!');
                            redirect ('/admin/files');

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
