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

if (is_admin(array(101, 102))) {
    show_title('Предложения и проблемы');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $type2 = (empty($type))? 1 : 0;

            $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `offers_type`=?;", array($type));
            $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `offers_type`=?;", array($type2));

            echo '<i class="fa fa-book"></i> ';

            if (empty($type)) {
                echo '<b>Предложения</b> ('.$total.') / <a href="/admin/offers?type=1">Проблемы</a> ('.$total2.')';
            } else {
                echo '<a href="/admin/offers?type=0">Предложения</a> ('.$total2.') / <b>Проблемы</b> ('.$total.')';
            }

            echo ' / <a href="/pages//admin/offers?type='.$type.'&amp;start='.$start.'">Обзор</a><hr />';

            if ($total > 0) {
                if ($start >= $total) {
                    $start = 0;
                }

                $queryoffers = DB::run() -> query("SELECT * FROM `offers` WHERE `offers_type`=? ORDER BY `offers_votes` DESC, `offers_time` DESC LIMIT ".$start.", ".$config['postoffers'].";", array($type));

                echo '<form action="/admin/offers?act=del&amp;type='.$type.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryoffers -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-file-o"></i> ';
                    echo '<b><a href="/admin/offers?act=view&amp;type='.$type.'&amp;id='.$data['offers_id'].'">'.$data['offers_title'].'</a></b> (Голосов: '.$data['offers_votes'].')<br />';

                    switch ($data['offers_status']) {
                        case '1': echo '<i class="fa fa-spinner"></i> <b><span style="color:#0000ff">В процессе</span></b><br />';
                            break;
                        case '2': echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Выполнено</span></b><br />';
                            break;
                        case '3': echo '<i class="fa fa-times-circle"></i> <b><span style="color:#ff0000">Закрыто</span></b><br />';
                            break;
                        default: echo '<i class="fa fa-question-circle"></i> <b><span style="color:#ffa500">Под вопросом</span></b><br />';
                    }

                    echo '<input type="checkbox" name="del[]" value="'.$data['offers_id'].'" /> ';
                    echo '<a href="/admin/offers?act=edit&amp;id='.$data['offers_id'].'">Редактировать</a> / ';
                    echo '<a href="/admin/offers?act=reply&amp;id='.$data['offers_id'].'">Ответить</a></div>';

                    echo '<div>'.bb_code($data['offers_text']).'<br />';
                    echo 'Добавлено: '.profile($data['offers_user']).'  ('.date_fixed($data['offers_time']).')<br />';
                    echo '<a href="/pages//admin/offers?act=comments&amp;id='.$data['offers_id'].'">Комментарии</a> ('.$data['offers_comments'].') ';
                    echo '<a href="/pages//admin/offers?act=end&amp;id='.$data['offers_id'].'">&raquo;</a></div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                page_strnavigation('/admin/offers?type='.$type.'&amp;', $config['postoffers'], $start, $total);

                echo 'Всего записей: <b>'.$total.'</b><br /><br />';
            } else {
                show_error('Записей еще нет!');
            }

            if (is_admin(array(101))) {
                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/offers?act=rest&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
            }
        break;

        ############################################################################################
        ##                                    Просмотр записи                                     ##
        ############################################################################################
        case 'view':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `offers_type`=?;", array(0));
            $total2 = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `offers_type`=?;", array(1));

            echo '<i class="fa fa-book"></i> <a href="/admin/offers?type=0">Предложения</a>  ('.$total.') / ';
            echo '<a href="/admin/offers?type=1">Проблемы</a> ('.$total2.') / ';
            echo '<a href="/pages//admin/offers?act=view&amp;type='.$type.'&amp;id='.$id.'">Обзор</a><hr />';

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `offers_id`=? LIMIT 1;", array($id));
            if (!empty($queryoff)) {
                $config['newtitle'] = $queryoff['offers_title'];

                echo '<div class="b">';
                echo '<i class="fa fa-file-o"></i> ';
                echo '<b>'.$queryoff['offers_title'].'</b> (Голосов: '.$queryoff['offers_votes'].')<br />';

                switch ($queryoff['offers_status']) {
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

                echo '<div>'.bb_code($queryoff['offers_text']).'<br />';
                echo 'Добавлено: '.profile($queryoff['offers_user']).' ('.date_fixed($queryoff['offers_time']).')<br />';

                echo '<a href="/pages//admin/offers?act=comments&amp;id='.$id.'">Комментарии</a> ('.$queryoff['offers_comments'].') ';
                echo '<a href="/pages//admin/offers?act=end&amp;id='.$id.'">&raquo;</a></div><br />';

                if (!empty($queryoff['offers_text_reply'])) {
                    echo '<div class="b"><b>Официальный ответ</b></div>';
                    echo '<div class="q">'.bb_code($queryoff['offers_text_reply']).'<br />';
                    echo profile($queryoff['offers_user_reply']).' ('.date_fixed($queryoff['offers_time_reply']).')</div><br />';
                }
            } else {
                show_error('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?type='.$type.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Ответ на предложение                                  ##
        ############################################################################################
        case 'reply':

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `offers_id`=? LIMIT 1;", array($id));
            if (!empty($queryoff)) {

                echo '<div class="form">';
                echo '<form action="/admin/offers?act=answer&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Текст ответа: <br /><textarea cols="25" rows="5" name="text">'.$queryoff['offers_text_reply'].'</textarea><br />';

                echo 'Статус: <br />';

                $arrstatus = array('Под вопросом', 'В процессе', 'Выполнено', 'Закрыто');
                echo '<select name="status">';

                foreach ($arrstatus as $k => $v) {
                    $selected = ($k == $queryoff['offers_status']) ? ' selected="selected"' : '';

                    echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
                }
                echo '</select><br />';

                echo 'Закрыть комментарии: ';
                $checked = ($queryoff['offers_closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                echo '<input type="submit" value="Отправить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=view&amp;id='.$id.'">Вернуться</a><br />';
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
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `offers_id`=? LIMIT 1;", array($id));
                if (!empty($queryoff)) {
                    if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {
                        if ($status >= 0 && $status <= 3) {

                            $text = antimat($text);

                            DB::run() -> query("UPDATE `offers` SET `offers_status`=?, `offers_closed`=?, `offers_text_reply`=?, `offers_user_reply`=?, `offers_time_reply`=? WHERE `offers_id`=?;", array($status, $closed, $text, $log, SITETIME, $id));

                            if ($queryoff['offers_status'] >= 2) {
                                DB::run() -> query("DELETE FROM `ratedoffers` WHERE `rated_offers`=?;", array($id));
                            }

                            notice('Данные успешно отправлены!');
                            redirect("/admin/offers?act=view&id=$id");
                        } else {
                            show_error('Ошибка! Недопустимый статус предложения или проблемы!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный или короткий текст ответа (От 5 до 1000 символов)!');
                    }
                } else {
                    show_error('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=reply&amp;id='.$id.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Редактирование предложения                             ##
        ############################################################################################
        case 'edit':

            $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `offers_id`=? LIMIT 1;", array($id));
            if (!empty($queryoff)) {

                echo '<div class="form">';
                echo '<form action="/admin/offers?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Тип:<br />';
                echo '<select name="types">';
                $selected = ($queryoff['offers_type'] == 0) ? ' selected="selected"' : '';
                echo '<option value="0"'.$selected.'>Предложение</option>';
                $selected = ($queryoff['offers_type'] == 1) ? ' selected="selected"' : '';
                echo '<option value="1"'.$selected.'>Проблема</option>';
                echo '</select><br />';

                echo 'Заголовок: <br /><input type="text" name="title" value="'.$queryoff['offers_title'].'" /><br />';
                echo 'Описание: <br /><textarea cols="25" rows="5" name="text">'.$queryoff['offers_text'].'</textarea><br />';

                echo 'Закрыть комментарии: ';
                $checked = ($queryoff['offers_closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данного предложения или проблемы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=view&amp;id='.$id.'">Вернуться</a><br />';
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
                $queryoff = DB::run() -> queryFetch("SELECT * FROM `offers` WHERE `offers_id`=? LIMIT 1;", array($id));
                if (!empty($queryoff)) {
                    if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                        if (utf_strlen($text) >= 5 && utf_strlen($text) <= 1000) {

                            $title = antimat($title);
                            $text = antimat($text);

                            DB::run() -> query("UPDATE `offers` SET `offers_type`=?, `offers_closed`=?, `offers_title`=?, `offers_text`=? WHERE `offers_id`=?;", array($types, $closed, $title, $text, $id));

                            notice('Данные успешно отредактированы!');
                            redirect("/admin/offers?act=view&id=$id");
                        } else {
                            show_error('Ошибка! Слишком длинное или короткое описание (От 5 до 1000 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинный или короткий заголовок (От 5 до 50 символов)!');
                    }
                } else {
                    show_error('Ошибка! Данного предложения или проблемы не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?act=edit&amp;id='.$id.'">Вернуться</a><br />';
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

                    DB::run() -> query("DELETE FROM `offers` WHERE `offers_id` IN (".$del.");");
                    DB::run() -> query("DELETE FROM `commoffers` WHERE `comm_offers` IN (".$del.");");
                    DB::run() -> query("DELETE FROM `ratedoffers` WHERE `rated_offers` IN (".$del.");");

                    notice('Выбранные пункты успешно удалены!');
                    redirect("/admin/offers?type=$type&start=$start");
                } else {
                    show_error('Ошибка! Отсутствуют выбранные предложения или проблемы!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers?start='.$start.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Пересчет комментариев                                 ##
        ############################################################################################
        case 'rest':

            $uid = (isset($_GET['uid'])) ? check($_GET['uid']) : '';

            if (is_admin(array(101))) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("UPDATE `offers` SET `offers_comments`=(SELECT count(*) FROM `commoffers` WHERE `offers`.`offers_id`=`commoffers`.`comm_offers`);");

                    notice('Комментарии успешно пересчитаны!');
                    redirect("/admin/offers");
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Пересчитывать комментарии могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers">Вернуться</a><br />';
            break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
	redirect('/');
}

App::view($config['themes'].'/foot');
