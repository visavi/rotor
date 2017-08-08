<?php
App::view(Setting::get('themes').'/index');

$act = check(Request::input('act', 'index'));
$id = abs(intval(Request::input('id')));
$page = abs(intval(Request::input('page', 1)));

if (is_admin()) {
    //show_title('Просмотр новых файлов');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [0]);
            $page = App::paginate(Setting::get('downlist'), $total);

            if ($total > 0) {

                $querynew = DB::run() -> query("SELECT `downs`.*, `name` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE `active`=? ORDER BY `app` DESC, `time` DESC  LIMIT ".$page['offset'].", ".Setting::get('downlist').";", [0]);

                echo '<form action="/admin/newload?act=deldown&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $querynew -> fetch()) {
                    echo '<div class="b">';
                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';

                    if (empty($data['app'])) {
                        echo '<i class="fa fa-download"></i> ';
                    } else {
                        echo '<i class="fa fa-check"></i> ';
                    }

                    echo '<b><a href="/admin/newload?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.date_fixed($data['time']).')</div>';
                    echo '<div>';
                    echo 'Категория: <a href="/load/down?cid='.$data['category_id'].'">'.$data['name'].'</a><br />';
                    echo 'Добавлено: '.profile($data['user']).'<br />';
                    if (!empty($data['link'])) {
                        echo 'Файл: '.$data['link'].' ('.read_file(HOME.'/uploads/files/'.$data['link']).')<br />';
                    } else {
                        echo 'Файл: Не загружен<br />';
                    }
                    if (!empty($data['screen'])) {
                        echo 'Скрин: '.$data['screen'].' ('.read_file(HOME.'/uploads/screen/'.$data['screen']).')<br />';
                    } else {
                        echo 'Скрин: Не загружен<br />';
                    }
                    echo '</div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                echo 'Всего файлов: <b>'.$total.'</b><br /><br />';
            } else {
                show_error('Новых файлов еще нет!');
            }
        break;

        ############################################################################################
        ##                                  Просмотр файла                                      ##
        ############################################################################################
        case 'view':

            $new = DB::run() -> queryFetch("SELECT `d`.*, `c`.`folder` FROM `downs` d LEFT JOIN `cats` c ON `d`.`category_id`=`c`.`id` WHERE d.`id`=? LIMIT 1;", [$id]);

            if (!empty($new)) {
                if (empty($new['active'])) {

                    $querydown = DB::run() -> query("SELECT * FROM `cats` ORDER BY sort ASC;");
                    $downs = $querydown -> fetchAll();

                    if (count($downs) > 0) {

                        if (is_admin([101]) && App::getUsername() == Setting::get('nickname')) {
                            echo '<a href="/admin/newload?act=allow&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете публикацию файла?\')">Опубликовать</a> / ';
                        }

                        echo '<a href="/admin/newload?act=deldown&amp;del='.$new['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление файла?\')">Удалить файл</a><hr />';

                        $folder = $new['folder'] ? $new['folder'].'/' : '';

                        if (!empty($new['link'])) {
                            echo '<i class="fa fa-download"></i> <b><a href="/uploads/files/'.$folder.$new['link'].'">'.$new['link'].'</a></b> ('.read_file(HOME.'/uploads/files/'.$folder.$new['link']).')  (<a href="/admin/newload?act=delfile&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный файл?\')">Удалить</a>)<br />';
                        } else {
                            echo '<i class="fa fa-download"></i> <b>Не загружен</b><br />';
                        }

                        if (!empty($new['screen'])) {
                            echo '<i class="fa fa-picture-o"></i> <b><a href="/uploads/screen/'.$folder.$new['screen'].'">'.$new['screen'].'</a></b> ('.read_file(HOME.'/uploads/screen/'.$folder.$new['screen']).') (<a href="/admin/newload?act=delscreen&amp;id='.$id.'" onclick="return confirm(\'Вы действительно хотите удалить данный скриншот?\')">Удалить</a>)<br /><br />';
                            echo resize_image('uploads/screen/'.$folder, $new['screen'], Setting::get('previewsize')).'<br />';
                        } else {
                            echo '<i class="fa fa-picture-o"></i> <b>Не загружен</b><br />';
                        }

                        echo '<br /><b><big>Редактирование</big></b><br /><br />';

                        echo 'Добавлено: <b>'.profile($new['user']).'</b> '.user_visit($new['user']).'<br />';
                        echo 'Время последнего изменения:  ('.date_fixed($new['time']).')<br /><br />';

                        echo '<div class="form">';
                        echo '<form action="/admin/newload?act=edit&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';
                        echo 'Категория*:<br />';

                        $output = [];

                        foreach ($downs as $row) {
                            $i = $row['id'];
                            $p = $row['parent'];
                            $output[$p][$i] = $row;
                        }

                        echo '<select name="cid">';

                        foreach ($output[0] as $key => $data) {
                            $selected = $new['category_id'] == $data['id'] ? ' selected="selected"' : '';
                            $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : '';
                            echo '<option value="'.$data['id'].'"'.$selected.$disabled.'>'.$data['name'].'</option>';

                            if (isset($output[$key])) {
                                foreach($output[$key] as $datasub) {
                                    $selected = ($new['category_id'] == $datasub['id']) ? ' selected="selected"' : '';
                                    $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : '';
                                    echo '<option value="'.$datasub['id'].'"'.$selected.$disabled.'>– '.$datasub['name'].'</option>';
                                }
                            }
                        }

                        echo '</select><br />';

                        if (empty($new['site'])) {
                            $new['site'] = 'http://';
                        }

                        echo 'Название*:<br />';
                        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$new['title'].'" /><br />';
                        echo 'Описание*:<br />';
                        echo '<textarea cols="25" rows="10" name="text">'.$new['text'].'</textarea><br />';
                        echo 'Автор файла:<br />';
                        echo '<input type="text" name="author" maxlength="50" value="'.$new['author'].'" /><br />';
                        echo 'Сайт автора:<br />';
                        echo '<input type="text" name="site" maxlength="50" value="'.$new['site'].'" /><br />';
                        echo 'Имя файла*:<br />';
                        echo '<input type="text" name="link" maxlength="50" value="'.$new['link'].'" /><br />';
                        echo 'Уведомление:<br />';
                        echo '<textarea cols="25" rows="5" name="notice">'.$new['notice'].'</textarea><br />';

                        echo 'Файл проверен: ';
                        $checked = ($new['app'] == 1) ? ' checked="checked"' : '';
                        echo '<input name="app" type="checkbox" value="1"'.$checked.' /><br /><br />';

                        echo '<input value="Изменить" type="submit" /></form></div><br />';

                    } else {
                        show_error('Категории файлов еще не созданы!');
                    }
                } else {
                    show_error('Ошибка! Данный файл уже проверен модератором!');
                }
            } else {
                show_error('Данного файла не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'edit':

            $uid = check($_GET['uid']);
            $cid = abs(intval($_POST['cid']));
            $title = check($_POST['title']);
            $text = check($_POST['text']);
            $author = check($_POST['author']);
            $site = ($_POST['site'] != 'http://') ? check($_POST['site']) : '';
            $link = check(strtolower($_POST['link']));
            $notice = check($_POST['notice']);
            $app = (empty($_POST['app'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
                    if (utf_strlen($text) >= 10 && utf_strlen($text) < 5000) {
                        if (utf_strlen($author) <= 50) {
                            if (empty($site) || preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $site)) {
                                if (strlen($link) <= 50) {
                                    if (!preg_match('/\.(php|pl|cgi|phtml|htaccess)/i', $link)) {

                                        $new = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.`folder` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id`=? LIMIT 1;", [$id]);

                                        $folder = $new['folder'] ? $new['folder'].'/' : '';

                                        if (!empty($new)) {
                                            if (empty($new['active'])) {
                                                $downs = DB::run() -> querySingle("SELECT `id` FROM `cats` WHERE `id`=? LIMIT 1;", [$cid]);
                                                if (!empty($downs)) {
                                                    $downlink = DB::run() -> querySingle("SELECT `link` FROM `downs` WHERE `link`=? AND `id`<>? LIMIT 1;", [$link, $id]);
                                                    if (empty($downlink)) {

                                                        $newtitle = DB::run() -> querySingle("SELECT `title` FROM `downs` WHERE `title`=? AND `id`<>? LIMIT 1;", [$title, $id]);
                                                        if (empty($newtitle)) {

                                                            if (!empty($link) && $link != $new['link'] && file_exists(HOME.'/uploads/files/'.$folder.$new['link'])) {

                                                                $oldext = getExtension($new['link']);
                                                                $newext = getExtension($link);

                                                                if ($oldext == $newext) {

                                                                    $screen = $new['screen'];
                                                                    rename(HOME.'/uploads/files/'.$folder.$new['link'], HOME.'/uploads/files/'.$folder.$link);

                                                                    if (!empty($new['screen']) && file_exists(HOME.'/uploads/screen/'.$folder.$new['screen'])) {

                                                                        $screen = $link.'.'.getExtension($new['screen']);
                                                                        rename(HOME.'/uploads/screen/'.$folder.$new['screen'], HOME.'/uploads/screen/'.$screen);
                                                                        unlink_image('uploads/screen/'.$folder, $new['screen']);
                                                                    }
                                                                    DB::run() -> query("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", [$link, $screen, $id]);
                                                                }
                                                            }

                                                            if (!empty($notice) && $notice != $new['notice']) {
                                                                // ------------------------Уведомление по привату------------------------//
                                                                if (user($new['user'])) {
                                                                    $textpriv = 'Уведомеление о проверке файла.'.PHP_EOL.'Ваш файл [b]'.$new['title'].'[/b] не прошел проверку на добавление'.PHP_EOL.'Причина: '.$notice.PHP_EOL.'Отредактировать описание файла вы можете на [url='.Setting::get('home').'/load/add?act=view&amp;id='.$id.']этой[/url] странице';

                                                                    DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$new['user'], App::getUsername(), $textpriv, SITETIME]);

                                                                    DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=?", [$new['user']]);
                                                                }
                                                            }

                                                            DB::run() -> query("UPDATE `downs` SET `category_id`=?, `title`=?, `text`=?, `author`=?, `site`=?, `notice`=?, `time`=?, `app`=? WHERE `id`=?;", [$cid, $title, $text, $author, $site, $notice, $new['time'], $app, $id]);

                                                            App::setFlash('success', 'Данные успешно изменены!');
                                                            App::redirect("/admin/newload?act=view&id=$id");

                                                        } else {
                                                            show_error('Ошибка! Название файла '.$title.' уже имеется в загрузках!');
                                                        }
                                                    } else {
                                                        show_error('Ошибка! Имя файла '.$link.' уже имеется в загрузках!');
                                                    }
                                                } else {
                                                    show_error('Ошибка! Выбранный вами раздел не существует!');
                                                }
                                            } else {
                                                show_error('Ошибка! Данный файл уже проверен модератором!');
                                            }
                                        } else {
                                            show_error('Ошибка! Данного файла не существует!');
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
                            show_error('Ошибка! Слишком длинный ник (логин) автора (не более 50 символов)!');
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

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload?act=view&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Публикация файла                                     ##
        ############################################################################################
        case 'allow':

            $uid = check($_GET['uid']);

            if (is_admin([101]) && App::getUsername() == Setting::get('nickname')) {
                if ($uid == $_SESSION['token']) {
                    $new = DB::run() -> queryFetch("SELECT * FROM `downs` WHERE `id`=? LIMIT 1;", [$id]);

                    if (!empty($new)) {
                        if (empty($new['active'])) {
                            if (!empty($new['link'])) {

                                DB::run() -> query("UPDATE `downs` SET `notice`=?, `time`=?, `app`=?, `active`=? WHERE `id`=?;", ['', SITETIME, 0, 1, $id]);

                                DB::run() -> query("UPDATE `cats` SET `count`=`count`+1 WHERE `id`=?", [$new['category_id']]);

                                if (user($new['user'])) {
                                    $textpriv = 'Уведомеление о проверке файла.'.PHP_EOL.'Ваш файл [b]'.$new['title'].'[/b] успешно прошел проверку и добавлен в архив файлов'.PHP_EOL.'Просмотреть свой файл вы можете на [url='.Setting::get('home').'/load/down?act=view&amp;id='.$id.']этой[/url] странице';

                                    DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);", [$new['user'], App::getUsername(), $textpriv, SITETIME]);
                                    DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=?", [$new['user']]);
                                }

                                App::setFlash('success', 'Файл успешно опубликован!');
                                App::redirect("/admin/newload");

                            } else {
                                show_error('Ошибка! В данной загрузке отсутствует прикрепленный файл!');
                            }
                        } else {
                            show_error('Ошибка! Данный файл уже проверен модератором!');
                        }
                    } else {
                        show_error('Ошибка! Данного файла не существует!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Опубликовывать файлы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload?act=view&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Удаление файла                                       ##
        ############################################################################################
        case 'delfile':

            $link = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.`folder` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id`=? LIMIT 1;", [$id]);

            $folder = $link['folder'] ? $link['folder'].'/' : '';

            if (!empty($link)) {
                if (empty($link['active'])) {

                    if (!empty($link['link']) && file_exists(HOME.'/uploads/files/'.$folder.$link['link'])) {
                        unlink(HOME.'/uploads/files/'.$folder.$link['link']);
                    }

                    unlink_image('uploads/screen/'.$folder, $link['screen']);

                    DB::run() -> query("UPDATE `downs` SET `link`=?, `screen`=? WHERE `id`=?;", ['', '', $id]);

                    App::setFlash('success', 'Файл успешно удален!');
                    App::redirect("/admin/newload?act=view&id=$id");

                } else {
                    show_error('Ошибка! Данный файл уже проверен модератором!');
                }
            } else {
                show_error('Ошибка! Данного файла не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload?act=view&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Удаление скриншота                                  ##
        ############################################################################################
        case 'delscreen':

            $screen = DB::run() -> queryFetch("SELECT `downs`.*, `cats`.`folder` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id`=? LIMIT 1;", [$id]);

            $folder = $screen['folder'] ? $screen['folder'].'/' : '';

            if (!empty($screen)) {
                if (empty($screen['active'])) {

                    unlink_image('uploads/screen/'.$folder, $screen['screen']);

                    DB::run() -> query("UPDATE `downs` SET `screen`=? WHERE `id`=?;", ['', $id]);

                    App::setFlash('success', 'Скриншот успешно удален!');
                    App::redirect("/admin/newload?act=view&id=$id");

                } else {
                    show_error('Ошибка! Данный файл уже проверен модератором!');
                }
            } else {
                show_error('Ошибка! Данного файла не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload?act=view&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Удаление файлов                                      ##
        ############################################################################################
        case 'deldown':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } elseif (isset($_GET['del'])) {
                $del = [abs(intval($_GET['del']))];
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);


                    $querydel = DB::run() -> query("SELECT `downs`.*, `cats`.`folder` FROM `downs` LEFT JOIN `cats` ON `downs`.`category_id`=`cats`.`id` WHERE downs.`id` IN (".$del.");");
                    $arr_files = $querydel -> fetchAll();

                    DB::run() -> query("DELETE FROM `downs` WHERE `id` IN (".$del.");");

                    foreach ($arr_files as $delfile) {
                        $folder = $delfile['folder'] ? $delfile['folder'].'/' : '';

                        if (!empty($delfile['link']) && file_exists(HOME.'/uploads/files/'.$folder.$delfile['link'])) {
                            unlink(HOME.'/uploads/files/'.$folder.$delfile['link']);
                        }

                        unlink_image('uploads/screen/'.$folder, $delfile['screen']);
                    }

                    App::setFlash('success', 'Выбранные файлы успешно удалены!');
                    App::redirect("/admin/newload?page=$page");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные файлы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/newload?page='.$page.'">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
