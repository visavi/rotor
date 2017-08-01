<?php

//$uz = check(Request::input('uz'));
$gid = param('gid');
$page = abs(intval(Request::input('page', 1)));

switch ($act):
/**
 * Главная страница
 */
case 'index':

    $total = Photo::count();
    $page = App::paginate(App::setting('fotolist'), $total);

    $photos = Photo::orderBy('created_at', 'desc')
        ->offset($page['offset'])
        ->limit(App::setting('fotolist'))
        ->with('user')
        ->get();

    App::view('gallery/index', compact('photos', 'page', 'total'));
break;

/**
 * Просмотр полной фотографии
 */
case 'view':
    $photo = Photo::select('photo.*', 'pollings.vote')
        ->where('photo.id', $gid)
        ->leftJoin ('pollings', function($join) {
            $join->on('photo.id', '=', 'pollings.relate_id')
                ->where('pollings.relate_type', Photo::class)
                ->where('pollings.user_id', App::getUserId());
        })
        ->with('user')
        ->first();

    if (! $photo) {
        App::abort('default', 'Фотография не найдена');
    }

    App::view('gallery/view', compact('photo', 'page'));
break;

/**
 * Форма загрузки фото
 */
case 'create':

    if (! is_user()) {
        App::abort(403, 'Для добавления фотографий небходимо авторизоваться!');
    }

    if (Request::isMethod('post')) {

        $token = check(Request::input('token'));
        $title = check(Request::input('title'));
        $text = check(Request::input('text'));
        $closed = Request::has('closed') ? 1 : 0;


        $validation = new Validation();
        $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('string', $title, ['title' => 'Слишком длинное или короткое название!'], true, 5, 50)
            ->addRule('string', $text, ['text' => 'Слишком длинное описание!'], true, 0, 1000)
            ->addRule('bool', is_flood(App::getUsername()), ['text' => 'Антифлуд! Разрешается отправлять сообщения раз в '.flood_period().' секунд!']);

        $handle = upload_image(
            $_FILES['photo'],
            App::setting('filesize'),
            App::setting('fileupfoto'),
            uniqid()
        );

        if (! $handle) {
            $validation -> addError(['photo' => 'Не удалось загрузить фотографию!']);
        }

        if ($validation->run()) {

            $handle -> process(HOME.'/uploads/pictures/');
            if ($handle->processed) {
                $photo = new Photo();
                $photo->user_id    = App::getUserId();
                $photo->title      = $title;
                $photo->text       = antimat($text);
                $photo->link       = $handle->file_dst_name;
                $photo->created_at = SITETIME;
                $photo->closed     = $closed;
                $photo->save();

                App::setFlash('success', 'Фотография успешно загружена!');
                App::redirect('/gallery/'.$photo->id);
            } else {
                $validation -> addError(['photo' => $handle->error]);
            }
        }

        App::setInput(Request::all());
        App::setFlash('danger', $validation->getErrors());
    }

    App::view('gallery/create');
break;

/**
 * Редактирование фото
 */
case 'edit':

    if (is_user()) {
        $photo = Photo::where('user_id', App::getUserId())->find($gid);

        if (!empty($photo)) {

            echo '<div class="form">';
            echo '<form action="/gallery?act=change&amp;gid='.$gid.'&amp;page='.$page.'" method="post">';
            echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
            echo 'Название: <br /><input type="text" name="title" value="'.$photo['title'].'" /><br />';
            echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text">'.$photo['text'].'</textarea><br />';

            echo 'Закрыть комментарии: ';
            $checked = ($photo['closed'] == 1) ? ' checked="checked"' : '';
            echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

            echo '<input type="submit" value="Изменить" /></form></div><br />';
        } else {
            show_error('Ошибка! Фотография удалена или вы не автор этой фотографии!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать фотографию, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;uz='.$uz.'&amp;page='.$page.'">Альбом</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
break;

/**
 * Изменение описания
 */
case 'change':

    $token = check(Request::input('token'));
    $title = check(Request::input('title'));
    $text = check(Request::input('text'));
    $closed = Request::has('closed') ? 1 : 0;

    if ($token == $_SESSION['token']) {
        if (is_user()) {
            $photo = Photo::where('user_id', App::getUserId())->find($gid);

            if (!empty($photo)) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) <= 1000) {

                        $text = antimat($text);

                        DB::run() -> query("UPDATE `photo` SET `title`=?, `text`=?, `closed`=? WHERE `id`=?;", [$title, $text, $closed, $gid]);

                        App::setFlash('success', 'Фотография успешно отредактирована!');
                        App::redirect("/gallery/album?act=photo&uz=$uz&page=$page");

                    } else {
                        show_error('Ошибка! Слишком длинное описание (Необходимо до 1000 символов)!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинное или короткое название!');
                }
            } else {
                show_error('Ошибка! Фотография удалена или вы не автор этой фотографии!');
            }
        } else {
            show_login('Вы не авторизованы, чтобы редактировать фотографию, необходимо');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery?act=edit&amp;gid='.$gid.'&amp;page='.$page.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
break;

/**
 * Список комментариев
 */
case 'comments':

    $photo = Photo::find($gid);

    if (! $photo) {
        App::abort('default', 'Фотография не найдена');
    }

    $total = Comment::where('relate_type', Photo::class)
        ->where('relate_id', $gid)
        ->count();
    $page = App::paginate(App::setting('postgallery'), $total);

    $comments = Comment::where('relate_type', Photo::class)
        ->where('relate_id', $gid)
        ->offset($page['offset'])
        ->limit($page['limit'])
        ->orderBy('created_at')
        ->with('user')
        ->get();

    $isAdmin = is_admin();

    App::view('gallery/comments', compact('photo', 'comments', 'page', 'isAdmin'));
break;


/**
 * Запись комментариев
 */
case 'addcomm':

    $token = check(Request::input('token'));
    $msg = check(Request::input('msg'));

    //App::setting('newtitle') = 'Добавление комментария';

    if (is_user()) {
        if ($token == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= 1000) {
                $data = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `id`=? LIMIT 1;", [$gid]);

                if (!empty($data)) {
                    if (empty($data['closed'])) {
                        if (is_flood(App::getUsername())) {
                            $msg = antimat($msg);

                            DB::run() -> query("INSERT INTO `comments` (relate_type, `relate_id`, `text`, `user_id`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", [Photo::class, $gid, $msg, App::getUserId(), SITETIME, App::getClientIp(), App::getUserAgent()]);

                            DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `created_at` DESC LIMIT ".App::setting('maxpostgallery').") AS del);", [Photo::class, $gid, Photo::class, $gid]);

                            DB::run() -> query("UPDATE `photo` SET `comments`=`comments`+1 WHERE `id`=?;", [$gid]);
                            DB::run() -> query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `id`=?", [App::getUserId()]);

                            App::setFlash('success', 'Комментарий успешно добавлен!');
                            App::redirect("/gallery?act=end&gid=$gid");

                        } else {
                            show_error('Антифлуд! Разрешается отправлять комментарии раз в '.flood_period().' секунд!');
                        }
                    } else {
                        show_error('Ошибка! Комментирование данной фотографии запрещено!');
                    }
                } else {
                    show_error('Ошибка! Данного изображения не существует!');
                }
            } else {
                show_error('Ошибка! Слишком длинный или короткий комментарий!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery">В галерею</a><br />';
break;

/**
 * Подготовка к редактированию
 */
 case 'editcomm':

    $cid = abs(intval(Request::input('cid')));

    if (is_user()) {
        $comm = Comment::select('comments.*', 'photo.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $cid)
            ->where('comments.user_id', App::getUserId())
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->first();

        if (!empty($comm)) {
            if (empty($comm['closed'])) {
                if ($comm['created_at'] + 600 > SITETIME) {

                    echo '<i class="fa fa-pencil"></i> <b>'.$comm->getUser()->login.'</b> <small>('.date_fixed($comm['created_at']).')</small><br /><br />';

                    echo '<div class="form">';
                    echo '<form action="/gallery?act=changecomm&amp;gid='.$comm['relate_id'].'&amp;cid='.$cid.'&amp;page='.$page.'" method="post">';
                    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                    echo '<textarea id="markItUp" cols="25" rows="5" name="msg" id="msg">'.$comm['text'].'</textarea><br />';
                    echo '<input type="submit" value="Редактировать" /></form></div><br />';

                } else {
                    show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!!');
                }
            } else {
                show_error('Ошибка! Редактирование невозможно, комментирование запрещено!');
            }
        } else {
            show_error('Ошибка! Комментарий удален или вы не автор этого комментария!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать комментарии, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'&amp;page='.$page.'">Вернуться</a><br />';
break;

/**
 * Редактирование комментария
 */
case 'changecomm':

    $cid = abs(intval(Request::input('cid')));
    $msg = check(Request::input('msg'));
    $token = check(Request::input('token'));

    if (is_user()) {
        if ($token == $_SESSION['token']) {
            if (utf_strlen($msg) >= 5 && utf_strlen($msg) <= 1000) {

                $comm = Comment::select('comments.*', 'photo.closed')
                    ->where('relate_type', Photo::class)
                    ->where('comments.id', $cid)
                    ->where('comments.user_id', App::getUserId())
                    ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
                    ->first();

                if (!empty($comm)) {
                    if (empty($comm['closed'])) {
                        if ($comm['created_at'] + 600 > SITETIME) {

                            $msg = antimat($msg);

                            DB::run() -> query("UPDATE `comments` SET `text`=? WHERE relate_type=? AND `id`=?;", [$msg, Photo::class, $cid]);

                            App::setFlash('success', 'Комментарий успешно отредактирован!');
                            App::redirect("/gallery?act=comments&gid=$gid&page=$page");

                        } else {
                            show_error('Ошибка! Редактирование невозможно, прошло более 10 минут!');
                        }
                    } else {
                        show_error('Ошибка! Редактирование невозможно, комментирование запрещено!');
                    }
                } else {
                    show_error('Ошибка! Комментарий удален или вы не автор этого комментария!');
                }
            } else {
                show_error('Ошибка! Слишком длинный или короткий комментарий!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы редактировать комментарии, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=editcomm&amp;gid='.$gid.'&amp;cid='.$cid.'&amp;page='.$page.'">Вернуться</a><br />';
break;

/**
 * Удаление комментариев
 */
case 'delcomm':

    $token = check(Request::input('token'));
    $del   = intar(Request::input('del'));

    if (is_admin()) {
        if ($token == $_SESSION['token']) {
            if (!empty($del)) {
                $del = implode(',', $del);

                $delcomments = DB::run() -> exec("DELETE FROM comments WHERE relate_type=Photo::class AND id IN (".$del.") AND relate_id=".$gid.";");
                DB::run() -> query("UPDATE photo SET comments=comments-? WHERE id=?;", [$delcomments, $gid]);

                App::setFlash('success', 'Выбранные комментарии успешно удалены!');
                App::redirect("/gallery?act=comments&gid=$gid&page=$page");

            } else {
                show_error('Ошибка! Отстутствуют выбранные комментарии для удаления!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Удалять комментарии могут только модераторы!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=comments&amp;gid='.$gid.'">Вернуться</a><br />';
break;



/**
 * Удаление фотографий
 */
case 'delphoto':

    $token = check(Request::input('token'));

    if (is_user()) {
        if ($token == $_SESSION['token']) {
            if (is_writeable(HOME.'/uploads/pictures')) {
                $querydel = DB::run() -> queryFetch("SELECT `id`, `link`, `comments` FROM `photo` WHERE `id`=? AND `user_id`=? LIMIT 1;", [$gid, App::getUserId()]);
                if (!empty($querydel)) {
                    if (empty($querydel['comments'])) {
                        DB::run() -> query("DELETE FROM `photo` WHERE `id`=? LIMIT 1;", [$querydel['id']]);
                        DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=?;", [Photo::class, $querydel['id']]);

                        unlink_image('uploads/pictures/', $querydel['link']);

                        App::setFlash('success', 'Фотография успешно удалена!');
                        App::redirect("/gallery/album?act=photo&page=$page");

                    } else {
                        show_error('Ошибка! Запрещено удалять фотографии к которым имеются комментарии!');
                    }
                } else {
                    show_error('Ошибка! Данная фотография не существует или вы не автор этой фотографии!');
                }
            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы удалять фотографии, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery/album?act=photo&amp;page='.$page.'">Вернуться</a><br />';
break;


/**
 * Переадресация на последнюю страницу
 */
case 'end':

    $photo = Photo::find($gid);

    if (empty($photo)) {
        App::abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
    }

    $total = Comment::where('relate_type', Photo::class)
        ->where('relate_id', $gid)
        ->count();

    $end = ceil($total / App::setting('postgallery'));
    App::redirect('/gallery/'.$gid.'/comments?page='.$end);
break;
endswitch;
