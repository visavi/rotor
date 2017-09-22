<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$page = abs(intval(Request::input('page')));
$id = abs(intval(Request::input('id')));

if (isAdmin()) {
    //show_title('Управление гостевой');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/book?page='.$page.'">Обзор</a><br><hr>';

            $total = Guest::count();
            if ($total > 0) {

                $page = paginate(setting('bookpost'), $total);

                $posts = Guest::orderBy('created_at', 'desc')
                    ->limit(setting('bookpost'))
                    ->offset($page['offset'])
                    ->with('user')
                    ->get();

                echo '<form action="/admin/book?act=del&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                foreach($posts as $data) {

                    echo '<div class="b">';
                    echo '<div class="img">'.userAvatar($data->user).'</div>';

                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';

                    if (empty($data['user_id'])) {
                        echo '<b>'.$data->user->login.'</b> <small>('.dateFixed($data['created_at']).')</small>';
                    } else {
                        echo '<b>'.profile($data->user).'</b> <small>('.dateFixed($data['created_at']).')</small><br>';
                        echo userStatus($data->user).' '.userOnline($data->user);
                    }

                    echo '</div>';

                    echo '<div class="right">';
                    echo '<a href="/admin/book?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a> / ';
                    echo '<a href="/admin/book?act=reply&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Ответить</a></div>';

                    echo '<div>'.bbCode($data['text']).'<br>';

                    if (!empty($data['edit_user_id'])) {
                        echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.$data->editUser->login.' ('.dateFixed($data['updated_at']).')</small><br>';
                    }

                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';

                    if (!empty($data['reply'])) {
                        echo '<br><span style="color:#ff0000">Ответ: '.$data['reply'].'</span>';
                    }

                    echo '</div>';
                }
                echo '<span class="imgright"><input type="submit" value="Удалить выбранное"></span></form>';

                pagination($page);

                echo 'Всего сообщений: <b>'.$total.'</b><br><br>';

                if (isAdmin([101])) {
                    echo '<i class="fa fa-times"></i> <a href="/admin/book?act=alldel&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы уверены что хотите удалить все сообщения?\')">Очистить</a><br>';
                }
            } else {
                showError('Сообщений еще нет!');
            }
        break;

        ############################################################################################
        ##                                        Ответ                                           ##
        ############################################################################################
        case 'reply':

            $post = Guest::with('user')->find($id);

            if ($post) {
                echo '<b>Добавление ответа</b><br><br>';

                echo '<div class="b"><i class="fa fa-pencil"></i> <b>'.profile($post->user).'</b> '.userStatus($post->user) . userOnline($post->user).' <small>('.dateFixed($post['created_at']).')</small></div>';
                echo '<div>Сообщение: '.bbCode($post['text']).'</div><hr>';

                echo '<div class="form">';
                echo '<form action="/admin/book?id='.$id.'&amp;act=addreply&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br>';
                echo '<textarea cols="25" rows="5" name="reply">'.$post['reply'].'</textarea>';
                echo '<br><input type="submit" value="Ответить"></form></div><br>';
            } else {
                showError('Ошибка! Сообщения для ответа не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Добавление ответа                                     ##
        ############################################################################################
        case 'addreply':

            $uid = check($_GET['uid']);
            $reply = check($_POST['reply']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen($reply) >= 5 && utfStrlen($reply) < setting('guesttextlength')) {

                    $post = Guest::find($id);

                    if ($post) {

                        $post->reply = $reply;
                        $post->save();

                        setFlash('success', 'Ответ успешно добавлен!');
                        redirect("/admin/book?page=$page");
                    } else {
                        showError('Ошибка! Сообщения для ответа не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий текст ответа!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?act=reply&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/book?page='.$page.'">В гостевую</a><br>';
        break;

        ############################################################################################
        ##                                    Редактирование                                      ##
        ############################################################################################
        case 'edit':

            $post = Guest::with('user')->find($id);

            if ($post) {

                echo '<b>Редактирование сообщения</b><br><br>';

                echo '<i class="fa fa-pencil"></i> <b>'.$post->user->login.'</b> <small>('.dateFixed($post['created_at']).')</small><br><br>';

                echo '<div class="form">';
                echo '<form action="/admin/book?act=addedit&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
                echo 'Cообщение:<br>';
                echo '<textarea cols="50" rows="5" name="msg">'.$post['text'].'</textarea><br><br>';
                echo '<input type="submit" value="Изменить"></form></div><br>';
            } else {
                showError('Ошибка! Сообщения для редактирования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Изменение сообщения                                    ##
        ############################################################################################
        case 'addedit':

            $uid = check($_GET['uid']);
            $msg = check($_POST['msg']);

            if ($uid == $_SESSION['token']) {
                if (utfStrlen(trim($msg)) >= 5 && utfStrlen($msg) < setting('guesttextlength')) {

                    $post = Guest::find($id);
                    if ($post) {

                        $post->text = $msg;
                        $post->edit_user_id = user('id');
                        $post->updated_at = SITETIME;
                        $post->save();

                        setFlash('success', 'Сообщение успешно отредактировано!');
                        redirect("/admin/book?page=$page");
                    } else {
                        showError('Ошибка! Сообщения для редактирования не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинный или короткий текст сообщения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
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

                    setFlash('success', 'Выбранные сообщения успешно удалены!');
                    redirect("/admin/book?page=$page");
                } else {
                    showError('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Очистка гостевой                                     ##
        ############################################################################################
        case 'alldel':

            $uid = check($_GET['uid']);

            if (isAdmin([101])) {
                if ($uid == $_SESSION['token']) {
                    Guest::truncate();

                    setFlash('success', 'Гостевая книга успешно очищена!');
                    redirect("/admin/book");
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Очищать гостевую могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/book">Вернуться</a><br>';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
