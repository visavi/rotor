<?php
App::view($config['themes'].'/index');

$act = check(Request::input('act', 'index'));
$id  = abs(intval(Request::input('id', 0)));

if (is_admin([101, 102])) {
    show_title('Управление новостями');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    echo '<div class="form"><a href="/news">Обзор новостей</a></div>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");

    if ($total > 0) {
        $page = App::paginate(App::setting('postnews'), $total);

        $querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['postnews'].";");

        echo '<form action="/admin/news?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

        while ($data = $querynews -> fetch()) {

            echo '<div class="b">';

            $icon = (empty($data['closed'])) ? 'unlock' : 'lock';
            echo '<i class="fa fa-'.$icon.'"></i> ';

            echo '<b><a href="/news/'.$data['id'].'">'.$data['title'].'</a></b><small> ('.date_fixed($data['time']).')</small><br />';
            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'" /> ';
            echo '<a href="/admin/news?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';

            if (!empty($data['image'])) {
                echo '<div class="img"><a href="/uploads/news/'.$data['image'].'">'.resize_image('uploads/news/', $data['image'], 75, ['alt' => $data['title']]).'</a></div>';
            }

            if (!empty($data['top'])){
                echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
            }

            if(stristr($data['text'], '[cut]')) {
                $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/news/'.$data['id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.App::bbCode($data['text']).'</div>';

            echo '<div style="clear:both;">Добавлено: '.profile($data['author']).'<br />';
            echo '<a href="/news/'.$data['id'].'/comments">Комментарии</a> ('.$data['comments'].') ';
            echo '<a href="/news/'.$data['id'].'/end">&raquo;</a></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        App::pagination($page);

        echo 'Всего новостей: <b>'.(int)$total.'</b><br /><br />';
    } else {
        show_error('Новостей еще нет!');
    }

    echo '<i class="fa fa-check"></i> <a href="/admin/news?act=add">Добавить</a><br />';

    if (is_admin([101])) {
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
    }
break;

############################################################################################
##                          Подготовка к редактированию новости                           ##
############################################################################################
case 'edit':
    $page  = abs(intval(Request::input('page', 1)));

    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($datanews)) {

        echo '<b><big>Редактирование</big></b><br /><br />';

        echo '<div class="form cut">';
        echo '<form action="/admin/news?act=change&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
        echo 'Заголовок:<br />';
        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$datanews['title'].'" /><br />';
        echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$datanews['text'].'</textarea><br />';

        if (!empty($datanews['image']) && file_exists(HOME.'/uploads/news/'.$datanews['image'])){

            echo '<a href="/uploads/news/'.$datanews['image'].'">'.resize_image('uploads/news/', $datanews['image'], 75, ['alt' => $datanews['title']]).'</a><br />';
            echo '<b>'.$datanews['image'].'</b> ('.read_file(HOME.'/uploads/news/'.$datanews['image']).')<br /><br />';
        }

        echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br /><br />';

        echo 'Закрыть комментарии: ';
        $checked = ($datanews['closed'] == 1) ? ' checked="checked"' : '';
        echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

        echo 'Показывать на главной: ';
        $checked = ($datanews['top'] == 1) ? ' checked="checked"' : '';
        echo '<input name="top" type="checkbox" value="1"'.$checked.' /><br />';

        echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
    } else {
        show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page='.$page.'">Вернуться</a><br />';
break;

############################################################################################
##                            Редактирование выбранной новости                            ##
############################################################################################
case 'change':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $closed = (empty($_POST['closed'])) ? 0 : 1;
    $top = (empty($_POST['top'])) ? 0 : 1;
    $page = abs(intval(Request::input('page', 1)));

    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $datanews, 'Выбранной новости не существует, возможно она была удалена!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок новости!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст новости!', true, 5, 10000);

    if ($validation->run()) {

        DB::run() -> query("UPDATE `news` SET `title`=?, `text`=?, `closed`=?, `top`=? WHERE `id`=? LIMIT 1;", [$title, $msg, $closed, $top, $id]);

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $id);
            if ($handle) {

                // Удаление старой картинки
                if (!empty($datanews['image'])) {
                    unlink_image('uploads/news/', $datanews['image']);
                }

                $handle -> process(HOME.'/uploads/news/');

                if ($handle -> processed) {

                    DB::run() -> query("UPDATE `news` SET `image`=? WHERE `id`=? LIMIT 1;", [$handle -> file_dst_name, $id]);
                    $handle -> clean();

                } else {
                    notice($handle->error, 'danger');
                }
            }
        }
        // ---------------------------------------------------------------------------------//

        notice('Новость успешно отредактирована!');
        redirect("/admin/news?page=$page");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?page='.$page.'">К новостям</a><br />';
break;

############################################################################################
##                            Подготовка к добавлению новости                             ##
############################################################################################
case 'add':

    echo '<b><big>Создание новости</big></b><br /><br />';

    echo '<div class="form cut">';
    echo '<form action="/admin/news?act=addnews&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
    echo 'Заголовок:<br />';
    echo '<input type="text" name="title" size="50" maxlength="50" /><br />';
    echo '<textarea id="markItUp" cols="50" rows="10" name="msg"></textarea><br />';
    echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br /><br />';

    echo 'Вывести на главную: <input name="top" type="checkbox" value="1" /><br />';
    echo 'Закрыть комментарии: <input name="closed" type="checkbox" value="1" /><br />';

    echo '<br /><input type="submit" value="Добавить" /></form></div><br />';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news">Вернуться</a><br />';
break;

############################################################################################
##                                  Добавление новости                                    ##
############################################################################################
case 'addnews':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $top = (empty($_POST['top'])) ? 0 : 1;
    $closed = (empty($_POST['closed'])) ? 0 : 1;


    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

    if ($validation->run()) {

        DB::run() -> query("INSERT INTO `news` (`title`, `text`, `author`, `time`, `comments`, `closed`, `top`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$title, $msg, $log, SITETIME, 0, $closed, $top]);

        $lastid = DB::run() -> lastInsertId();

        // Выводим на главную если там нет новостей
        if (!empty($top) && empty($config['lastnews'])) {
            DB::run() -> query("UPDATE `setting` SET `value`=? WHERE `name`=?;", [1, 'lastnews']);
            save_setting();
        }

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $lastid);
            if ($handle) {

                $handle -> process(HOME.'/uploads/news/');

                if ($handle -> processed) {
                    DB::run() -> query("UPDATE `news` SET `image`=? WHERE `id`=? LIMIT 1;", [$handle -> file_dst_name, $lastid]);
                    $handle -> clean();

                } else {
                    notice($handle->error, 'danger');
                    redirect("/admin/news?act=edit&id=$lastid");
                }
            }
        }
        // ---------------------------------------------------------------------------------//

        notice('Новость успешно добавлена!');
        redirect("/admin/news");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?act=add">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news">К новостям</a><br />';
break;

############################################################################################
##                                  Пересчет комментариев                                 ##
############################################################################################
case 'restatement':

    $uid = check($_GET['uid']);

    if (is_admin([101])) {
        if ($uid == $_SESSION['token']) {
            restatement('news');

            notice('Комментарии успешно пересчитаны!');
            redirect("/admin/news");

        } else {
            show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        show_error('Ошибка! Пересчитывать комментарии могут только суперадмины!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news">Вернуться</a><br />';
break;

############################################################################################
##                                    Удаление новостей                                   ##
############################################################################################
case 'del':

    $uid = check($_GET['uid']);
    $del = (isset($_REQUEST['del'])) ? intar($_REQUEST['del']) : 0;
    $page  = abs(intval(Request::input('page', 1)));

    if ($uid == $_SESSION['token']) {
        if (!empty($del)) {
            if (is_writeable(HOME.'/uploads/news')){

                $del = implode(',', $del);

                $querydel = DB::run()->query("SELECT `image` FROM `news` WHERE `id` IN (".$del.");");
                $arr_image = $querydel->fetchAll();

                if (count($arr_image)>0){
                    foreach ($arr_image as $delete){
                        unlink_image('uploads/news/', $delete['image']);
                    }
                }

                DB::run() -> query("DELETE FROM `news` WHERE `id` IN (".$del.");");
                DB::run() -> query("DELETE FROM `commnews` WHERE `id` IN (".$del.");");

                notice('Выбранные новости успешно удалены!');
                redirect("/admin/news?page=$page");

            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с изображениями!');
            }
        } else {
            show_error('Ошибка! Отсутствуют выбранные новости!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page='.$page.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
