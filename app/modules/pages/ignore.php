<?php
view(setting('themes').'/index');

$page = abs(intval(Request::input('page', 1)));

if (! isUser()) abort(403);

//show_title('Игнор-лист');

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Ignore::where('user_id', getUserId())->count();
    $page = paginate(setting('ignorlist'), $total);

    $ignores = Ignore::where('user_id', getUserId())
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('ignoring')
        ->get();

    if ($ignores->isNotEmpty()) {

        echo '<form action="/ignore/delete?page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        foreach ($ignores as $data) {
            echo '<div class="b">';
            echo '<div class="img">'.userAvatar($data->ignoring).'</div>';

            echo '<b>'.profile($data->ignoring).'</b> <small>('.dateFixed($data['created_at']).')</small><br>';
            echo userStatus($data->ignoring).' '.userOnline($data->ignoring).'</div>';

            echo '<div>';
            if ($data['text']) {
                echo 'Заметка: '.$data['text'].'<br>';
            }

            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> ';
            echo '<a href="/private/send?user='.$data->ignoring->login.'">Написать</a> | ';
            echo '<a href="/ignore/note/'.$data['id'].'">Заметка</a>';
            echo '</div>';
        }

        echo '<br><input type="submit" value="Удалить выбранное"></form>';

        pagination($page);

        echo 'Всего в игноре: <b>'.$page['total'].'</b><br>';
    } else {
        showError('Игнор-лист пуст!');
    }

    echo '<br><div class="form"><form method="post" action="/ignore/create">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
    echo '<b>Логин:</b><br><input name="user">';
    echo '<input value="Добавить" type="submit"></form></div><br>';
    break;

############################################################################################
##                                 Добавление пользователей                               ##
############################################################################################
case 'create':

    $token = check(Request::input('token'));
    $login = check(Request::input('user'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

    $user = User::where('login', $login)->first();
    $validation->addRule('not_empty', $user, 'Данного пользователя не существует!');

    if ($user) {
        $validation->addRule('not_equal', [$user->login, getUsername()], 'Запрещено добавлять свой логин!');

        $totalIgnore = Ignore::where('user_id', getUserId())->count();
        $validation->addRule('min', [$totalIgnore, setting('limitignore')], 'Ошибка! Игнор-лист переполнен (Максимум ' . setting('limitignore') . ' пользователей!)');

        $validation->addRule('custom', ! isIgnore(user(), $user), 'Данный пользователь уже есть в игнор-листе!');

        $validation->addRule('custom', ! in_array($user->level, [101, 102, 103, 105]), 'Запрещено добавлять в игнор администрацию сайта');
    }

    if ($validation->run()) {

        DB::insert("INSERT INTO `ignoring` (`user_id`, `ignore_id`, `created_at`) VALUES (?, ?, ?);", [getUserId(), $user->id, SITETIME]);

        if (! isIgnore($user, user())) {
            $message = 'Пользователь [b]' . getUsername() . '[/b] добавил вас в свой игнор-лист!';
            sendPrivate($user->id, getUserId(), $message);
        }

        setFlash('success', 'Пользователь успешно добавлен в игнор-лист!');
    } else {
        setInput(Request::all());
        setFlash('danger', $validation->getErrors());
    }

    redirect('/ignore?page='.$page);
break;

############################################################################################
##                                    Изменение заметки                                   ##
############################################################################################
case 'note':

    $id = param('id');

    $ignore = Ignore::where('user_id', getUserId())
        ->where('id', $id)
        ->first();

    if (! $ignore) {
        abort('default', 'Запись не найдена');
    }

    if (Request::isMethod('post')) {

        $token = check(Request::input('token'));
        $msg = check(Request::input('msg'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $msg, ['msg' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

        if ($validation->run()) {

            DB::update("UPDATE ignoring SET text=? WHERE id=? AND user_id=?;", [$msg, $id, getUserId()]);

            setFlash('success', 'Заметка успешно отредактирована!');
            redirect("/ignore");
        } else {
            setInput(Request::all());
            setFlash('danger', $validation->getErrors());
        }
    }

    echo '<i class="fa fa-pencil"></i> Заметка для пользователя <b>'.$ignore->ignoring->login.'</b> '.userOnline($ignore->ignoring).':<br><br>';

    echo '<div class="form">';
    echo '<form method="post" action="/ignore/note/'.$id.'">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
    echo 'Заметка:<br>';
    echo '<textarea cols="25" rows="5" name="msg" id="markItUp">'.$ignore['text'].'</textarea><br>';
    echo '<input value="Редактировать" type="submit"></form></div><br>';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/ignore?page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                                   Удаление пользователей                               ##
############################################################################################
case 'delete':

    $token = check(Request::input('token'));
    $del = intar(Request::input('del'));

    $validation = new Validation();
    $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        ->addRule('bool', $del, 'Ошибка удаления! Отсутствуют выбранные сообщения');

    if ($validation->run()) {

        $del = implode(',', $del);
        DB::delete("DELETE FROM ignoring WHERE `id` IN (".$del.") AND `user_id`=?;", [getUserId()]);

        setFlash('success', 'Выбранные пользователи успешно удалены!');
    } else {
        setFlash('danger', $validation->getErrors());
    }

    redirect("/ignore?page=$page");
    break;

endswitch;

echo '<i class="fa fa-users"></i> <a href="/contact">Контакт-лист</a><br>';
echo '<i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br>';

view(setting('themes').'/foot');
