<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

if (! is_admin([101, 102])) redirect('/admin/');

show_title('Управление смайлами');

switch ($act):
/**
 * Список смайлов
 */
case 'index':

    $total = DBM::run()->count('smiles');

    if ($total > 0 && $start >= $total) {
        $start = last_page($total, $config['smilelist']);
    }

    $smiles = DBM::run()->query("SELECT * FROM `smiles` ORDER BY CHAR_LENGTH(`code`) ASC LIMIT :start, :limit;", ['start' => intval($start), 'limit' => intval($config['smilelist'])]);

    echo '<form action="/admin/smiles?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

    foreach($smiles as $smile) {
        echo '<img src="/upload/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';

        echo '<input type="checkbox" name="del[]" value="'.$smile['id'].'" /> <a href="/admin/smiles?act=edit&amp;id='.$smile['id'].'&amp;start='.$start.'">Редактировать</a><br />';
    }

    echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

    page_strnavigation('/admin/smiles?', $config['smilelist'], $start, $total);

    echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';

    //show_error('Смайлы еще не загружены!');

    echo '<i class="fa fa-upload"></i> <a href="/admin/smiles?act=add&amp;start='.$start.'">Загрузить</a><br />';
break;

/**
 * Форма загрузки смайла
 */
case 'add':

    $config['newtitle'] = 'Добавление смайла';

    echo '<div class="form">';
    echo '<form action="/admin/smiles?act=load&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

    echo 'Прикрепить смайл:<br /><input type="file" name="smile" /><br />';
    echo 'Код смайла: <br /><input type="text" name="code" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';

    echo '<input type="submit" value="Загрузить" /></form></div><br />';

    echo 'Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png, bmp<br />';
    echo 'Весом не более '.formatsize($config['smilemaxsize']).' и размером до '.$config['smilemaxweight'].' px<br /><br />';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?start='.$start.'">Вернуться</a><br />';
break;

/**
 * Загрузка смайла
 */
case 'load':

    $config['newtitle'] = 'Результат добавления';

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

    if (is_writeable(HOME.'/upload/smiles')){

        $smile = DBM::run()->selectFirst('smiles', ['code' => $code]);

        $validation = new Validation();

        $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            -> addRule('empty', $smile, 'Смайл с данным кодом уже имеется в списке!')
            -> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 2, 20)
            -> addRule('regex', [$code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i'], 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!', true);


        if ($validation->run()) {

            $handle = new FileUpload($_FILES['smile']);

            if ($handle -> uploaded) {

                if (! preg_match('/[А-Яа-яЁё]/u', $code)) {
                    $handle -> file_new_name_body = substr($code, 1);
                }
                //$handle -> file_overwrite = true;

                $handle -> ext_check = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
                $handle -> file_max_size = $config['smilemaxsize'];  // byte
                $handle -> image_max_width = $config['smilemaxweight'];  // px
                $handle -> image_max_height = $config['smilemaxweight']; // px
                $handle -> image_min_width = $config['smileminweight'];   // px
                $handle -> image_min_height = $config['smileminweight'];  // px
                $handle -> process(HOME.'/upload/smiles/');

                if ($handle -> processed) {

                    $smile = DBM::run()->insert('smiles', [
                        'cats' => 1,
                        'name' => $handle->file_dst_name,
                        'code' => $code,
                    ]);

                    $handle -> clean();
                    clearCache();

                    notice('Смайл успешно загружен!');
                    redirect("/admin/smiles");

                } else {
                    show_error($handle->error);
                }
            } else {
                show_error($handle->error);
            }
        } else {
            show_error($validation->getErrors());
        }
    } else {
        show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
    }

    render('includes/back', ['link' => '/admin/smiles?act=add&amp;start='.$start, 'title' => 'Вернуться']);
break;

/**
 * Редактирование
 */
case 'edit':

    $smile = DBM::run()->selectFirst('smiles', ['id' => $id]);

    if (! empty($smile)) {
        echo '<b><big>Редактирование смайла</big></b><br /><br />';
        echo '<img src="/upload/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';

        echo '<div class="form">';
        echo '<form action="/admin/smiles?act=change&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Код смайла:<br />';
        echo '<input type="text" name="code" value="'.$smile['code'].'" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';
        echo '<input type="submit" value="Изменить" /></form></div><br />';
    } else {
        show_error('Ошибка! Смайла для редактирования не существует!');
    }

    render('includes/back', ['link' => '/admin/smiles?start='.$start, 'title' => 'Вернуться']);
break;

/**
 * Изменение смайла
 */
case 'change':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

    $smile = DBM::run()->selectFirst('smiles', ['id' => $id]);

    $checkcode = DBM::run()->selectFirst('smiles', [
        'code' => $code,
        'id' => $id,
    ]);
    $checkcode = DBM::run()->queryFirst("SELECT `id` FROM `smiles` WHERE `code`=:code AND `id`<>:id LIMIT 1;", compact('code', 'id'));

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $smile, 'Не найден смайл для редактирования!')
        -> addRule('empty', $checkcode, 'Смайл с данным кодом уже имеется в списке!')
        -> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 1, 20)
        -> addRule('regex', [$code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i'], 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!', true);

    if ($validation->run()) {

        $smile = DBM::run()->update('smiles', ['code' => $code], ['id' => $id]);
        clearCache();

        notice('Смайл успешно отредактирован!');
        redirect("/admin/smiles?start=$start");


    } else {
        show_error($validation->getErrors());
    }

    render('includes/back', ['link' => '/admin/smiles?act=edit&amp;id='.$id.'&amp;start='.$start, 'title' => 'Вернуться']);
break;

/**
 * Удаление смайлов
 */
case 'del':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (! empty($del)) {
            if (is_writeable(HOME.'/upload/smiles')){

                $del = implode(',', $del);

                $arr_smiles = DBM::run()->query("SELECT `name` FROM `smiles` WHERE `id` IN(".$del.");");

                if (count($arr_smiles)>0){
                    foreach ($arr_smiles as $delfile) {
                        if (file_exists(HOME.'/upload/smiles/'.$delfile['name'])) {
                            unlink(HOME.'/upload/smiles/'.$delfile['name']);
                        }
                    }
                }
                DBM::run()->execute("DELETE FROM `smiles` WHERE `id` IN (".$del.");");
                clearCache();

                notice('Выбранные смайлы успешно удалены!');
                redirect("/admin/smiles?start=$start");

            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
            }
        } else {
            show_error('Ошибка! Отсутствуют выбранные смайлы!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    render('includes/back', ['link' => '/admin/smiles?start='.$start, 'title' => 'Вернуться']);
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

App::view($config['themes'].'/foot');
