<?php
view(setting('themes').'/index');

$page = abs(intval(Request::input('page', 1)));

if (! isUser()) abort(403);

//show_title('Контакт-лист');

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    $total = Contact::where('user_id', getUserId())->count();
    $page = paginate(setting('contactlist'), $total);

    $contacts = Contact::where('user_id', getUserId())
        ->orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->with('contactor')
        ->get();

    if ($contacts->isNotEmpty()) {

        echo '<form action="/contact/delete?page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        foreach ($contacts as $contact) {
            echo '<div class="b">';
            echo '<div class="img">'.userAvatar($contact->contactor).'</div>';

            echo '<b>'.profile($contact->contactor).'</b> <small>('.dateFixed($contact['created_at']).')</small><br>';
            echo userStatus($contact->contactor).' '.userOnline($contact->contactor).'</div>';

            echo '<div>';
            if ($contact['text']) {
                echo 'Заметка: '.$contact['text'].'<br>';
            }

            echo '<input type="checkbox" name="del[]" value="'.$contact['id'].'"> ';
            echo '<a href="/private/send?user='.$contact->contactor->login.'">Написать</a> | ';
            echo '<a href="/transfer?uz='.$contact->contactor->login.'">Перевод</a> | ';
            echo '<a href="/contact/note/'.$contact['id'].'">Заметка</a>';
            echo '</div>';
        }

        echo '<br><input type="submit" value="Удалить выбранное"></form>';

        pagination($page);

        echo 'Всего в контактах: <b>'.$page['total'].'</b><br>';
    } else {
        showError('Контакт-лист пуст!');
    }

    echo '<br><div class="form"><form method="post" action="/contact/create">';
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

        $totalContact = Contact::where('user_id', getUserId())->count();
        $validation->addRule('min', [$totalContact, setting('limitcontact')], 'Ошибка! Контакт-лист переполнен (Максимум ' . setting('limitcontact') . ' пользователей!)');

        $validation->addRule('custom', ! isContact(user(), $user), 'Данный пользователь уже есть в контакт-листе!');
    }

    if ($validation->run()) {

            DB::insert("INSERT INTO `contact` (`user_id`, `contact_id`, `created_at`) VALUES (?, ?, ?);", [getUserId(), $user->id, SITETIME]);

            if (! isIgnore($user, user())) {

                $message = 'Пользователь [b]'.getUsername().'[/b] добавил вас в свой контакт-лист!';
                sendPrivate($user->id, getUserId(), $message);
            }

        setFlash('success', 'Пользователь успешно добавлен в контакт-лист!');

    } else {
        setInput(Request::all());
        setFlash('danger', $validation->getErrors());
    }

    redirect('/contact?page='.$page);
break;

############################################################################################
##                                    Изменение заметки                                   ##
############################################################################################
case 'note':

    $id = param('id');

    $contact = Contact::where('user_id', getUserId())
        ->where('id', $id)
        ->first();

    if (! $contact) {
        abort('default', 'Запись не найдена');
    }

    if (Request::isMethod('post')) {

        $token = check(Request::input('token'));
        $msg = check(Request::input('msg'));

        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
            ->addRule('string', $msg, ['msg' => 'Слишком большая заметка, не более 1000 символов!'], true, 0, 1000);

        if ($validation->run()) {

            DB::update("UPDATE contact SET text=? WHERE id=? AND user_id=?;", [$msg, $id, getUserId()]);

            setFlash('success', 'Заметка успешно отредактирована!');
            redirect("/contact");
        } else {
            setInput(Request::all());
            setFlash('danger', $validation->getErrors());
        }
    }

    echo '<i class="fa fa-pencil"></i> Заметка для пользователя <b>'.$contact->contactor->login.'</b> '.userOnline($contact->contactor).':<br><br>';

    echo '<div class="form">';
    echo '<form method="post" action="/contact/note/'.$id.'">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
    echo 'Заметка:<br>';
    echo '<textarea cols="25" rows="5" name="msg" id="markItUp">'.$contact['text'].'</textarea><br>';
    echo '<input value="Редактировать" type="submit"></form></div><br>';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/contact">Вернуться</a><br>';
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
        DB::delete("DELETE FROM contact WHERE id IN (".$del.") AND user_id=?;", [getUserId()]);

        setFlash('success', 'Выбранные пользователи успешно удалены!');
    } else {
        setFlash('danger', $validation->getErrors());
    }

    redirect("/contact?page=$page");
break;

endswitch;

echo '<i class="fa fa-ban"></i> <a href="/ignore">Игнор-лист</a><br>';
echo '<i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br>';

view(setting('themes').'/foot');
