<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$file = check(Request::input('file'));
$path = check(Request::input('path'));

if (
    !file_exists(RESOURCES.'/views/'.$path) ||
    !is_dir(RESOURCES.'/views/'.$path) ||
    str_contains($path, '.') ||
    starts_with($path, '/') ||
    !ends_with($path, '/')
) {
    $path = '';
}

if (isAdmin([101]) && getUser('login') == env('SITE_ADMIN')) {
    //show_title('Редактирование страниц');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':



        break;

        ############################################################################################
        ##                             Подготовка к редактированию                                ##
        ############################################################################################
        case 'edit':

            if ((preg_match('#^([a-z0-9_\-/]+|)$#', $path)) && preg_match('#^[a-z0-9_\-/]+$#', $file)) {
                if (file_exists(RESOURCES.'/views/'.$path.$file.'.blade.php')) {
                    if (is_writable(RESOURCES.'/views/'.$path.$file.'.blade.php')) {

                        if (Request::isMethod('post')) {
                            $token = check(Request::input('token'));
                            $msg = Request::input('msg');

                            if ($token == $_SESSION['token']) {

                                file_put_contents(RESOURCES.'/views/'.$path.$file.'.blade.php', $msg);

                                setFlash('success', 'Файл успешно сохранен!');
                                redirect ("/admin/files?act=edit&path=$path&file=$file");

                            } else {
                                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                            }
                        }

                        $mainfile = file_get_contents(RESOURCES.'/views/'.$path.$file.'.blade.php');

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

            if (is_writable(RESOURCES.'/views/'.$path)) {

                if (Request::isMethod('post')) {
                    $token = check(Request::input('token'));
                    $name = check(Request::input('name'));
                    $type = check(Request::input('type'));

                    if ($token == $_SESSION['token']) {
                        if (preg_match('|^[a-z0-9_\-]+$|', $name)) {

                            if ($type == 'file') {
                                if (!file_exists(RESOURCES .'/views/'.$path.$name.'.blade.php')) {

                                    file_put_contents(RESOURCES.'/views/'.$path.$name.'.blade.php', '');
                                    chmod(RESOURCES.'/views/'.$path.$name.'.blade.php', 0666);

                                    setFlash('success', 'Новый файл успешно создан!');
                                    redirect('/admin/files?act=edit&file='.$name.'&path='.$path);

                                } else {
                                    showError('Ошибка! Файл с данным названием уже существует!');
                                }
                            } else {
                                if (!file_exists(RESOURCES .'/views/'.$path.$name)) {
                                    $old = umask(0);
                                    mkdir(RESOURCES .'/views/'.$path.$name, 0777, true);
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
                if (is_writable(RESOURCES.'/views/'.$path)) {
                    if (preg_match('|^[a-z0-9_\-]+$|', $name)) {

                        if ($type == 'dir') {
                            if (file_exists(RESOURCES .'/views/'.$path.$name)) {
                                removeDir(RESOURCES . '/views/' . $path . $name);
                                setFlash('success', 'Директория успешно удалена!');
                                redirect('/admin/files?path=' . $path);
                            } else {
                                showError('Ошибка! Данного директории не существует!');
                            }
                        } else {
                            if (file_exists(RESOURCES .'/views/'.$path.$name.'.blade.php')) {

                                if (unlink(RESOURCES .'/views/'.$path.$name.'.blade.php')) {
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
