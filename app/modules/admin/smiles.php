<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;
$page = abs(intval(Request::input('page', 1)));

if (! isAdmin([101, 102])) redirect('/admin/');

//show_title('Управление смайлами');

switch ($action):
/**
 * Список смайлов
 */
case 'index':

    $total = Smile::count();
    $page = paginate(setting('smilelist'), $total);

    $smiles = Smile::order_by_expr('CHAR_LENGTH(`code`) ASC')
        ->order_by_asc('name')
        ->limit(setting('smilelist'))
        ->offset($page['offset'])
        ->find_many();

    echo '<form action="/admin/smiles?act=del&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'" method="post">';

    foreach($smiles as $smile) {
        echo '<img src="/uploads/smiles/'.$smile['name'].'" alt=""> — <b>'.$smile['code'].'</b><br>';

        echo '<input type="checkbox" name="del[]" value="'.$smile['id'].'"> <a href="/admin/smiles?act=edit&amp;id='.$smile['id'].'&amp;page='.$page['current'].'">Редактировать</a><br>';
    }

    echo '<br><input type="submit" value="Удалить выбранное"></form>';

    pagination($page);

    echo 'Всего cмайлов: <b>'.$total.'</b><br><br>';

    //showError('Смайлы еще не загружены!');

    echo '<i class="fa fa-upload"></i> <a href="/admin/smiles?act=add&amp;page='.$page['current'].'">Загрузить</a><br>';
break;

/**
 * Форма загрузки смайла
 */
case 'add':

    //setting('newtitle') = 'Добавление смайла';

    echo '<div class="form">';
    echo '<form action="/admin/smiles?act=load&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post" enctype="multipart/form-data">';

    echo 'Прикрепить смайл:<br><input type="file" name="smile"><br>';
    echo 'Код смайла: <br><input type="text" name="code"> <i>Код смайла должен начинаться со знака двоеточия</i><br>';

    echo '<input type="submit" value="Загрузить"></form></div><br>';

    echo 'Разрешается добавлять смайлы с расширением jpg, jpeg, gif, png, bmp<br>';
    echo 'Весом не более '.formatSize(setting('smilemaxsize')).' и размером до '.setting('smilemaxweight').' px<br><br>';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page='.$page.'">Вернуться</a><br>';
break;

/**
 * Загрузка смайла
 */
case 'load':

    //setting('newtitle') = 'Результат добавления';

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utfLower($_POST['code'])) : '';

    if (is_writeable(UPLOADS.'/smiles')){

        $smile = Smile::query()->where('code', $code)->first();

        $validator = new Validator();
        $validator->equal($uid, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->empty($smile, 'Смайл с данным кодом уже имеется в списке!')
            ->length($code, 2, 20, 'Слишком длинный или короткий код смайла!')
            ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!');


        if ($validator->isValid()) {

            $handle = new FileUpload($_FILES['smile']);

            if ($handle -> uploaded) {

                if (! preg_match('/[А-Яа-яЁё]/u', $code)) {
                    $handle -> file_new_name_body = substr($code, 1);
                }
                //$handle -> file_overwrite = true;

                $handle -> ext_check = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
                $handle -> file_max_size = setting('smilemaxsize');  // byte
                $handle -> image_max_width = setting('smilemaxweight');  // px
                $handle -> image_max_height = setting('smilemaxweight'); // px
                $handle -> image_min_width = setting('smileminweight');   // px
                $handle -> image_min_height = setting('smileminweight');  // px
                $handle -> process(UPLOADS.'/smiles/');

                if ($handle -> processed) {

                    $smile = Smile::create();
                    $smile->cats = 1;
                    $smile->name = $handle->file_dst_name;
                    $smile->code = $code;
                    $smile->save();

                    $handle -> clean();
                    clearCache();

                    setFlash('success', 'Смайл успешно загружен!');
                    redirect("/admin/smiles");

                } else {
                    showError($handle->error);
                }
            } else {
                showError($handle->error);
            }
        } else {
            showError($validator->getErrors());
        }
    } else {
        showError('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?act=add&amp;page='.$page.'">Вернуться</a><br>';
break;

/**
 * Редактирование
 */
case 'edit':

    $smile = Smile::query()->find($id);

    if (! empty($smile)) {
        echo '<b><big>Редактирование смайла</big></b><br><br>';
        echo '<img src="/uploads/smiles/'.$smile['name'].'" alt=""> — <b>'.$smile['code'].'</b><br>';

        echo '<div class="form">';
        echo '<form action="/admin/smiles?act=change&amp;id='.$id.'&amp;page='.$page.'&amp;uid='.$_SESSION['token'].'" method="post">';
        echo 'Код смайла:<br>';
        echo '<input type="text" name="code" value="'.$smile['code'].'"> <i>Код смайла должен начинаться со знака двоеточия</i><br>';
        echo '<input type="submit" value="Изменить"></form></div><br>';
    } else {
        showError('Ошибка! Смайла для редактирования не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page='.$page.'">Вернуться</a><br>';
break;

/**
 * Изменение смайла
 */
case 'change':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $code = (isset($_POST['code'])) ? check(utfLower($_POST['code'])) : '';

    $smile = Smile::query()->find($id);

    $checkcode = Smile::select('id')
        ->where('code', $code)
        ->where_not_equal('id', $id)
        ->find_one($id);

    $validator = new Validator();
    $validator->equal($uid, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
        ->notEmpty($smile, 'Не найден смайл для редактирования!')
        ->empty($checkcode, 'Смайл с данным кодом уже имеется в списке!')
        ->length($code, 1, 20, 'Слишком длинный или короткий код смайла!')
        ->regex($code, '|^:+[a-яa-z0-9_\-/\(\)]+$|i', 'Код смайла должен начинаться с двоеточия. Разрешены буквы, цифры и дефис!');

    if ($validator->isValid()) {

        $smile->code = $code;
        $smile->save();

        clearCache();

        setFlash('success', 'Смайл успешно отредактирован!');
        redirect("/admin/smiles?page=$page");


    } else {
        showError($validator->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?act=edit&amp;id='.$id.'&amp;page='.$page.'">Вернуться</a><br>';
break;

/**
 * Удаление смайлов
 */
case 'del':
    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $del = (isset($_POST['del'])) ? intar($_POST['del']) : 0;

    if ($uid == $_SESSION['token']) {
        if (! empty($del)) {
            if (is_writeable(UPLOADS.'/smiles')){

                $arr_smiles = Smile::select('name')->where_id_in($del)->find_many();

                if (count($arr_smiles)>0){
                    foreach ($arr_smiles as $delfile) {
                        if (file_exists(UPLOADS.'/smiles/'.$delfile['name'])) {
                            unlink(UPLOADS.'/smiles/'.$delfile['name']);
                        }
                    }
                }

                Smile::where_id_in($del)->delete_many();
                clearCache();

                setFlash('success', 'Выбранные смайлы успешно удалены!');
                redirect("/admin/smiles?page=$page");

            } else {
                showError('Ошибка! Не установлены атрибуты доступа на дирекоторию со смайлами!');
            }
        } else {
            showError('Ошибка! Отсутствуют выбранные смайлы!');
        }
    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page='.$page.'">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

view(setting('themes').'/foot');
