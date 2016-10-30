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

if (is_admin(array(101)) && $log == $config['nickname']) {
    show_title('Редактирование страниц');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $arrfiles = array();
            $globfiles = glob(DATADIR."/main/*.dat");
            foreach ($globfiles as $filename) {
                $arrfiles[] = basename($filename);
            }

            $total = count($arrfiles);

            if ($total > 0) {
                if ($start < 0 || $start > $total) {
                    $start = 0;
                }
                if ($total < $start + $config['editfiles']) {
                    $end = $total;
                } else {
                    $end = $start + $config['editfiles'];
                }
                for ($i = $start; $i < $end; $i++) {
                    $filename = str_replace('.dat', '', $arrfiles[$i]);
                    $size = formatsize(filesize(DATADIR."/main/$arrfiles[$i]"));
                    $strok = count(file(DATADIR."/main/$arrfiles[$i]"));

                    if ($arrfiles[$i] == 'index.dat') {
                        echo '<div class="b"><img src="/assets/img/images/edit.gif" alt="image" /> ';
                        echo '<b><a href="/"><span style="color:#ff0000">'.$arrfiles[$i].'</span></a></b> ('.$size.')<br />';
                        echo '<a href="/admin/files?act=edit&amp;file='.$arrfiles[$i].'">Редактировать</a> | ';
                        echo '<a href="/admin/files?act=obzor&amp;file='.$arrfiles[$i].'">Просмотр</a></div>';
                        echo '<div>Кол. строк: '.$strok.'<br />';
                        echo 'Изменен: '.date_fixed(filemtime(DATADIR."/main/$arrfiles[$i]")).'</div>';
                    } else {
                        echo '<div class="b"><img src="/assets/img/images/edit.gif" alt="image" /> ';
                        echo '<b><a href="/page/'.$filename.'">'.$arrfiles[$i].'</a></b> ('.$size.')<br />';
                        echo '<a href="/admin/files?act=edit&amp;file='.$arrfiles[$i].'">Редактировать</a> | ';
                        echo '<a href="/admin/files?act=obzor&amp;file='.$arrfiles[$i].'">Просмотр</a> | ';
                        echo '<a href="/admin/files?act=poddel&amp;file='.$arrfiles[$i].'">Удалить</a></div>';
                        echo '<div>Кол. строк: '.$strok.'<br />';
                        echo 'Изменен: '.date_fixed(filemtime(DATADIR."/main/$arrfiles[$i]")).'</div>';
                    }
                }

                page_strnavigation('/admin/files?', $config['editfiles'], $start, $total);

                echo 'Всего файлов: <b>'.(int)$total.'</b><br /><br />';
            } else {
                show_error('Файлов еще нет!');
            }

            echo'<img src="/assets/img/images/files.gif" alt="image" /> <a href="/admin/files?act=new">Создать</a><br />';
        break;

        ############################################################################################
        ##                                      Обзор файла                                       ##
        ############################################################################################
        case 'obzor':

            if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                if (file_exists(DATADIR."/main/$file")) {
                    echo '<b>Просмотр файла '.$file.'</b><br />';

                    $opis = file_get_contents(DATADIR."/main/$file");
                    $count = count(file(DATADIR."/main/$file"));

                    echo 'Строк: '.(int)$count.'<br /><br />';

                    echo '<pre class="prettyprint linenums">'.check($opis).'</pre><br />';

                    echo '<img src="/assets/img/images/edit.gif" alt="image" /> <a href="/admin/files?act=edit&amp;file='.$file.'">Редактировать</a><br />';
                    echo '<img src="/assets/img/images/error.gif" alt="image" /> <a href="/admin/files?act=poddel&amp;file='.$file.'">Удалить</a><br />';
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Недопустимое название страницы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                             Подготовка к редактированию                                ##
        ############################################################################################
        case 'edit':

            if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                if (file_exists(DATADIR."/main/$file")) {
                    $filename = str_replace(".dat", "", $file);

                    if (is_writeable(DATADIR."/main/$file")) {
                        $mainfile = file_get_contents(DATADIR."/main/$file");
                        $mainfile = str_replace('&amp;', '&', $mainfile);

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
            echo '<img src="/assets/img/images/online.gif" alt="image" /> <a href="/page/'.$filename.'">Просмотр</a><br />';
        break;

        ############################################################################################
        ##                                  Редактирование файла                                  ##
        ############################################################################################
        case 'editfile':

            $uid = check($_GET['uid']);
            $msg = $_POST['msg'];

            if ($uid == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                    if (file_exists(DATADIR.'/main/'.$file)) {
                        $msg = str_replace('&', '&amp;', $msg);
                        $msg = str_replace('&amp;&amp;', '&&', $msg);

                        $fp = fopen(DATADIR.'/main/'.$file, "a+");
                        flock ($fp, LOCK_EX);
                        ftruncate($fp, 0);
                        fputs ($fp, $msg);
                        fflush($fp);
                        flock ($fp, LOCK_UN);
                        fclose($fp);

                        $_SESSION['note'] = 'Файл успешно отредактирован!';
                        redirect ("/admin/files?act=edit&file=$file");

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

            if (is_writeable(DATADIR."/main")) {
                echo '<div class="form"><form action="/admin/files?act=addnew&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Название файла:<br />';
                echo '<input type="text" name="newfile" maxlength="20" /><br /><br />';
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
                    if (!file_exists(DATADIR.'/main/'.$newfile.'.dat')) {
                        $fp = fopen(DATADIR.'/main/'.$newfile.'.dat', "a+");
                        flock ($fp, LOCK_EX);
                        fputs ($fp, '');
                        fflush($fp);
                        flock ($fp, LOCK_UN);
                        fclose($fp);
                        chmod(DATADIR.'/main/'.$newfile.'.dat', 0666);

                        $_SESSION['note'] = 'Новый файл успешно создан!';
                        redirect ('/admin/files?act=edit&file='.$newfile.'.dat');

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
        ##                                  Подготовка к удалению                                 ##
        ############################################################################################
        case 'poddel':

            if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                if (file_exists(DATADIR."/main/$file")) {
                    echo 'Вы подтверждаете что хотите удалить файл <b>'.$file.'</b><br />';
                    echo '<img src="/assets/img/images/error.gif" alt="image" /> <b><a href="/admin/files?act=del&amp;file='.$file.'&amp;uid='.$_SESSION['token'].'">Удалить</a></b><br /><br />';
                } else {
                    show_error('Ошибка! Данного файла не существует!');
                }
            } else {
                show_error('Ошибка! Недопустимое название страницы!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/files">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                     Удаление файла                                     ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (preg_match('|^[a-z0-9_\.\-]+$|i', $file)) {
                    if (file_exists(DATADIR."/main/$file")) {
                        if ($file != 'index.dat') {
                            if (unlink (DATADIR."/main/$file")) {
                                $_SESSION['note'] = 'Файл успешно удален!';
                                redirect ('/admin/files');

                            } else {
                                show_error('Ошибка! Не удалось удалить файл!');
                            }
                        } else {
                            show_error('Ошибка! Запрещено удалять главный файл!');
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
