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
if (isset($_GET['type'])) {
    $type = abs(intval($_GET['type']));
} else {
    $type = 0;
}
$page = abs(intval(Request::input('page', 1)));

if (is_admin([101, 102])) {
    //show_title('Предложения и проблемы');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $type2 = (empty($type))? 1 : 0;

            $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [$type]);
            $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [$type2]);
            $page = App::paginate(Setting::get('postoffers'), $total);

            echo '<i class="fa fa-book"></i> ';

            if (empty($type)) {
                echo '<b>Предложения</b> ('.$total.') / <a href="/admin/offers?type=1">Проблемы</a> ('.$total2.')';
            } else {
                echo '<a href="/admin/offers?type=0">Предложения</a> ('.$total2.') / <b>Проблемы</b> ('.$total.')';
            }

            echo ' / <a href="/offers?type='.$type.'&amp;page='.$page['current'].'">Обзор</a><hr>';

            if ($total > 0) {

                $queryoffers = DB::run() -> query("SELECT * FROM `offers` WHERE `type`=? ORDER BY `votes` DESC, `time` DESC LIMIT ".$page['offset'].", ".Setting::get('postoffers').";", [$type]);

                echo '<form action="/admin/offers?act=del&amp;type='.$type.'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryoffers -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> ';
                    echo '<b><a href="/admin/offers?act=view&amp;type='.$type.'&amp;id='.$data['id'].'">'.$data['title'].'</a></b> (Голосов: '.$data['votes'].')<br>';

                    switch ($data['status']) {
                        case '1': echo '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b><br>';
                            break;
                        case '2': echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b><br>';
                            break;
                        case '3': echo '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b><br>';
                            break;
                        default: echo '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b><br>';
                    }

                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> ';
                    echo '<a href="/admin/offers?act=edit&amp;id='.$data['id'].'">Редактировать</a> / ';
                    echo '<a href="/admin/offers?act=reply&amp;id='.$data['id'].'">Ответить</a></div>';

                    echo '<div>'.App::bbCode($data['text']).'<br>';
                    echo 'Добавлено: '.profile($data['user']).'  ('.date_fixed($data['time']).')<br>';
                    echo '<a href="/offers?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
                    echo '<a href="/offers?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
                }

                echo '<br><input type="submit" value="Удалить выбранное"></form>';

                App::pagination($page);

                echo 'Всего записей: <b>'.$total.'</b><br><br>';
            } else {
                App::showError('Записей еще нет!');
            }

            if (is_admin([101])) {
                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/offers?act=rest&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br>';
            }
        break;

        ############################################################################################
        ##                                    Просмотр записи                                     ##
        ############################################################################################
        case 'view':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [0]);
            $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [1]);

            echo '<i class="fa fa-book"></i> <a href="/admin/offers?type=0">Предложения</a>  ('.$total.') / ';
            echo '<a href="/admin/offers?type=1">Проблемы</a> ('.$total2.') / ';
            echo '<a href="/offers?act=view&amp;type='.$type.'&amp;id='.$id.'">Обзор</a><hr>';

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
            if (!empty($queryoff)) {
                //Setting::get('newtitle') = $queryoff['title'];

                echo '<div class="b">';
                echo '<i class="fa fa-file-o"></i> ';
                echo '<b>'.$queryoff['title'].'</b> (Голосов: '.$queryoff['votes'].')<br>';

                switch ($queryoff['status']) {
                    case '1': echo '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b>';
                        break;
                    case '2': echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b>';
                        break;
                    case '3': echo '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b>';
                        break;
                    default: echo '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b>';
                }

                echo '</div>';

                echo '<div class="right"><a href="/admin/offers?act=edit&amp;id='.$id.'">Редактировать</a> / ';
                echo '<a href="/admin/offers?act=reply&amp;id='.$id.'">Ответить</a></div>';

                echo '<div>'.App::bbCode($queryoff['text']).'<br>';
                echo 'Добавлено: '.profile($queryoff['user']).' ('.date_fixed($queryoff['time']).')<br>';

                echo '<a href="/offers?act=comments&amp;id='.$id.'">Комментарии</a> ('.$queryoff['comments'].') ';
                echo '<a href="/offers?act=end&amp;id='.$id.'">&raquo;</a></div><br>';

                if (!empty($queryoff['text_reply'])) {
                    echo '<div class="b"><b>Официальный ответ</b></div>';
                    echo '<div class="q">'.App::bbCode($queryoff['text_reply']).'<br>';
                    echo profile($queryoff['user_reply']).' ('.date_fixed($queryoff['time_reply']).')</div><br>';
                }
            } else {
                App::showError('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?type='.$type.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Ответ на предложение                                  ##
        ############################################################################################
        case 'reply':

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
            if (!empty($queryoff)) {

                echo '<div class="form">';
                echo '<form action="/admin/offers?act=answer&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Текст ответа: <br><textarea cols="25" rows="5" name="text">'.$queryoff['text_reply'].'</textarea><br>';

                echo 'Статус: <br>';

                $arrstatus = ['Под вопросом', 'В процессе', 'Выполнено', 'Закрыто'];
                echo '<select name="status">';

                foreach ($arrstatus as $k => $v) {
                    $selected = ($k == $queryoff['status']) ? ' selected="selected"' : '';

                    echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
                }
                echo '</select><br>';

                echo 'Закрыть комментарии: ';
                $checked = ($queryoff['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                echo '<input type="submit" value="Отправить"></form></div><br>';
            } else {
                App::showError('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=view&amp;id='.$id.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Добавление ответа                                      ##
        ############################################################################################
        case 'answer':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
            $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
            $status = (isset($_POST['status'])) ? abs(intval($_POST['status'])) : '';
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
                if (!empty($queryoff)) {
                    if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {
                        if ($status >= 0 && $status <= 3) {

                            $text = antimat($text);

                            DB::run() -> query("UPDATE `offers` SET `status`=?, `closed`=?, `text_reply`=?, `user_reply`=?, `time_reply`=? WHERE `id`=?;", [$status, $closed, $text, App::getUsername(), SITETIME, $id]);

                            if ($queryoff['status'] >= 2) {
                                DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND `relate_id`=?;", ['offer', $id]);
                            }

                            App::setFlash('success', 'Данные успешно отправлены!');
                            App::redirect("/admin/offers?act=view&id=$id");
                        } else {
                            App::showError('Ошибка! Недопустимый статус предложения или проблемы!');
                        }
                    } else {
                        App::showError('Ошибка! Слишком длинный или короткий текст ответа (От 5 до 1000 символов)!');
                    }
                } else {
                    App::showError('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=reply&amp;id='.$id.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Редактирование предложения                             ##
        ############################################################################################
        case 'edit':

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
            if (!empty($queryoff)) {

                echo '<div class="form">';
                echo '<form action="/admin/offers?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Тип:<br>';
                echo '<select name="types">';
                $selected = ($queryoff['type'] == 0) ? ' selected="selected"' : '';
                echo '<option value="0"'.$selected.'>Предложение</option>';
                $selected = ($queryoff['type'] == 1) ? ' selected="selected"' : '';
                echo '<option value="1"'.$selected.'>Проблема</option>';
                echo '</select><br>';

                echo 'Заголовок: <br><input type="text" name="title" value="'.$queryoff['title'].'"><br>';
                echo 'Описание: <br><textarea cols="25" rows="5" name="text">'.$queryoff['text'].'</textarea><br>';

                echo 'Закрыть комментарии: ';
                $checked = ($queryoff['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                echo '<input type="submit" value="Изменить"></form></div><br>';
            } else {
                App::showError('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=view&amp;id='.$id.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Изменение описания                                     ##
        ############################################################################################
        case 'change':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
            $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
            $text = (isset($_POST['text'])) ? check($_POST['text']) : '';
            $types = (empty($_POST['types'])) ? 0 : 1;
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if ($uid == $_SESSION['token']) {
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `id`=? LIMIT 1;", [$id]);
                if (!empty($queryoff)) {
                    if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                        if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {

                            $title = antimat($title);
                            $text = antimat($text);

                            DB::run() -> query("UPDATE `offers` SET `type`=?, `closed`=?, `title`=?, `text`=? WHERE `id`=?;", [$types, $closed, $title, $text, $id]);

                            App::setFlash('success', 'Данные успешно отредактированы!');
                            App::redirect("/admin/offers?act=view&id=$id");
                        } else {
                            App::showError('Ошибка! Слишком длинное или короткое описание (От 5 до 1000 символов)!');
                        }
                    } else {
                        App::showError('Ошибка! Слишком длинный или короткий заголовок (От 5 до 50 символов)!');
                    }
                } else {
                    App::showError('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=edit&amp;id='.$id.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                          Удаление предложений и проблем                                ##
        ############################################################################################
        case 'del':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM `offers` WHERE `id` IN (".$del.");");
                    DB::run() -> query("DELETE FROM `comments` WHERE relate_type='offer' AND `relate_id` IN (".$del.");");
                    DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND `relate_id` IN (".$del.");");

                    App::setFlash('success', 'Выбранные пункты успешно удалены!');
                    App::redirect("/admin/offers?type=$type&page=$page");
                } else {
                    App::showError('Ошибка! Отсутствуют выбранные предложения или проблемы!');
                }
            } else {
                App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Пересчет комментариев                                 ##
        ############################################################################################
        case 'rest':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("UPDATE `offers` SET `comments`=(SELECT count(*) FROM `comments` WHERE `offers`.`id`=`comments`.`relate_id` AND relate_type='offer');");

                    App::setFlash('success', 'Комментарии успешно пересчитаны!');
                    App::redirect("/admin/offers");
                } else {
                    App::showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                App::showError('Ошибка! Пересчитывать комментарии могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers">Вернуться</a><br>';
            break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
