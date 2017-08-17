<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : "";
$page = abs(intval(Request::input('page', 1)));

if (is_admin()) {

    //show_title('Управление мини-чатом');

    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
    if ($act == 'index') {
        echo '<a href="/admin/minichat?rand=' . mt_rand(100, 990) . '">Обновить</a> / ';
        echo '<a href="/chat?page=' . $page . '">Обзор</a><br><hr>';

        if (! file_exists(STORAGE."/chat/chat.dat")){
            touch(STORAGE."/chat/chat.dat");
        }

        $file = file(STORAGE."/chat/chat.dat");
        $file = array_reverse($file);
        $total = count($file);

        $page = App::paginate(Setting::get('chatpost'), $total);

        if ($total > 0) {
            echo '<form action="/admin/minichat?act=del&amp;page=' . $page['current'] . '&amp;uid=' . $_SESSION['token'] . '" method="post">';

            if ($total < $page['offset'] + Setting::get('chatpost')) {
                $end = $total;
            } else {
                $end = $page['offset'] + Setting::get('chatpost');
            }

            for ($i = $page['offset']; $i < $end; $i++) {
                $data = explode("|", $file[$i]);

                $num = $total - $i - 1;

                $useronline = user_online($data[1]);
                $useravatars = user_avatars($data[1]);
                $anketa = profile($data[1]);

                if ($data[1] == 'Вундер-киндер') {
                    $useravatars = '<img src="/assets/img/chat/mag.gif" alt="image"> ';
                    $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
                    $anketa = 'Вундер-киндер';
                }
                if ($data[1] == 'Настюха') {
                    $useravatars = '<img src="/assets/img/chat/bot.gif" alt="image"> ';
                    $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
                    $anketa = 'Настюха';
                }
                if ($data[1] == 'Весельчак') {
                    $useravatars = '<img src="/assets/img/chat/shut.gif" alt="image"> ';
                    $useronline = '<i class="fa fa-asterisk fa-spin text-success"></i>';
                    $anketa = 'Весельчак';
                }

                echo '<div class="b">';

                echo $useravatars;

                echo '<b>' . $anketa . '</b> ' . user_title($data[1]) . ' ' . $useronline . ' <small> (' . date_fixed($data[3]) . ')</small><br>';
                echo '<input type="checkbox" name="del[]" value="' . $num . '"> ';
                echo '<a href="/admin/minichat?act=edit&amp;id=' . $num . '&amp;page=' . $page['current'] . '">Редактировать</a>';

                echo '</div><div>' . App::bbCode($data[0]) . '<br>';
                echo '<span style="color:#cc00cc"><small>(' . $data[4] . ', ' . $data[5] . ')</small></span></div>';
            }

            echo '<br><input type="submit" value="Удалить выбранное"></form><br>';

            App::pagination($page);

            echo '<p>Всего сообщений: <b>' . (int)$total . '</b></p>';

            if (is_admin([101])) {
                echo '<i class="fa fa-times"></i> <a href="/admin/minichat?act=prodel">Очистить</a><br>';
            }
        } else {
            show_error('Сообщений еще нет!');
        }
    }
    # ###########################################################################################
    # #                                 Подтверждение очистки                                  ##
    # ###########################################################################################
    if ($act == "prodel") {
        echo '<br>Вы уверены что хотите удалить все сообщения в мини-чате?<br>';

        echo '<i class="fa fa-times"></i> <b><a href="/admin/minichat?act=alldel&amp;uid=' . $_SESSION['token'] . '">Да уверен!</a></b><br><br>';

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/minichat">Вернуться</a><br>';
    }
    # ###########################################################################################
    # #                                   Очистка мини-чата                                    ##
    # ###########################################################################################
    if ($act == "alldel") {
        $uid = check($_GET['uid']);

        if (is_admin([101])) {
            if ($uid == $_SESSION['token']) {
                clear_files(STORAGE."/chat/chat.dat");

                App::setFlash('success', 'Мини-чат успешно очищен!');
                App::redirect("/admin/minichat");

            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Очищать мини-чат могут только суперадмины!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/minichat">Вернуться</a><br>';
    }

    ############################################################################################
    ##                                 Удаление сообщений                                     ##
    ############################################################################################
    if ($act == "del") {
        $uid = check($_GET['uid']);
        $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : "";

        if ($uid == $_SESSION['token']) {
            if ($del !== "") {
                delete_lines(STORAGE."/chat/chat.dat", $del);

                App::setFlash('success', 'Выбранные сообщения успешно удалены!');
                App::redirect("/admin/minichat?page=$page");

            } else {
                show_error('Ошибка удаления! Отсутствуют выбранные сообщения');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/minichat?page=' . $page . '">Вернуться</a><br>';
    }

    ############################################################################################
    ##                                    Редактирование                                      ##
    ############################################################################################
    if ($act == "edit") {
        if ($id !== "") {
            $file = file(STORAGE."/chat/chat.dat");
            if (isset($file[$id])) {
                $data = explode("|", $file[$id]);

                $data[0] = yes_br($data[0]);

                //Setting::get('header') = 'Редактирование сообщения';

                echo '<div class="form"><form action="/admin/minichat?act=addedit&amp;id=' . $id . '&amp;page=' . $page . '&amp;uid=' . $_SESSION['token'] . '" method="post">';

                echo '<i class="fa fa-pencil"></i> <b>' . $data[1] . '</b> <small>(' . date_fixed($data[3]) . ')</small><br>';

                echo '<textarea id="markItUp" cols="25" rows="5" name="msg">' . $data[0] . '</textarea><br/>';
                echo '<input type="submit" value="Изменить"></form></div><br>';
            } else {
                show_error('Ошибка! Сообщения для редактирования не существует!');
            }
        } else {
            show_error('Ошибка! Не выбрано сообщение для редактирования!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/minichat?page=' . $page . '">Вернуться</a><br>';
    }
    # ###########################################################################################
    # #                                 Изменение сообщения                                    ##
    # ###########################################################################################
    if ($act == "addedit") {
        $uid = check($_GET['uid']);
        $msg = check($_POST['msg']);

        if ($uid == $_SESSION['token']) {
            if ($id !== "") {
                if ($msg != "") {
                    $file = file(STORAGE."/chat/chat.dat");
                    if (isset($file[$id])) {
                        $data = explode("|", $file[$id]);

                        $msg = no_br($msg);

                        $text = no_br($msg . '|' . $data[1] . '|' . $data[2] . '|' . $data[3] . '|' . $data[4] . '|' . $data[5] . '|' . $data[6] . '|' . $data[7] . '|' . $data[8] . '|');

                        replace_lines(STORAGE."/chat/chat.dat", $id, $text);

                        App::setFlash('success', 'Сообщение успешно отредактировано!');
                        App::redirect("/admin/minichat?page=$page");

                    } else {
                        show_error('Ошибка! Сообщения для редактирования не существует!');
                    }
                } else {
                    show_error('Ошибка! Вы не написали текст сообщения!');
                }
            } else {
                show_error('Ошибка! Не выбрано сообщение для редактирования!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/minichat?act=edit&amp;id=' . $id . '&amp;page=' . $page . '">Вернуться</a><br>';
    }
    // -------------------------------- КОНЦОВКА ----------------------------------//
    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    App::redirect("/");
}

App::view(Setting::get('themes').'/foot');
