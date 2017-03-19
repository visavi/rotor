<?php
App::view($config['themes'].'/index');

$act = check(Request::input('act', 'index'));
$page = abs(intval(Request::input('page', 1)));

if (is_admin()) {
    show_title('Управление галереей');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            echo '<a href="gallery?page='.$page.'&amp;rand='.mt_rand(100, 999).'">Обновить</a> / ';
            echo '<a href="/gallery?act=addphoto">Добавить фото</a> / ';
            echo '<a href="/gallery?page='.$page.'">Обзор</a><hr />';

            $total = Photo::count();
            $page = App::paginate(App::setting('fotolist'), $total);


            if ($total > 0) {

                echo '<form action="/admin/gallery?act=del&amp;page='.$page['current'].'" method="post">';
                echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
                $photos = Photo::orderBy('created_at', 'desc')
                    ->offset($page['offset'])
                    ->limit($config['fotolist'])
                    ->with('user')
                    ->get();

                foreach ($photos as $data) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-picture-o"></i> ';
                    echo '<b><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b> ('.read_file(HOME.'/uploads/pictures/'.$data['link']).')<br />';
                    echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> <a href="/admin/gallery?act=edit&amp;page='.$page['current'].'&amp;gid='.$data['id'].'">Редактировать</a>';
                    echo '</div>';

                    echo '<div><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.resize_image('uploads/pictures/', $data['link'], $config['previewsize'], ['alt' => $data['title']]).'</a><br />';

                    if (!empty($data['text'])){
                        echo App::bbCode($data['text']).'<br />';
                    }

                    echo 'Добавлено: '.profile($data['user']).' ('.date_fixed($data['time']).')<br />';
                    echo '<a href="/gallery?act=comments&amp;gid='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
                    echo '<a href="/gallery?act=end&amp;gid='.$data['id'].'">&raquo;</a>';
                    echo '</div>';
                }

                echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

                App::pagination($page);

                echo 'Всего фотографий: <b>'.$total.'</b><br /><br />';
            } else {
                show_error('Фотографий еще нет!');
            }

            if (is_admin([101])) {
                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/gallery?act=restatement&amp;token='.$_SESSION['token'].'">Пересчитать</a><br />';
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
                echo 'Название: <br /><input type="text" name="title" value="'.$photo['title'].'" /><br />';
                echo 'Подпись к фото: <br /><textarea cols="25" rows="5" name="text">'.$photo['text'].'</textarea><br />';

                echo 'Закрыть комментарии: ';
                $checked = ($photo['closed'] == 1) ? ' checked="checked"' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

                echo '<input type="submit" value="Изменить" /></form></div><br />';
            } else {
                show_error('Ошибка! Данной фотографии не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery?page='.$page.'">Вернуться</a><br />';
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
                    if (utf_strlen($title) >= 5 && utf_strlen($title) < 50) {
                        if (utf_strlen($text) <= 1000) {

                            $text = antimat($text);

                            DB::run() -> query("UPDATE `photo` SET `title`=?, `text`=?, `closed`=? WHERE `id`=?;", [$title, $text, $closed, $gid]);

                            notice('Фотография успешно отредактирована!');
                            redirect("/admin/gallery?page=$page");

                        } else {
                            show_error('Ошибка! Слишком длинное описание (Необходимо до 1000 символов)!');
                        }
                    } else {
                        show_error('Ошибка! Слишком длинное или короткое название!');
                    }
                } else {
                    show_error('Ошибка! Данной фотографии не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/gallery?act=edit&amp;gid='.$gid.'&amp;page='.$page.'">Вернуться</a><br />';
            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery">Галерея</a><br />';
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
                                DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=?;", ['Gallery', $delete['id']]);

                                unlink_image('uploads/pictures/', $delete['link']);
                            }

                            notice('Выбранные фотографии успешно удалены!');
                            redirect("/admin/gallery?page=$page");

                        } else {
                            show_error('Ошибка! Данных фотографий не существует!');
                        }
                    } else {
                        show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с фотографиями!');
                    }
                } else {
                    show_error('Ошибка! Отсутствуют выбранные фотографии!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery?page='.$page.'">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                  Пересчет комментариев                                 ##
        ############################################################################################
        case 'restatement':

            $token = check(Request::input('token'));

            if (is_admin([101])) {
                if ($token == $_SESSION['token']) {
                    restatement('gallery');

                    notice('Комментарии успешно пересчитаны!');
                    redirect("/admin/gallery");

                } else {
                    show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                show_error('Ошибка! Пересчитывать комментарии могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery">Вернуться</a><br />';
        break;

    default:
        redirect("/admin/gallery");
    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
