<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$file = check(Request::input('file'));
$path = check(Request::input('path'));

if (
    !file_exists(APP.'/views/'.$path) ||
    !is_dir(APP.'/views/'.$path) ||
    str_contains($path, '.') ||
    starts_with($path, '/') ||
    !ends_with($path, '/')
) {
    $path = '';
}

if (isAdmin([101]) && getUsername() == setting('nickname')) {
    //show_title('Редактирование страниц');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $files = preg_grep('/^([^.])/', scandir(APP.'/views/'.$path.$file));

            usort($files, function($a, $b) use($path) {
                if (is_file(APP.'/views/'.$path.$a) && is_file(APP.'/views/'.$path.$b)) {
                    return 0;
                }
                return (is_dir(APP.'/views/'.$path.$a)) ? -1 : 1;
            });

            $total = count($files);

            //show_title($path ? $path : 'Редактирование страниц');

            if ($total > 0) {

                echo '<ul class="list-group">';
                foreach ($files as $file) {

                    if (is_dir(APP.'/views/'.$path.$file)) {
                        echo '<li class="list-group-item">';
                        echo '<div class="float-right">';

                        echo '<a href="/admin/files?act=del&amp;path='.$path.'&amp;name='.$file.'&amp;type=dir&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить эту директорию\')"><i class="fa fa-remove"></i></a></div>';

                        echo '<i class="fa fa-folder-o"></i> <b><a href="/admin/files?path='.$path.$file.'/">'.$file.'</a></b><br>';
                        echo 'Объектов: '.count(array_diff(scandir(APP.'/views/'.$path.$file), ['.', '..'])).'</li>';
                    } else {

                        $size = formatSize(filesize(APP.'/views/'.$path.$file));
                        $strok = count(file(APP.'/views/'.$path.$file));

                        echo '<li class="list-group-item"><div class="float-right">';
                        echo '<a href="/admin/files?act=del&amp;path='.$path.'&amp;name='.basename($file, '.blade.php').'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить этот файл\')"><i class="fa fa-remove"></i></a></div>';

                        echo '<i class="fa fa-file-o"></i> ';
                        echo '<b><a href="/admin/files?act=edit&amp;path='.$path.'&amp;file='.basename($file, '.blade.php').'">'.$file.'</a></b> (' . $size . ')<br>';
                        echo 'Строк: ' . $strok . ' / ';
                        echo 'Изменен: ' . dateFixed(filemtime(APP.'/views/'.$path.$file)) . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                showError('Файлов нет!');
            }

            if ($path) {
                echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path='.ltrim(dirname($path), '.').'/">Вернуться</a><br>';
            }
            echo'<i class="fa fa-file-o"></i> <a href="/admin/files?act=new&amp;path='.$path.'">Создать</a><br>';
        break;

        ############################################################################################
        ##                             Подготовка к редактированию                                ##
        ############################################################################################
        case 'edit':

            if ((preg_match('#^([a-z0-9_\-/]+|)$#', $path)) && preg_match('#^[a-z0-9_\-/]+$#', $file)) {
                if (file_exists(APP.'/views/'.$path.$file.'.blade.php')) {
                    if (is_writeable(APP.'/views/'.$path.$file.'.blade.php')) {

                        if (Request::isMethod('post')) {
                            $token = check(Request::input('token'));
                            $msg = Request::input('msg');

                            if ($token == $_SESSION['token']) {

                                file_put_contents(APP.'/views/'.$path.$file.'.blade.php', $msg);

                                setFlash('success', 'Файл успешно сохранен!');
                                redirect ("/admin/files?act=edit&path=$path&file=$file");

                            } else {
                                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                            }
                        }

                        $mainfile = file_get_contents(APP.'/views/'.$path.$file.'.blade.php');

                        echo '<div class="form" id="form">';
                        echo '<b>Редактирование файла '.$file.'</b><br>';

                        echo '<form method="post">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo '<textarea id="markItUpHtml" cols="90" rows="30" name="msg">'.check($mainfile).'</textarea><br>';
                        echo '<input type="submit" value="Редактировать"></form></div><br>';

                        echo '<p class="help-block">Нажмите Ctrl+Enter для перевода строки, Shift+Enter для вставки линии</p>';

                    } else {
                        showError('Ошибка! Файл недоступен для записи!');
                    }
                } else {
                    showError('Ошибка! Данного файла не существует!');
                }
            } else {
                showError('Ошибка! Недопустимое название страницы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path='.$path.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                             Подготовка к созданию файла                                ##
        ############################################################################################
        case 'new':

            echo '<b>Создание нового файла</b><br><br>';

            if (is_writeable(APP.'/views/'.$path)) {

                if (Request::isMethod('post')) {
                    $token = check(Request::input('token'));
                    $name = check(Request::input('name'));
                    $type = check(Request::input('type'));

                    if ($token == $_SESSION['token']) {
                        if (preg_match('|^[a-z0-9_\-]+$|', $name)) {

                            if ($type == 'file') {
                                if (!file_exists(APP .'/views/'.$path.$name.'.blade.php')) {

                                    file_put_contents(APP.'/views/'.$path.$name.'.blade.php', '');
                                    chmod(APP.'/views/'.$path.$name.'.blade.php', 0666);

                                    setFlash('success', 'Новый файл успешно создан!');
                                    redirect('/admin/files?act=edit&file='.$name.'&path='.$path);

                                } else {
                                    showError('Ошибка! Файл с данным названием уже существует!');
                                }
                            } else {
                                if (!file_exists(APP .'/views/'.$path.$name)) {
                                    $old = umask(0);
                                    mkdir(APP .'/views/'.$path.$name, 0777, true);
                                    umask($old);

                                    setFlash('success', 'Новая директория успешно создана!');
                                    redirect('/admin/files?path='.$path.$name.'/');
                                } else {
                                    showError('Ошибка! Категория с данным названием уже существует!');
                                }
                            }

                        } else {
                            showError('Ошибка! Недопустимое название файла или директории!');
                        }
                    } else {
                        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                    }
                }

                echo '<div class="form">';
                echo '<form action="/admin/files?act=new&amp;path='.$path.'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                echo '<input type="hidden" name="type" value="dir">';
                echo 'Название директории:<br>';
                echo '<input type="text" name="name" maxlength="30"><br>';
                echo '<input value="Создать директорию" type="submit"></form></div><br>';

                echo '<div class="form">';
                echo '<form action="/admin/files?act=new&amp;path='.$path.'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                echo '<input type="hidden" name="type" value="file">';
                echo 'Название файла (без расширения):<br>';
                echo '<input type="text" name="name" maxlength="30"><br>';
                echo '<input value="Создать файл" type="submit"></form></div>';
                echo '<br>Разрешены латинские символы и цифры, а также знаки дефис и нижнее подчеркивание<br><br>';
            } else {
                showError('Директория '.$path.' недоступна для записи!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path='.$path.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Удаление файла                                     ##
        ############################################################################################
        case 'del':

            $token = check(Request::input('token'));
            $name = check(Request::input('name'));
            $type = check(Request::input('type'));

            if ($token == $_SESSION['token']) {
                if (is_writeable(APP.'/views/'.$path)) {
                    if (preg_match('|^[a-z0-9_\-]+$|', $name)) {

                        if ($type == 'dir') {
                            if (file_exists(APP .'/views/'.$path.$name)) {
                                removeDir(APP . '/views/' . $path . $name);
                                setFlash('success', 'Директория успешно удалена!');
                                redirect('/admin/files?path=' . $path);
                            } else {
                                showError('Ошибка! Данного директории не существует!');
                            }
                        } else {
                            if (file_exists(APP .'/views/'.$path.$name.'.blade.php')) {

                                if (unlink(APP .'/views/'.$path.$name.'.blade.php')) {
                                    setFlash('success', 'Файл успешно удален!');
                                    redirect ('/admin/files?path='.$path);

                                } else {
                                    showError('Ошибка! Не удалось удалить файл!');
                                }
                            } else {
                                showError('Ошибка! Данного файла не существует!');
                            }
                        }
                    } else {
                        showError('Ошибка! Недопустимое название страницы!');
                    }
                } else {
                    showError('Директория '.$path.' недоступна для записи!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files?path='.$path.'">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
