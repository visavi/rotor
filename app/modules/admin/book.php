<?php
App::view(Setting::get('themes').'/index');

$act = check(Request::input('act', 'index'));
$page = abs(intval(Request::input('page')));
$id = abs(intval(Request::input('id')));

if (is_admin()) {
    //show_title('Управление гостевой');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/book?page='.$page.'">Обзор</a><br /><hr />';

            $total = Guest::count();
            if ($total > 0) {

                $page = App::paginate(Setting::get('bookpost'), $total);

                $posts = Guest::orderBy('created_at', 'desc')
                    ->limit(Setting::get('bookpost'))
                    ->offset($page['offset'])
                    ->with('user')
                    ->get();

                echo '<form action="/admin/book?act=del&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'" />';

                foreach($posts as $data) {

                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data->user).'</div>';

                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';

                    if (empty($data['user_id'])) {
                        echo '<b>'.$data->getUser()->login.'</b> <small>('.date_fixed($data['created_at']).')</small>';
                    } else {
                        echo '<b>'.profile($data->user).'</b> <small>('.date_fixed($data['created_at']).')</small><br />';
                        echo user_title($data->user).' '.user_online($data->user);
                    }

                    echo '</div>';

                    echo '<div class="right">';
                    echo '<a href="/admin/book?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a> / ';
                    echo '<a href="/admin/book?act=reply&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Ответить</a></div>';

                    echo '<div>'.App::bbCode($data['text']).'<br />';

                    if (!empty($data['edit_user_id'])) {
                        echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.$data->getEditUser()->login.' ('.date_fixed($data['updated_at']).')</small><br />';
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
                    echo '<i class="fa fa-times"></i> <a href="/admin/book?act=alldel&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы уверены что хотите удалить все сообщения?\')">Очистить</a><br />';
                }
            } else {
                show_error('Сообщений еще нет!');
            }
        break;

        ############################################################################################
        ##                                        Ответ                                           ##
        ############################################################################################
        case 'reply':

            $post = Guest::with('user')->find($id);

            if ($post) {
                echo '<b>Добавление ответа</b><br /><br />';

                echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post->user).'</b> '.user_title($post->user) . user_online($post->user).' <small>('.date_fixed($post['created_at']).')</small></div>';
                echo '<div>Сообщение: '.App::bbCode($post['text']).'</div><hr />';

                echo '<div class="form">';
                echo '<form action="/admin/book?id='.$id.'&amp;act=addreply&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br />';
                echo '<textarea cols="25" rows="5" name="reply">'.$post['reply'].'</textarea>';
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
                if (utf_strlen($reply) >= 5 && utf_strlen($reply) < Setting::get('guesttextlength')) {

                    $post = Guest::find($id);

                    if ($post) {

                        $post->reply = $reply;
                        $post->save();

                        App::setFlash('success', 'Ответ успешно добавлен!');
                        App::redirect("/admin/book?page=$page");
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

            $post = Guest::with('user')->find($id);

            if ($post) {

                echo '<b>Редактирование сообщения</b><br /><br />';

                echo '<i class="fa fa-pencil"></i> <b>'.$post->getUser()->login.'</b> <small>('.date_fixed($post['created_at']).')</small><br /><br />';

                echo '<div class="form">';
                echo '<form action="/admin/book?act=addedit&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br />';
                echo '<textarea cols="50" rows="5" name="msg">'.$post['text'].'</textarea><br /><br />';
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
                if (utf_strlen(trim($msg)) >= 5 && utf_strlen($msg) < Setting::get('guesttextlength')) {

                    $post = Guest::find($id);
                    if ($post) {

                        $post->text = $msg;
                        $post->edit_user_id = App::getUserId();
                        $post->updated_at = SITETIME;
                        $post->save();

                        App::setFlash('success', 'Сообщение успешно отредактировано!');
                        App::redirect("/admin/book?page=$page");
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

            $token = check(Request::input('token'));
            $postIds = intar(Request::input('del'));

            if ($token == $_SESSION['token']) {
                if ($postIds) {

                    Guest::whereIn('id', $postIds)->delete();

                    App::setFlash('success', 'Выбранные сообщения успешно удалены!');
                    App::redirect("/admin/book?page=$page");
                } else {
                    show_error('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Очистка гостевой                                     ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if (is_admin([101])) {
                if ($uid == $_SESSION['token']) {
                    Guest::truncate();

                    App::setFlash('success', 'Гостевая книга успешно очищена!');
                    App::redirect("/admin/book");
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
    App::redirect('/');
}

App::view(Setting::get('themes').'/foot');
