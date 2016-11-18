<?php
App::view($config['themes'].'/index');

$act = check(Request::input('act', 'index'));
$page = abs(intval(Request::input('page')));
$id = abs(intval(Request::input('id')));

if (is_admin()) {
    show_title('Управление гостевой');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/book?page='.$page.'">Обзор</a><br /><hr />';

            $total = DB::run() -> querySingle("SELECT count(*) FROM guest;");

            if ($total > 0) {

                $page = App::paginate(App::setting('bookpost'), $total);

                $queryguest = DB::run() -> query("SELECT * FROM guest ORDER BY time DESC LIMIT ".$page['offset'].", ".$config['bookpost'].";");

                echo '<form action="/admin/book?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

                while ($data = $queryguest -> fetch()) {

                    echo '<div class="b">';
                    echo '<div class="img">'.user_avatars($data['user']).'</div>';

                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';

                    if ($data['user'] == $config['guestsuser']) {
                        echo '<b>'.$data['user'].'</b> <small>('.date_fixed($data['time']).')</small>';
                    } else {
                        echo '<b>'.profile($data['user']).'</b> <small>('.date_fixed($data['time']).')</small><br />';
                        echo user_title($data['user']).' '.user_online($data['user']);
                    }

                    echo '</div>';

                    echo '<div class="right">';
                    echo '<a href="/admin/book?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a> / ';
                    echo '<a href="/admin/book?act=reply&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Ответить</a></div>';

                    echo '<div>'.App::bbCode($data['text']).'<br />';

                    if (!empty($data['edit'])) {
                        echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.nickname($data['edit']).' ('.date_fixed($data['edit_time']).')</small><br />';
                    }

                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';

                    if (!empty($data['reply'])) {
                        echo '<br /><span style="color:#ff0000">Ответ: '.$data['reply'].'</span>';
                    }

                    echo '</div>';
                }
                echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';

                App::pagination($page);

                echo 'Всего сообщений: <b>'.$total.'</b><br /><br />';

                if (is_admin([101])) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/book?act=prodel">Очистить</a><br />';
                }
            } else {
                show_error('Сообщений еще нет!');
            }
        break;

        ############################################################################################
        ##                                        Ответ                                           ##
        ############################################################################################
        case 'reply':

            $data = DB::run() -> queryFetch("SELECT * FROM guest WHERE id=? LIMIT 1;", [$id]);

            if (!empty($data)) {
                echo '<b><big>Добавление ответа</big></b><br /><br />';

                echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($data['user']).'</b> '.user_title($data['user']) . user_online($data['user']).' <small>('.date_fixed($data['time']).')</small></div>';
                echo '<div>Сообщение: '.App::bbCode($data['text']).'</div><hr />';

                echo '<div class="form">';
                echo '<form action="/admin/book?id='.$id.'&amp;act=addreply&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br />';
                echo '<textarea cols="25" rows="5" name="reply">'.$data['reply'].'</textarea>';
                echo '<br /><input type="submit" value="Ответить" /></form></div><br />';
            } else {
                show_error('Ошибка! Сообщения для ответа не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Добавление ответа                                     ##
        ############################################################################################
        case 'addreply':

            $uid = check($_GET['uid']);
            $reply = check($_POST['reply']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($reply) >= 5 && utf_strlen($reply) < $config['guesttextlength']) {
                    $queryguest = DB::run() -> querySingle("SELECT id FROM guest WHERE id=? LIMIT 1;", [$id]);
                    if (!empty($queryguest)) {

                        DB::run() -> query("UPDATE guest SET reply=? WHERE id=?", [$reply, $id]);

                        notice('Ответ успешно добавлен!');
                        redirect("/admin/book?page=$page");
                    } else {
                        show_error('Ошибка! Сообщения для ответа не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий текст ответа!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?act=reply&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/book?page='.$page.'">В гостевую</a><br />';
        break;

        ############################################################################################
        ##                                    Редактирование                                      ##
        ############################################################################################
        case 'edit':

            $data = DB::run() -> queryFetch("SELECT * FROM guest WHERE id=? LIMIT 1;", [$id]);

            if (!empty($data)) {

                echo '<b><big>Редактирование сообщения</big></b><br /><br />';

                echo '<i class="fa fa-pencil"></i> <b>'.nickname($data['user']).'</b> <small>('.date_fixed($data['time']).')</small><br /><br />';

                echo '<div class="form">';
                echo '<form action="/admin/book?act=addedit&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br />';
                echo '<textarea cols="50" rows="5" name="msg">'.$data['text'].'</textarea><br /><br />';
                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Сообщения для редактирования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Изменение сообщения                                    ##
        ############################################################################################
        case 'addedit':

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen(trim($msg)) >= 5 && utf_strlen($msg) < $config['guesttextlength']) {
                    $queryguest = DB::run() -> querySingle("SELECT id FROM guest WHERE id=? LIMIT 1;", [$id]);
                    if (!empty($queryguest)) {

                        DB::run() -> query("UPDATE guest SET text=?, edit=?, edit_time=? WHERE id=?", [$msg, $log, SITETIME, $id]);

                        notice('Сообщение успешно отредактировано!');
                        redirect("/admin/book?page=$page");
                    } else {
                        show_error('Ошибка! Сообщения для редактирования не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий текст сообщения!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Удаление сообщений                                     ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);
            if (isset($_POST['del'])) {
                $del = intar($_POST['del']);
            } else {
                $del = 0;
            }

            if ($uid == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    DB::run() -> query("DELETE FROM guest WHERE id IN (".$del.");");

                    notice('Выбранные сообщения успешно удалены!');
                    redirect("/admin/book?page=$page");
                } else {
                    show_error('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                 Подтверждение очистки                                  ##
        ############################################################################################
        case 'prodel':
            echo 'Вы уверены что хотите удалить все сообщения в гостевой?<br />';
            echo '<i class="fa fa-times"></i> <b><a href="/admin/book?act=alldel&amp;uid='.$_SESSION['token'].'">Да, уверен!</a></b><br /><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Очистка гостевой                                     ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    DB::run() -> query("DELETE FROM guest;");

                    notice('Гостевая книга успешно очищена!');
                    redirect("/admin/book");
                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Очищать гостевую могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book">Вернуться</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
