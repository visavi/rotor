<?php
App::view(Setting::get('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['id'])) {
    $id = abs(intval($_GET['id']));
} else {
    $id = 0;
}
if (isset($_GET['cid'])) {
    $cid = abs(intval($_GET['cid']));
} else {
    $cid = 0;
}
$page = abs(intval(Request::input('page', 1)));

if (is_admin()) {
    //show_title('Управление блогами');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $queryblog = DB::run() -> query("SELECT * FROM `catsblog` ORDER BY sort ASC;");
            $blogs = $queryblog -> fetchAll();

            if (count($blogs) > 0) {
                foreach($blogs as $data) {
                    echo '<i class="fa fa-folder-open"></i> ';
                    echo '<b>'.$data['sort'].'. <a href="/admin/blog?act=blog&amp;cid='.$data['id'].'">'.$data['name'].'</a></b> ('.$data['count'].')<br />';

                    if (is_admin([101])) {
                        echo '<a href="/admin/blog?act=editcats&amp;cid='.$data['id'].'">Редактировать</a> / ';
                        echo '<a href="/admin/blog?act=prodelcats&amp;cid='.$data['id'].'">Удалить</a>';
                    }
                    echo '<br />';
                }
            } else {
                show_error('Разделы блогов еще не созданы!');
            }

            if (is_admin([101])) {
                echo '<br /><div class="form">';
                echo '<form action="/admin/blog?act=addcats&amp;uid='.$_SESSION['token'].'" method="post">';
                echo '<b>Заголовок:</b><br />';
                echo '<input type="text" name="name" maxlength="50" />';
                echo '<input type="submit" value="Создать раздел" /></form></div><br />';

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
            }
        break;

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'restatement':

            $uid = check($_GET['uid']);

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    restatement('blog');

                    App::setFlash('success', 'Все данные успешно пересчитаны!');
                    App::redirect("/admin/blog");

                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Добавление разделов                                 ##
        ############################################################################################
        case 'addcats':

            $uid = check($_GET['uid']);
            $name = check($_POST['name']);

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    if (utf_strlen($name) >= 3 && utf_strlen($name) < 50) {
                        $maxorder = DB::run() -> querySingle("SELECT IFNULL(MAX(sort),0)+1 FROM `catsblog`;");
                        DB::run() -> query("INSERT INTO `catsblog` (sort, `name`) VALUES (?, ?);", [$maxorder, $name]);

                        App::setFlash('success', 'Новый раздел успешно добавлен!');
                        App::redirect("/admin/blog");

                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Добавлять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                          Подготовка к редактированию разделов                          ##
        ############################################################################################
        case 'editcats':

            if (is_admin([101])) {
                $blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

                if (!empty($blogs)) {
                    echo '<b><big>Редактирование</big></b><br /><br />';

                    echo '<div class="form">';
                    echo '<form action="/admin/blog?act=changecats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'" method="post">';
                    echo 'Заголовок:<br />';
                    echo '<input type="text" name="name" maxlength="50" value="'.$blogs['name'].'" /><br />';
                    echo 'Положение:<br />';
                    echo '<input type="text" name="order" maxlength="2" value="'.$blogs['sort'].'" /><br /><br />';

                    echo '<input type="submit" value="Изменить" /></form></div><br />';
                } else {
                    show_error('Ошибка! Данного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Редактирование разделов                                ##
        ############################################################################################
        case 'changecats':

            $uid = check($_GET['uid']);
            $name = check($_POST['name']);
            $order = abs(intval($_POST['order']));

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    if (utf_strlen($name) >= 3 && utf_strlen($name) < 50) {
                        $blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

                        if (!empty($blogs)) {
                            DB::run() -> query("UPDATE `catsblog` SET sort=?, `name`=? WHERE `id`=?;", [$order, $name, $cid]);

                            App::setFlash('success', 'Раздел успешно отредактирован!');
                            App::redirect("/admin/blog");

                        } else {
                            show_error('Ошибка! Данного раздела не существует!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название раздела!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Изменять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog?act=editcats&amp;cid='.$cid.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Категории</a><br />';
        break;

        ############################################################################################
        ##                                  Подтвержение удаления                                 ##
        ############################################################################################
        case 'prodelcats':

            if (is_admin([101])) {
                $blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

                if (!empty($blogs)) {
                    echo 'Вы уверены что хотите удалить раздел <b>'.$blogs['name'].'</b> в блогах?<br />';
                    echo '<i class="fa fa-times"></i> <b><a href="/admin/blog?act=delcats&amp;cid='.$cid.'&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';
                } else {
                    show_error('Ошибка! Данного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Удаление раздела                                    ##
        ############################################################################################
        case 'delcats':

            $uid = check($_GET['uid']);

            if (is_admin([101]) && App::getUsername() == Setting::get('nickname')) {
                if ($uid == $_SESSION['token']) {
                    $blogs = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

                    if (!empty($blogs)) {
                        DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_category_id`=?;", ['blog', $cid]);
                        DB::run() -> query("DELETE FROM `blogs` WHERE `category_id`=?;", [$cid]);
                        DB::run() -> query("DELETE FROM `catsblog` WHERE `id`=?;", [$cid]);

                        App::setFlash('success', 'Раздел успешно удален!');
                        App::redirect("/admin/blog");

                    } else {
                        show_error('Ошибка! Данного раздела не существует!');
                    }
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                       Просмотр статей                                  ##
        ############################################################################################
        case 'blog':

            $cats = DB::run() -> queryFetch("SELECT * FROM `catsblog` WHERE `id`=? LIMIT 1;", [$cid]);

            if (!empty($cats)) {
                //Setting::get('newtitle') = $cats['name'];

                echo '<i class="fa fa-folder-open"></i> <b>'.$cats['name'].'</b> (Статей: '.$cats['count'].')';
                echo ' (<a href="/blog/blog?cid='.$cid.'&amp;page='.$page.'">Обзор</a>)';
                echo '<hr />';

                $total = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `category_id`=?;", [$cid]);
                $page = App::paginate(Setting::get('blogpost'), $total);

                if ($total > 0) {

                    $queryblog = DB::run() -> query("SELECT * FROM `blogs` WHERE `category_id`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".Setting::get('blogpost').";", [$cid]);

                    echo '<form action="/admin/blog?act=delblog&amp;cid='.$cid.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                    while ($data = $queryblog -> fetch()) {

                        echo '<div class="b"><i class="fa fa-pencil"></i> ';
                        echo '<b><a href="/blog/blog?act=view&amp;id='.$data['id'].'">'.$data['title'].'</a></b> ('.format_num($data['rating']).')<br />';

                        echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';

                        echo '<a href="/admin/blog?act=editblog&amp;cid='.$cid.'&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a> / ';
                        echo '<a href="/admin/blog?act=moveblog&amp;cid='.$cid.'&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Переместить</a></div>';

                        echo '<div>Автор: '.profile($data['user']).' ('.date_fixed($data['time']).')<br />';
                        echo 'Просмотров: '.$data['visits'].'<br />';
                        echo '<a href="/blog/blog?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].')<br />';
                        echo '</div>';
                    }

                    echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                    App::pagination($page);
                } else {
                    show_error('В данном разделе еще нет статей!');
                }
            } else {
                show_error('Ошибка! Данного раздела не существует!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog">Категории</a><br />';
        break;

        ############################################################################################
        ##                            Подготовка к редактированию статьи                          ##
        ############################################################################################
        case 'editblog':

            $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($blogs)) {
                echo '<b><big>Редактирование</big></b><br /><br />';

                echo '<div class="form next">';
                echo '<form action="/admin/blog?act=addeditblog&amp;cid='.$cid.'&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Заголовок:<br />';
                echo '<input type="text" name="title" size="50" maxlength="50" value="'.$blogs['title'].'" /><br />';
                echo 'Текст:<br />';
                echo '<textarea id="markItUp" cols="25" rows="15" name="text">'.$blogs['text'].'</textarea><br />';
                echo 'Автор:<br />';
                echo '<input type="text" name="user" maxlength="20" value="'.$blogs['user'].'" /><br />';
                echo 'Метки:<br />';
                echo '<input type="text" name="tags" size="50" maxlength="100" value="'.$blogs['tags'].'" /><br />';

                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данной статьи не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog?act=blog&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog">Категории</a><br />';
        break;

        ############################################################################################
        ##                                  Редактирование статьи                                ##
        ############################################################################################
        case 'addeditblog':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $text = check($_POST['text']);
            $user = check($_POST['user']);
            $tags = check($_POST['tags']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) >= 100 && utf_strlen($text) <= Setting::get('maxblogpost')) {
                        if (utf_strlen($tags) >= 2 && utf_strlen($tags) <= 50) {
                            if (preg_match('|^[a-z0-9\-]+$|i', $user)) {
                                $queryblog = DB::run() -> querySingle("SELECT `id` FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);
                                if (!empty($queryblog)) {

                                    DB::run() -> query("UPDATE `blogs` SET `title`=?, `text`=?, `user`=?, `tags`=? WHERE `id`=?;", [$title, $text, $user, $tags, $id]);

                                    App::setFlash('success', 'Статья успешно отредактирована!');
                                    App::redirect("/admin/blog?act=blog&cid=$cid&page=$page");

                                } else {
                                    show_error('Ошибка! Данной статьи не существует!');
                                }
                            } else {
                                show_error('Ошибка! Недопустимые символы в логине! Разрешены только знаки латинского алфавита и цифры!');
                            }
                        } else {
                            show_error('Ошибка! Слишком длинные или короткие метки статьи (от 2 до 50 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный или короткий текст статьи (от 100 до '.Setting::get('maxblogpost').' символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий заголовок (от 5 до 50 символов)!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog?act=editblog&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog?act=blog&amp;cid='.$cid.'&amp;page='.$page.'">В раздел</a><br />';
        break;

        ############################################################################################
        ##                               Подготовка к перемещению статьи                          ##
        ############################################################################################
        case 'moveblog':

            $blogs = DB::run() -> queryFetch("SELECT * FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($blogs)) {
                echo '<i class="fa fa-file-o"></i> <b>'.$blogs['title'].'</b><br /><br />';

                $querycats = DB::run() -> query("SELECT `id`, `name` FROM `catsblog` ORDER BY sort ASC;");
                $cats = $querycats -> fetchAll();

                if (count($cats) > 1) {
                    echo '<div class="form">';
                    echo '<form action="/admin/blog?act=addmoveblog&amp;cid='.$blogs['category_id'].'&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                    echo 'Выберите раздел для перемещения:<br />';
                    echo '<select name="section">';
                    echo '<option value="0">Список разделов</option>';

                    foreach ($cats as $data) {
                        if ($blogs['category_id'] != $data['id']) {
                            echo '<option value="'.$data['id'].'">'.$data['name'].'</option>';
                        }
                    }

                    echo '</select>';
                    echo '<input type="submit" value="Переместить" /></form></div><br />';
                } elseif(count($cats) == 1) {
                    show_error('Нет разделов для перемещения!');
                } else {
                    show_error('Ошибка! Разделы блогов еще не созданы!');
                }
            } else {
                show_error('Ошибка! Данной статьи не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog?act=blog&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Перемещение статьи                                  ##
        ############################################################################################
        case 'addmoveblog':

            $uid = check($_GET['uid']);
            $section = abs(intval($_POST['section']));

            if ($uid == $_SESSION['token']) {
                $querycats = DB::run() -> querySingle("SELECT `id` FROM `catsblog` WHERE `id`=? LIMIT 1;", [$section]);
                if (!empty($querycats)) {
                    $queryblog = DB::run() -> querySingle("SELECT `id` FROM `blogs` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryblog)) {
                        DB::run() -> query("UPDATE `blogs` SET `category_id`=? WHERE `id`=?;", [$section, $id]);
                        DB::run() -> query("UPDATE `comments` SET `relate_category_id`=? WHERE relate_type=? AND `relate_id`=?;", [$section, 'blog', $id]);
                        // Обновление счетчиков
                        DB::run() -> query("UPDATE `catsblog` SET `count`=`count`+1 WHERE `id`=?", [$section]);
                        DB::run() -> query("UPDATE `catsblog` SET `count`=`count`-1 WHERE `id`=?", [$cid]);

                        App::setFlash('success', 'Статья успешно перемещена!');
                        App::redirect("/admin/blog?act=blog&cid=$section");

                    } else {
                        show_error('Ошибка! Статьи для перемещения не существует!');
                    }
                } else {
                    show_error('Ошибка! Выбранного раздела не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog?act=moveblog&amp;cid='.$cid.'&amp;id='.$id.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/blog?act=blog&amp;cid='.$cid.'">К блогам</a><br />';
        break;

        ############################################################################################
        ##                                     Удаление статей                                    ##
        ############################################################################################
        case 'delblog':

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

                    DB::run() -> query("DELETE FROM `comments` WHERE relate_type='blog' AND `relate_id` IN (".$del.");");
                    $delblogs = DB::run() -> exec("DELETE FROM `blogs` WHERE `id` IN (".$del.");");
                    // Обновление счетчиков
                    DB::run() -> query("UPDATE `catsblog` SET `count`=`count`-? WHERE `id`=?", [$delblogs, $cid]);

                    App::setFlash('success', 'Выбранные статьи успешно удалены!');
                    App::redirect("/admin/blog?act=blog&cid=$cid&page=$page");

                } else {
                    show_error('Ошибка! Отсутствуют выбранные статьи для удаления!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/blog?act=blog&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
