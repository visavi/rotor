<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$page = abs(intval(Request::input('page', 1)));

if (isAdmin()) {
    //show_title('Управление галереей');

    switch ($action):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="/gallery/create">Добавить фото</a> / ';
            echo '<a href="/gallery?page='.$page.'">Обзор</a><hr>';

            $total = Photo::count();
            $page = paginate(setting('fotolist'), $total);


            if ($total > 0) {

                echo '<form action="/admin/gallery?act=del&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                $photos = Photo::orderBy('created_at', 'desc')
                    ->offset($page['offset'])
                    ->limit(setting('fotolist'))
                    ->with('user')
                    ->get();

                foreach ($photos as $data) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-picture-o"></i> ';
                    echo '<b><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b> ('.formatFileSize(HOME.'/uploads/pictures/'.$data['link']).')<br>';
                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> <a href="/admin/gallery?act=edit&amp;page='.$page['current'].'&amp;gid='.$data['id'].'">Редактировать</a>';
                    echo '</div>';

                    echo '<div><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.resizeImage('uploads/pictures/', $data['link'], setting('previewsize'), ['alt' => $data['title']]).'</a><br>';

                    if (!empty($data['text'])){
                        echo bbCode($data['text']).'<br>';
                    }

                    echo 'Добавлено: '.profile($data['user']).' ('.dateFixed($data['time']).')<br>';
                    echo '<a href="/gallery?act=comments&amp;gid='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
                    echo '<a href="/gallery?act=end&amp;gid='.$data['id'].'">&raquo;</a>';
                    echo '</div>';
                }

                echo '<br><input type="submit" value="Удалить выбранное"></form>';

                pagination($page);

                echo 'Всего фотографий: <b>'.$total.'</b><br><br>';
            } else {
                showError('Фотографий еще нет!');
            }

            if (isAdmin([101])) {
                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/gallery?act=restatement&amp;token='.$_SESSION['token'].'">Пересчитать</a><br>';
            }
        break;

        ############################################################################################
        ##                                    Редактирование                                      ##
        ############################################################################################
        case 'edit':

            $gid = abs(intval(Request::input('gid')));

            $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `id`=? LIMIT 1;", [$gid]);

            if (!empty($photo)) {

                echo '<div class="form">';
                echo '<form action="/admin/gallery?act=change&amp;gid='.$gid.'&amp;page='.$page.'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                echo 'Название: <br><input type="text" name="title" value="'.$photo['title'].'"><br>';
                echo 'Подпись к фото: <br><textarea cols="25" rows="5" name="text">'.$photo['text'].'</textarea><br>';

                echo 'Закрыть комментарии: ';
                $checked = ($photo['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                echo '<input type="submit" value="Изменить"></form></div><br>';
            } else {
                showError('Ошибка! Данной фотографии не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                 Изменение сообщения                                    ##
        ############################################################################################
        case 'change':

            $token = check(Request::input('token'));
            $gid = abs(intval(Request::input('gid')));
            $title = check(Request::input('title'));
            $text = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;

            if ($token == $_SESSION['token']) {
                $photo = DB::run() -> queryFetch("SELECT * FROM `photo` WHERE `id`=? LIMIT 1;", [$gid]);

                if (!empty($photo)) {
                    if (utfStrlen($title) >= 5 && utfStrlen($title) < 50) {
                        if (utfStrlen($text) <= 1000) {

                            $text = antimat($text);

                            DB::run() -> query("UPDATE `photo` SET `title`=?, `text`=?, `closed`=? WHERE `id`=?;", [$title, $text, $closed, $gid]);

                            setFlash('success', 'Фотография успешно отредактирована!');
                            redirect("/admin/gallery?page=$page");

                        } else {
                            showError('Ошибка! Слишком длинное описание (Необходимо до 1000 символов)!');
                        }
                    } else {
                        showError('Ошибка! Слишком длинное или короткое название!');
                    }
                } else {
                    showError('Ошибка! Данной фотографии не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/gallery?act=edit&amp;gid='.$gid.'&amp;page='.$page.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery">Галерея</a><br>';
        break;

        ############################################################################################
        ##                                 Удаление изображений                                   ##
        ############################################################################################
        case 'del':

            $token = check(Request::input('token'));
            $del = intar(Request::input('del'));

            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $del = implode(',', $del);

                    if (is_writeable(HOME.'/uploads/pictures')) {
                        $querydel = DB::run() -> query("SELECT `id`, `link` FROM `photo` WHERE `id` IN (".$del.");");
                        $arr_photo = $querydel -> fetchAll();

                        if (count($arr_photo) > 0) {
                            foreach ($arr_photo as $delete) {
                                DB::run() -> query("DELETE FROM `photo` WHERE `id`=? LIMIT 1;", [$delete['id']]);
                                DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=?;", [Photo::class, $delete['id']]);

                                deleteImage('uploads/pictures/', $delete['link']);
                            }

                            setFlash('success', 'Выбранные фотографии успешно удалены!');
                            redirect("/admin/gallery?page=$page");

                        } else {
                            showError('Ошибка! Данных фотографий не существует!');
                        }
                    } else {
                        showError('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');
                    }
                } else {
                    showError('Ошибка! Отсутствуют выбранные фотографии!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery?page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Пересчет комментариев                                 ##
        ############################################################################################
        case 'restatement':

            $token = check(Request::input('token'));

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {
                    restatement('photo');

                    setFlash('success', 'Комментарии успешно пересчитаны!');
                    redirect("/admin/gallery");

                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Пересчитывать комментарии могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery">Вернуться</a><br>';
        break;

    default:
        redirect("/admin/gallery");
    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
