<?php
view(setting('themes').'/index');

$act = check(Request::input('act', 'index'));
$id  = abs(intval(Request::input('id', 0)));

if (isAdmin([101, 102])) {
    //show_title('Управление новостями');

switch ($action):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    echo '<div class="form"><a href="/news">Обзор новостей</a></div>';

    $total = News::count();
    $page = paginate(setting('postnews'), $total);

    if ($total > 0) {

        $news = News::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        echo '<form action="/admin/news?act=del&amp;page='.$page['current'].'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

        foreach ($news as $data) {

            echo '<div class="b">';

            $icon = (empty($data['closed'])) ? 'unlock' : 'lock';
            echo '<i class="fa fa-'.$icon.'"></i> ';

            echo '<b><a href="/news/'.$data['id'].'">'.$data['title'].'</a></b><small> ('.dateFixed($data['created_at']).')</small><br>';
            echo '<input type="checkbox" name="del[]" value="'.$data['id'].'"> ';
            echo '<a href="/admin/news?act=edit&amp;id='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a></div>';

            if (!empty($data['image'])) {
                echo '<div class="img"><a href="/uploads/news/'.$data['image'].'">'.resizeImage('uploads/news/', $data['image'], 75, ['alt' => $data['title']]).'</a></div>';
            }

            if (!empty($data['top'])){
                echo '<div class="right"><span style="color:#ff0000">На главной</span></div>';
            }

            if(stristr($data['text'], '[cut]')) {
                $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/news/'.$data['id'].'">Читать далее &raquo;</a>';
            }

            echo '<div>'.bbCode($data['text']).'</div>';

            echo '<div style="clear:both;">Добавлено: '.profile($data['user']).'<br>';
            echo '<a href="/news/'.$data['id'].'/comments">Комментарии</a> ('.$data['comments'].') ';
            echo '<a href="/news/'.$data['id'].'/end">&raquo;</a></div>';
        }

        echo '<br><input type="submit" value="Удалить выбранное"></form>';

        pagination($page);

        echo 'Всего новостей: <b>'.(int)$total.'</b><br><br>';
    } else {
        showError('Новостей еще нет!');
    }

    echo '<i class="fa fa-check"></i> <a href="/admin/news?act=add">Добавить</a><br>';

    if (isAdmin([101])) {
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?act=restatement&amp;token='.$_SESSION['token'].'">Пересчитать</a><br>';
    }
break;

############################################################################################
##                          Подготовка к редактированию новости                           ##
############################################################################################
case 'edit':
    $page  = abs(intval(Request::input('page', 1)));

    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($datanews)) {

        echo '<b><big>Редактирование</big></b><br><br>';

        echo '<div class="form cut">';
        echo '<form action="/admin/news?act=change&amp;id='.$id.'&amp;page='.$page.'" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo 'Заголовок:<br>';
        echo '<input type="text" name="title" size="50" maxlength="50" value="'.$datanews['title'].'"><br>';
        echo '<textarea id="markItUp" cols="25" rows="10" name="msg">'.$datanews['text'].'</textarea><br>';

        if (!empty($datanews['image']) && file_exists(HOME.'/uploads/news/'.$datanews['image'])){

            echo '<a href="/uploads/news/'.$datanews['image'].'">'.resizeImage('uploads/news/', $datanews['image'], 75, ['alt' => $datanews['title']]).'</a><br>';
            echo '<b>'.$datanews['image'].'</b> ('.formatFileSize(HOME.'/uploads/news/'.$datanews['image']).')<br><br>';
        }

        echo 'Прикрепить картинку:<br><input type="file" name="image"><br><br>';

        echo 'Закрыть комментарии: ';
        $checked = ($datanews['closed'] == 1) ? ' checked="checked"' : '';
        echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

        echo 'Показывать на главной: ';
        $checked = ($datanews['top'] == 1) ? ' checked="checked"' : '';
        echo '<input name="top" type="checkbox" value="1"'.$checked.'><br>';

        echo '<br><input type="submit" value="Изменить"></form></div><br>';
    } else {
        showError('Ошибка! Выбранная новость не существует, возможно она была удалена!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page='.$page.'">Вернуться</a><br>';
break;

############################################################################################
##                            Редактирование выбранной новости                            ##
############################################################################################
case 'change':

    $token = check(Request::input('token'));
    $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $closed = (empty($_POST['closed'])) ? 0 : 1;
    $top = (empty($_POST['top'])) ? 0 : 1;
    $page = abs(intval(Request::input('page', 1)));

    $datanews = DB::run() -> queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

    $validation = new Validation();

    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $datanews, 'Выбранной новости не существует, возможно она была удалена!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок новости!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст новости!', true, 5, 10000);

    if ($validation->run()) {

        DB::update("UPDATE `news` SET `title`=?, `text`=?, `closed`=?, `top`=? WHERE `id`=? LIMIT 1;", [$title, $msg, $closed, $top, $id]);

        // ---------------------------- Загрузка изображения -------------------------------//
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $handle = uploadImage($_FILES['image'], setting('filesize'), setting('fileupfoto'), $id);
            if ($handle) {

                // Удаление старой картинки
                if (!empty($datanews['image'])) {
                    deleteImage('uploads/news/', $datanews['image']);
                }

                $handle -> process(HOME.'/uploads/news/');

                if ($handle -> processed) {

                    DB::update("UPDATE `news` SET `image`=? WHERE `id`=? LIMIT 1;", [$handle -> file_dst_name, $id]);

                } else {
                    setFlash('danger', $handle->error);
                }
            }
        }
        // ---------------------------------------------------------------------------------//

        setFlash('success', 'Новость успешно отредактирована!');
        redirect("/admin/news?page=$page");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news?page='.$page.'">К новостям</a><br>';
break;

############################################################################################
##                            Подготовка к добавлению новости                             ##
############################################################################################
case 'add':

    echo '<h3>Создание новости</h3>';

    echo '<div class="form cut">';
    echo '<form action="/admin/news?act=addnews" method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
    echo 'Заголовок:<br>';
    echo '<input type="text" name="title" size="50" maxlength="50"><br>';
    echo '<textarea id="markItUp" cols="50" rows="10" name="msg"></textarea><br>';
    echo 'Прикрепить картинку:<br><input type="file" name="image"><br><br>';

    echo 'Вывести на главную: <input name="top" type="checkbox" value="1"><br>';
    echo 'Закрыть комментарии: <input name="closed" type="checkbox" value="1"><br>';

    echo '<br><input type="submit" value="Добавить"></form></div><br>';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news">Вернуться</a><br>';
break;

############################################################################################
##                                  Добавление новости                                    ##
############################################################################################
case 'addnews':

    $token = check(Request::input('token'));
    $msg = (isset($_POST['msg'])) ? check($_POST['msg']) : '';
    $title = (isset($_POST['title'])) ? check($_POST['title']) : '';
    $top = (empty($_POST['top'])) ? 0 : 1;
    $closed = (empty($_POST['closed'])) ? 0 : 1;


    $validation = new Validation();

    $validation -> addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('string', $title, 'Слишком длинный или короткий заголовок новости!', true, 5, 50)
        -> addRule('string', $msg, 'Слишком длинный или короткий текст новости!', true, 5, 10000);

    if ($validation->run()) {

        DB::insert("INSERT INTO `news` (`title`, `text`, `user_id`, `created_at`, `comments`, `closed`, `top`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$title, $msg, getUser('id'), SITETIME, 0, $closed, $top]);

        $lastid = DB::run() -> lastInsertId();

        // Выводим на главную если там нет новостей
        if (!empty($top) && empty(setting('lastnews'))) {
            DB::update("UPDATE `setting` SET `value`=? WHERE `name`=?;", [1, 'lastnews']);
            saveSetting();
        }

        // ---------------------------- Загрузка изображения -------------------------------//
        $handle = uploadImage($_FILES['image'], setting('filesize'), setting('fileupfoto'), $lastid);
        if ($handle) {

            $handle -> process(HOME.'/uploads/news/');

            if ($handle -> processed) {
                DB::update("UPDATE `news` SET `image`=? WHERE `id`=? LIMIT 1;", [$handle -> file_dst_name, $lastid]);

            } else {
                setFlash('danger', $handle->error);
                redirect("/admin/news?act=edit&id=$lastid");
            }
        }

        setFlash('success', 'Новость успешно добавлена!');
        redirect("/admin/news");

    } else {
        showError($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?act=add">Вернуться</a><br>';
    echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/news">К новостям</a><br>';
break;

############################################################################################
##                                  Пересчет комментариев                                 ##
############################################################################################
case 'restatement':

    $token = check(Request::input('token'));

    if (isAdmin([101])) {
        if ($token == $_SESSION['token']) {
            restatement('news');

            setFlash('success', 'Комментарии успешно пересчитаны!');
            redirect("/admin/news");

        } else {
            showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
        }
    } else {
        showError('Ошибка! Пересчитывать комментарии могут только суперадмины!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news">Вернуться</a><br>';
break;

############################################################################################
##                                    Удаление новостей                                   ##
############################################################################################
case 'del':

    $token = check(Request::input('token'));
    $del = intar(Request::input('del'));
    $page  = abs(intval(Request::input('page', 1)));

    if ($token == $_SESSION['token']) {
        if (!empty($del)) {
            if (is_writeable(HOME.'/uploads/news')){

                $del = implode(',', $del);

                $querydel = DB::select("SELECT `image` FROM `news` WHERE `id` IN (".$del.");");
                $arr_image = $querydel->fetchAll();

                if (count($arr_image)>0){
                    foreach ($arr_image as $delete){
                        deleteImage('uploads/news/', $delete['image']);
                    }
                }

                DB::delete("DELETE FROM `news` WHERE `id` IN (".$del.");");
                DB::delete("DELETE FROM `comments` WHERE relate_type = 'News' AND `relate_id` IN (".$del.");");

                setFlash('success', 'Выбранные новости успешно удалены!');
                redirect("/admin/news?page=$page");

            } else {
                showError('Ошибка! Не установлены атрибуты доступа на дирекоторию с изображениями!');
            }
        } else {
            showError('Ошибка! Отсутствуют выбранные новости!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/news?page='.$page.'">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
