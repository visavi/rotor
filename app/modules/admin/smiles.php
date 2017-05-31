<?php
App::view(App::setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$page = abs(intval(Request::input('page', 1)));

if (! is_admin([101, 102])) App::redirect('/admin/');

//show_title('Управление смайлами');

switch ($act):
/**
 * Список смайлов
 */
case 'index':

    $total = Smile::count();
    $page = App::paginate(App::setting('smilelist'), $total);

    $smiles = Smile::order_by_expr('CHAR_LENGTH(`code`) ASC')
        ->order_by_asc('name')
        ->limit(App::setting('smilelist'))
        ->offset($page['offset'])
        ->find_many();

    echo '<form action="/admin/smiles?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

    foreach($smiles as $smile) {
        echo '<img src="/uploads/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';

        echo '<input type="checkbox" name="del[]" value="'.$smile['id'].'" /> <a href="/admin/smiles?act=edit&amp;id='.$smile['id'].'&amp;page='.$page['current'].'">Редактировать</a><br />';
    }

    echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

    App::pagination($page);

    echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';

    //show_error('Смайлы еще не загружены!');

    echo '<i class="fa fa-upload"></i> <a href="/admin/smiles?act=add&amp;page='.$page['current'].'">Загрузить</a><br />';
break;

/**
 * Форма загрузки смайла
 */
case 'add':

    //App::setting('newtitle') = 'Добавление смайла';

    echo '<div class="form">';
    echo '<form action="/admin/smiles?act=load&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

    echo 'Прикрепить смайл:<br /><input type="file" name="smile" /><br />';
    echo 'Код смайла: <br /><input type="text" name="code" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';

    echo '<input type="submit" value="Загрузить" /></form></div><br />';

    echo 'Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png, bmp<br />';
    echo 'Весом не более '.formatsize(App::setting('smilemaxsize')).' и размером до '.App::setting('smilemaxweight').' px<br /><br />';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page='.$page.'">Вернуться</a><br />';
break;

/**
 * Загрузка смайла
 */
case 'load':

    //App::setting('newtitle') = 'Результат добавления';

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

    if (is_writeable(HOME.'/uploads/smiles')){

        $smile = Smile::where('code', $code)->find_one();
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
                $handle -> file_max_size = App::setting('smilemaxsize');  // byte
                $handle -> image_max_width = App::setting('smilemaxweight');  // px
                $handle -> image_max_height = App::setting('smilemaxweight'); // px
                $handle -> image_min_width = App::setting('smileminweight');   // px
                $handle -> image_min_height = App::setting('smileminweight');  // px
                $handle -> process(HOME.'/uploads/smiles/');

                if ($handle -> processed) {

                    $smile = Smile::create();
                    $smile->cats = 1;
                    $smile->name = $handle->file_dst_name;
                    $smile->code = $code;
                    $smile->save();

                    $handle -> clean();
                    clearCache();

                    App::setFlash('success', 'Смайл успешно загружен!');
                    App::redirect("/admin/smiles");

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

    App::view('includes/back', ['link' => '/admin/smiles?act=add&amp;page='.$page, 'title' => 'Вернуться']);
break;

/**
 * Редактирование
 */
case 'edit':

    $smile = Smile::find_one($id);

    if (! empty($smile)) {
        echo '<b><big>Редактирование смайла</big></b><br /><br />';
        echo '<img src="/uploads/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';

        echo '<div class="form">';
        echo '<form action="/admin/smiles?act=change&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Код смайла:<br />';
        echo '<input type="text" name="code" value="'.$smile['code'].'" /> <i>Код смайла должен начинаться со знака двоеточия</i><br />';
        echo '<input type="submit" value="Изменить" /></form></div><br />';
    } else {
        show_error('Ошибка! Смайла для редактирования не существует!');
    }

    App::view('includes/back', ['link' => '/admin/smiles?page='.$page, 'title' => 'Вернуться']);
break;

/**
 * Изменение смайла
 */
case 'change':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utf_lower($_POST['code'])) : '';

    $smile = Smile::find_one($id);

    $checkcode = Smile::select('id')
        ->where('code', $code)
        ->where_not_equal('id', $id)
        ->find_one($id);

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $smile, 'Не найден смайл для редактирования!')
        -> addRule('empty', $checkcode, 'Смайл с данным кодом уже имеется в списке!')
        -> addRule('string', $code, 'Слишком длинный или короткий код смайла!', true, 1, 20)
        -> addRule('regex', [$code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i'], 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!', true);

    if ($validation->run()) {

        $smile->code = $code;
        $smile->save();

        clearCache();

        App::setFlash('success', 'Смайл успешно отредактирован!');
        App::redirect("/admin/smiles?page=$page");


    } else {
        show_error($validation->getErrors());
    }

    App::view('includes/back', ['link' => '/admin/smiles?act=edit&amp;id='.$id.'&amp;page='.$page, 'title' => 'Вернуться']);
break;

/**
 * Удаление смайлов
 */
case 'del':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (! empty($del)) {
            if (is_writeable(HOME.'/uploads/smiles')){

                $arr_smiles = Smile::select('name')->where_id_in($del)->find_many();

                if (count($arr_smiles)>0){
                    foreach ($arr_smiles as $delfile) {
                        if (file_exists(HOME.'/uploads/smiles/'.$delfile['name'])) {
                            unlink(HOME.'/uploads/smiles/'.$delfile['name']);
                        }
                    }
                }

                Smile::where_id_in($del)->delete_many();
                clearCache();

                App::setFlash('success', 'Выбранные смайлы успешно удалены!');
                App::redirect("/admin/smiles?page=$page");

            } else {
                show_error('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
            }
        } else {
            show_error('Ошибка! Отсутствуют выбранные смайлы!');
        }
    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    App::view('includes/back', ['link' => '/admin/smiles?page='.$page, 'title' => 'Вернуться']);
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

App::view(App::setting('themes').'/foot');
