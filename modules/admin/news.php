<?php
App::view($config['themes'].'/index');

$act   = check(Request::input('act', 'index'));
$id    = abs(intval(Request::input('id', 0)));
$start = abs(intval(Request::input('start', 0)));

if (is_admin(array(101, 102))) {
    show_title('Управление новостями');

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    echo '<div class="form"><a href="/news">Обзор новостей</a></div>';

    $total = DB::run() -> querySingle("SELECT count(*) FROM `news`;");

    if ($total > 0) {
        if ($start >= $total) {
            $start = last_page($total, $config['postnews']);
        }

        $querynews = DB::run() -> query("SELECT * FROM `news` ORDER BY `news_time` DESC LIMIT ".$start.", ".$config['postnews'].";");

        echo '<form action="/admin/news?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

        while ($data = $querynews -> fetch()) {

            echo '<div class="b">';

            $icon = (empty($data['news_closed'])) ? 'document_plus.gif' : 'document_minus.gif';
            echo '<img src="/assets/img/images/'.$icon.'" alt="image" /> ';

            echo '<b><a href="/news/'.$data['news_id'].'">'.$data['news_title'].'</a></b><small> ('.date_fixed($data['news_time']).')</small><br />';
            echo '<input type="checkbox" name="del[]" value="'.$data['news_id'].'" /> ';
            echo '<a href="/admin/news?act=edit&amp;id='.$data['news_id'].'&amp;start='.$start.'">Редактировать</a></div>';

            if (!empty($data['news_image'])) {
                echo '<div class="img"><a href="/upload/news/'.$data['news_image'].'">'.resize_image('upload/news/', $data['news_image'], 75, array('alt' => $data['news_title'])).'</a></div>';
            }

            if (!empty($data['news_top'])){
                echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
            }

            if(stristr($data['news_text'], '[cut]')) {
                $data['news_text'] = current(explode('[cut]', $data['news_text'])).' <a href="/news/'.$data['news_id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.bb_code($data['news_text']).'</div>';

            echo '<div style="clear:both;">Добавлено: '.profile($data['news_author']).'<br />';
            echo '<a href="/news/'.$data['news_id'].'/comments">Комментарии</a> ('.$data['news_comments'].') ';
            echo '<a href="/news/'.$data['news_id'].'/end">&raquo;</a></div>';
        }

        echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

        page_strnavigation('/admin/news?', $config['postnews'], $start, $total);

        echo 'Всего новостей: <b>'.(int)$total.'</b><br /><br />';
    } else {
        show_error('Новостей еще нет!');
    }

    echo '<img src="/assets/img/images/open.gif" alt="image" /> <a href="/admin/news?act=add">Добавить</a><br />';

    if (is_admin(array(101))) {
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?act=restatement&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
    }
break;

############################################################################################
##                          Подготовка к редактированию новости                           ##
############################################################################################
case 'edit':
    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

    if (!empty($datanews)) {

        echo '<b><big>Редактирование</big></b><br /><br />';

        echo '<div class="form cut">';
        echo '<form action="/admin/news?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';
        echo 'Заголовок:<br />';
        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$datanews['news_title'].'" /><br />';
        echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$datanews['news_text'].'</textarea><br />';

        if (!empty($datanews['news_image']) && file_exists(HOME.'/upload/news/'.$datanews['news_image'])){

            echo '<a href="/upload/news/'.$datanews['news_image'].'">'.resize_image('upload/news/', $datanews['news_image'], 75, array('alt' => $datanews['news_title'])).'</a><br />';
            echo '<b>'.$datanews['news_image'].'</b> ('.read_file(HOME.'/upload/news/'.$datanews['news_image']).')<br /><br />';
        }

        echo 'Прикрепить картинку:<br /><input type="file" name="image" /><br /><br />';

        echo 'Закрыть комментарии: ';
        $checked = ($datanews['news_closed'] == 1) ? ' checked="checked"' : '';
        echo '<input name="closed" type="checkbox" value="1"'.$checked.' /><br />';

        echo 'Показывать на главной: ';
        $checked = ($datanews['news_top'] == 1) ? ' checked="checked"' : '';
        echo '<input name="top" type="checkbox" value="1"'.$checked.' /><br />';

        echo '<br /><input type="submit" value="Изменить" /></form></div><br />';
    } else {
        show_error('Ошибка! Выбранная новость не существует, возможно она была удалена!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?start='.$start.'">Вернуться</a><br />';
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

    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `news_id`=? LIMIT 1;", array($id));

    $validation = new Validation();

    $validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $datanews, 'Выбранной новости не существует, возможно она была удалена!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок новости!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст новости!', true, 5, 10000);

    if ($validation->run()) {

        DB::run() -> query("UPDATE `news` SET `news_title`=?, `news_text`=?, `news_closed`=?, `news_top`=? WHERE `news_id`=? LIMIT 1;", array($title, $msg, $closed, $top, $id));

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $id);
            if ($handle) {

                // Удаление старой картинки
                if (!empty($datanews['news_image'])) {
                    unlink_image('upload/news/', $datanews['news_image']);
                }

                $handle -> process(HOME.'/upload/news/');

                if ($handle -> processed) {

                    DB::run() -> query("UPDATE `news` SET `news_image`=? WHERE `news_id`=? LIMIT 1;", array($handle -> file_dst_name, $id));
                    $handle -> clean();

                } else {
                    notice($handle->error, 'danger');
                }
            }
        }
        // ---------------------------------------------------------------------------------//

        notice('Новость успешно отредактирована!');
        redirect("/admin/news?start=$start");

    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?act=edit&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?start='.$start.'">К новостям</a><br />';
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

    $validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок события!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст события!', true, 5, 10000);

    if ($validation->run()) {

        DB::run() -> query("INSERT INTO `news` (`news_title`, `news_text`, `news_author`, `news_time`, `news_comments`, `news_closed`, `news_top`) VALUES (?, ?, ?, ?, ?, ?, ?);", array($title, $msg, $log, SITETIME, 0, $closed, $top));

        $lastid = DB::run() -> lastInsertId();

        // Выводим на главную если там нет новостей
        if (!empty($top) && empty($config['lastnews'])) {
            DB::run() -> query("UPDATE `setting` SET `setting_value`=? WHERE `setting_name`=?;", array(1, 'lastnews'));
            save_setting();
        }

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = upload_image($_FILES['image'], $config['filesize'], $config['fileupfoto'], $lastid);
            if ($handle) {

                $handle -> process(HOME.'/upload/news/');

                if ($handle -> processed) {
                    DB::run() -> query("UPDATE `news` SET `news_image`=? WHERE `news_id`=? LIMIT 1;", array($handle -> file_dst_name, $lastid));
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

    if (is_admin(array(101))) {
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

    if ($uid == $_SESSION['token']) {
        if (!empty($del)) {
            if (is_writeable(HOME.'/upload/news')){

                $del = implode(',', $del);

                $querydel = DB::run()->query("SELECT `news_image` FROM `news` WHERE `news_id` IN (".$del.");");
                $arr_image = $querydel->fetchAll();

                if (count($arr_image)>0){
                    foreach ($arr_image as $delete){
                        unlink_image('upload/news/', $delete['news_image']);
                    }
                }

                DB::run() -> query("DELETE FROM `news` WHERE `news_id` IN (".$del.");");
                DB::run() -> query("DELETE FROM `commnews` WHERE `commnews_news_id` IN (".$del.");");

                notice('Выбранные новости успешно удалены!');
                redirect("/admin/news?start=$start");

            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию с изображениями!');
            }
        } else {
            show_error('Ошибка! Отсутствуют выбранные новости!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?start='.$start.'">Вернуться</a><br />';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
