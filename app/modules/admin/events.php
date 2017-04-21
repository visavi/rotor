<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$page = abs(intval(Request::input('page', 1)));

if (is_admin()) {
    //show_title('Управление событиями');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':
    echo '<div class="form"><a href="/events">Обзор событий</a></div>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `events`;");
    $page = App::paginate(App::setting('postevents'), $total);

    if ($total > 0) {

        $queryevents = DB::run() -> query("SELECT * FROM `events` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".App::setting('postevents').";");

        echo '<form action="/admin/events?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

        while ($data = $queryevents -> fetch()) {
            echo '<div class="b">';

            $icon = (empty($data['closed'])) ? 'unlock' : 'lock';
            echo '<i class="fa fa-'.$icon.'"></i> ';

            echo '<b><a href="/events?act=read&amp;id='.$data['id'].'">'.$data['title'].'</a></b><small> ('.date_fixed($data['time']).')</small><br />';
            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
            echo '<a href="/admin/events?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';

            if (!empty($data['image'])) {
                echo '<div class="img"><a href="/uploads/events/'.$data['image'].'">'.resize_image('uploads/events/', $data['image'], 75, ['alt' => $data['title']]).'</a></div>';
            }

            if (!empty($data['top'])){
                echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
            }

            if(stristr($data['text'], '[cut]')) {
                $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/events?act=read&amp;id='.$data['id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.App::bbCode($data['text']).'</div>';

            echo '<div style="clear:both;">Добавлено: '.profile($data['author']).'<br />';
            echo '<a href="/events?act=comments&amp;id='.$data['id'].'">Комментарии</a> ('.$data['comments'].') ';
            echo '<a href="/events?act=end&amp;id='.$data['id'].'">&raquo;</a></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего событий: <b>'.(int)$total.'</b><br /><br />';
    } else {
        show_error('Событий еще нет!');
    }

    echo '<i class="fa fa-check"></i> <a href="/events?act=new">Добавить событие</a><br />';

    if (is_admin([101])) {
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/events?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
    }
break;

############################################################################################
##                          Подготовка к редактированию события                           ##
############################################################################################
case 'edit':
    $dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($dataevent)) {

        echo '<b><big>Редактирование</big></b><br /><br />';

        echo '<div class="form cut">';
        echo '<form action="/admin/events?act=change&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
        echo 'Заголовок:<br />';
        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$dataevent['title'].'" /><br />';
        echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$dataevent['text'].'</textarea><br />';

        if (!empty($dataevent['image']) && file_exists(HOME.'/uploads/events/'.$dataevent['image'])){

            echo '<a href="/uploads/events/'.$dataevent['image'].'">'.resize_image('uploads/events/', $dataevent['image'], 75, ['alt' => $dataevent['title']]).'</a><br />';
            echo '<b>'.$dataevent['image'].'</b> ('.read_file(HOME.'/uploads/events/'.$dataevent['image']).')<br /><br />';
        }

        echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br />';
        echo '<i>gif, jpg, jpeg, png и bmp (Не более '.formatsize(App::setting('filesize')).' и '.App::setting('fileupfoto').'px)</i><br /><br />';

        $checked = ($dataevent['closed'] == 1) ? ' checked="checked"' : '';
        echo '<input name="closed" type="checkbox" value="1"'.$checked.' /> Закрыть комментарии<br />';

        $checked = ($dataevent['top'] == 1) ? ' checked="checked"' : '';
        echo '<input name="top" type="checkbox" value="1"'.$checked.' /> Показывать на главной<br />';

        echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
    } else {
        show_error('Ошибка! Выбранного события не существует, возможно оно было удалено!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/events?page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                            Редактирование выбранного события                           ##
############################################################################################
case 'change':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $closed = (empty($_POST['closed'])) ? 0 : 1;
    $top = (empty($_POST['top'])) ? 0 : 1;

    $dataevent = DB::run() -> queryFetch("SELECT * FROM `events` WHERE `id`=? LIMIT 1;", [$id]);

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $dataevent, 'Выбранного события не существует, возможно оно было удалено!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

    if ($validation->run()) {

        DB::run() -> query("UPDATE `events` SET `title`=?, `text`=?, `closed`=?, `top`=? WHERE `id`=? LIMIT 1;", [$title, $msg, $closed, $top, $id]);

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = upload_image($_FILES['image'], App::setting('filesize'), App::setting('fileupfoto'), $id);
            if ($handle) {

                // Удаление старой картинки
                if (!empty($dataevent['image'])) {
                    unlink_image('uploads/events/', $dataevent['image']);
                }

                $handle -> process(HOME.'/uploads/events/');

                if ($handle -> processed) {

                    DB::run() -> query("UPDATE `events` SET `image`=? WHERE `id`=? LIMIT 1;", [$handle -> file_dst_name, $id]);
                    $handle -> clean();

                } else {
                    notice($handle->error, 'danger');
                }
            }
        }
        // ---------------------------------------------------------------------------------//

        notice('Событие успешно отредактировано!');
        redirect("/admin/events?act=edit&id=$id");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/events?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/events?page='.$page.'">К событиям</a><br />';
break;

############################################################################################
##                                  Пересчет комментариев                                 ##
############################################################################################
case 'restatement':

    $uid = check($_GET['uid']);

    if (is_admin([101])) {
        if ($uid == $_SESSION['token']) {
            restatement('events');

            notice('Комментарии успешно пересчитаны!');
            redirect("/admin/events");

        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Пересчитывать сообщения могут только суперадмины!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/events">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление событий                                    ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (!empty($del)) {
            if (is_writeable(HOME.'/uploads/events')){

                $del = implode(',', $del);

                $querydel = DB::run()->query("SELECT `image` FROM `events` WHERE `id` IN (".$del.");");
                $arr_image = $querydel->fetchAll();

                if (count($arr_image)>0){
                    foreach ($arr_image as $delete){
                        unlink_image('uploads/events/', $delete['image']);
                    }
                }

                DB::run() -> query("DELETE FROM `events` WHERE `id` IN (".$del.");");
                DB::run() -> query("DELETE FROM `comments` WHERE relate_type=? AND `event_id` IN (".$del.");", ['event']);

                notice('Выбранные события успешно удалены!');
                redirect("/admin/events?page=$page");

                } else {
                show_error('Ошибка! Не установлены атрибуты доступа на директорию с изображениями!');
            }
        } else {
            show_error('Ошибка! Отсутствуют выбранные события!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/events?page='.$page.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view(App::setting('themes').'/foot');

