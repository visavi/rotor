<?php

$uz = check(Request::input('uz'));
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
    $gid = param('gid');

    $photo = Photo::where('id', $gid)
        ->with('user')
        ->first();

    if (! $photo) {
        App::abort('default', 'Фотография не найдена');
    }

    App::view('gallery/view', compact('photo', 'page'));
break;

/**
 * Оценка фотографии
 */
case 'vote':

    $token = check(Request::input('token'));
    $vote = check(Request::input('vote'));

    if (is_user()) {
        if ($token == $_SESSION['token']) {
            if ($vote == 'up' || $vote == 'down') {

                $score = ($vote == 'up') ? 1 : -1;

                $data = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `id`=? LIMIT 1;", [$gid]);

                if (!empty($data)) {
                    if (App::getUserId() != $data['user_id']) {
                        $queryrated = DB::run() -> querySingle("SELECT `id` FROM `pollings` WHERE relate_type=? AND `relate_id`=? AND `user_id`=? LIMIT 1;", [Photo::class, $gid, App::getUserId()]);

                        if (empty($queryrated)) {
                            $expiresrated = SITETIME + 3600 * App::setting('photoexprated');

                            DB::run() -> query("DELETE FROM `pollings` WHERE relate_type=? AND created_at<?;", [Photo::class, SITETIME]);
                            DB::run() -> query("INSERT INTO `pollings` (relate_type, `relate_id`, `user_id`, `created_at`) VALUES (?, ?, ?, ?);", [Photo::class, $gid, App::getUserId(), $expiresrated]);
                            DB::run() -> query("UPDATE `photo` SET `rating`=`rating`+? WHERE `id`=?;", [$score, $gid]);

                            App::setFlash('success', 'Ваша оценка принята! Рейтинг фотографии: '.format_num($data['rating'] + $score));
                            App::redirect("/gallery?act=view&gid=$gid");

                        } else {
                            show_error('Ошибка! Вы уже оценивали данную фотографию!');
                        }
                    } else {
                        show_error('Ошибка! Нельзя голосовать за свои фотографии!');
                    }
                } else {
                    show_error('Ошибка! Данной фотографии не существует!');
                }
            } else {
                show_error('Ошибка! Необходимо проголосовать за или против фотографии!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, для голосования за фотографии, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=view&amp;gid='.$gid.'">Вернуться</a><br />';
break;

/**
 * Форма загрузки фото
 */
case 'addphoto':

    //App::setting('newtitle') = 'Добавление фотографии';

    if (is_user()) {
        echo '<div class="form">';
        echo '<form action="/gallery?act=add" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo 'Прикрепить фото:<br /><input type="file" name="photo" /><br />';
        echo 'Название: <br /><input type="text" name="title" /><br />';
        echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text"></textarea><br />';

        echo 'Закрыть комментарии: <input name="closed" type="checkbox" value="1" /><br />';

        echo '<input type="submit" value="Добавить" /></form></div><br />';

        echo 'Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br />';
        echo 'Весом не более '.formatsize(App::setting('filesize')).' и размером от 100 до '.(int)App::setting('fileupfoto').' px<br /><br />';
    } else {
        show_login('Вы не авторизованы, чтобы добавить фотографию, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Вернуться</a><br />';
break;

/**
 * Загрузка фото
 */
case 'add':

    //App::setting('newtitle') = 'Результат добавления';

    $token = check(Request::input('token'));
    $title = check(Request::input('title'));
    $text = check(Request::input('text'));
    $closed = Request::has('closed') ? 1 : 0;

    if (is_user()) {
        if ($token == $_SESSION['token']) {
            if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
                if (utf_strlen($title) >= 5 && utf_strlen($title) <= 50) {
                    if (utf_strlen($text) <= 1000) {
                        if (is_flood(App::getUserId())) {

                            $handle = upload_image(
                                $_FILES['photo'],
                                App::setting('filesize'),
                                App::setting('fileupfoto'),
                                uniqid()
                            );

                            if ($handle) {
                                $handle -> process(HOME.'/uploads/pictures/');
                                if ($handle -> processed) {

                                    $photo = new Photo();
                                    $photo->user_id = App::getUserId();
                                    $photo->title = $title;
                                    $photo->text = antimat($text);
                                    $photo->link = $handle->file_dst_name;
                                    $photo->created_at = SITETIME;
                                    $photo->closed = $closed;
                                    $photo->save();

                                    $handle -> clean();

                                    App::setFlash('success', 'Фотография успешно загружена!');
                                    App::redirect("/gallery");

                                } else {
                                    show_error($handle->error);
                                }
                            } else {
                                show_error('Ошибка! Не удалось загрузить изображение!');
                            }
                        } else {
                            show_error('Антифлуд! Вы слишком часто добавляете фотографии!');
                        }
                    } else {
                        show_error('Слишком длинное описание (Необходимо до 1000 символов)!');
                    }
                } else {
                    show_error('Слишком длинное или короткое название (Необходимо от 5 до 50 символов)!');
                }
            } else {
                show_error('Ошибка! Не удалось загрузить изображение!');
            }
        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_login('Вы не авторизованы, чтобы добавить фотографию, необходимо');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery?act=addphoto">Вернуться</a><br />';
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
    if ($photo) {
        //App::setting('newtitle') = 'Комментарии - '.$photo['title'];

        echo '<i class="fa fa-picture-o"></i> <b><a href="/gallery?act=view&amp;gid='.$photo['id'].'">'.$photo['title'].'</a></b><hr />';

        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();
        $page = App::paginate(App::setting('postgallery'), $total);

        if ($total > 0) {
            $is_admin = is_admin();
            if ($is_admin) {
                echo '<form action="/gallery?act=delcomm&amp;gid='.$gid.'&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
            }

            $comments = Comment::where('relate_type', Photo::class)
                ->where('relate_id', $gid)
                ->offset($page['offset'])
                ->limit($page['limit'])
                ->orderBy('created_at')
                ->with('user')
                ->get();

            foreach ($comments as $data) {

                echo '<div class="b">';
                echo '<div class="img">'.user_avatars($data->user).'</div>';

                if ($is_admin) {
                    echo '<span class="imgright"><input type="checkbox" name="del[]" value="'.$data['id'].'" /></span>';
                }

                echo '<b>'.profile($data->user).'</b> <small>('.date_fixed($data['created_at']).')</small><br />';
                echo user_title($data->user).' '.user_online($data->user).'</div>';

                if ($data->user_id == App::getUserId() && $data['created_at'] + 600 > SITETIME) {
                    echo '<div class="right"><a href="/gallery?act=editcomm&amp;gid='.$gid.'&amp;cid='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';
                }

                echo '<div>'.App::bbCode($data['text']).'<br />';

                if (is_admin() || empty(App::setting('anonymity'))) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            if ($is_admin) {
                echo '<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>';
            }

            App::pagination($page);
        }

        if (empty($photo['closed'])) {

            if (empty($total)) {
                show_error('Комментариев еще нет!');
            }

            if (is_user()) {
                echo '<div class="form">';
                echo '<form action="/gallery?act=addcomm&amp;gid='.$gid.'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                echo '<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br />';
                echo '<input type="submit" value="Написать" /></form></div><br />';

                echo '<a href="/rules">Правила</a> / ';
                echo '<a href="/smiles">Смайлы</a> / ';
                echo '<a href="/tags">Теги</a><br /><br />';
            } else {
                show_login('Вы не авторизованы, чтобы добавить комментарий, необходимо');
            }
        } else {
            show_error('Комментирование данной фотографии закрыто!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;uz='.$photo['user'].'">Альбом</a><br />';
    } else {
        show_error('Ошибка! Данного изображение не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br />';
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

    $query = DB::run() -> queryFetch("SELECT count(*) as `total_comments` FROM `comments` WHERE relate_type=? AND `relate_id`=? LIMIT 1;", [Photo::class, $gid]);

    if (!empty($query)) {

        $total_comments = (empty($query['total_comments'])) ? 1 : $query['total_comments'];
        $end = ceil($total_comments / App::setting('postgallery'));

        App::redirect("/gallery?act=comments&gid=$gid&page=$end");

    } else {
        show_error('Ошибка! Комментарий к данному изображению не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />';
break;
endswitch;
