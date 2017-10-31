<?php
view(setting('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

if (! isAdmin([101])) redirect('/admin/');

//show_title('Шаблоны писем');

switch ($action):

/**
 * Главная страница
 */
case 'index':

    $total = Notice::count();

    if ($total > 0) {

        $notices = Notice::orderBy('id')
            ->with('user')
            ->get();

        foreach ($notices as $notice) {

            echo '<div class="b">';

            echo '<i class="fa fa-envelope"></i> <b><a href="/admin/notice?act=edit&amp;id='.$notice['id'].'">'.$notice['name'].'</a></b>';
            if (empty($notice['protect'])) {
                echo ' (<a href="/admin/notice?act=del&amp;id='.$notice['id'].'&amp;token='.$_SESSION['token'].'">Удалить</a>)';
            } else {
                echo ' (Системный шаблон)';
            }
            echo '</div>';

            echo '<div>Изменено: ';

            echo profile($notice['user']);

            echo ' ('.dateFixed($notice['updated_at']).')';

            echo '</div>';
        }

        echo '<br>Всего шаблонов: '.$total.'<br><br>';

    } else {
        showError('Шаблонов еще нет!');
    }
    echo '<i class="fa fa-check"></i> <a href="/admin/notice?act=new">Добавить</a><br>';
break;

/**
 * Coздание шаблона
 */
case 'new':
    //show_title('Новый шаблон');

    echo '<div class="form">';
    echo '<form action="/admin/notice?act=save" method="post">';
    echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
    echo 'Название: <br>';
    echo '<input type="text" name="name" maxlength="100" size="50"><br>';
    echo '<textarea id="markItUp" cols="35" rows="20" name="text"></textarea><br>';
    echo '<input name="protect" id="protect" type="checkbox" value="1"> <label for="protect">Системный шаблон</label><br>';

    echo '<input type="submit" value="Сохранить"></form></div><br>';

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/notice">Вернуться</a><br>';
break;

/**
 * Редактирование шаблона
 */
case 'edit':
    $notice = Notice::query()->find($id);
    if ($notice) {

        if ($notice['protect']) {
            echo '<div class="info"><i class="fa fa-exclamation-circle"></i> <b>Вы редактируете системный шаблон</b></div><br>';
        }

        echo '<div class="form">';
        echo '<form action="/admin/notice?act=save&amp;id='.$id.'" method="post">';
        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';
        echo 'Название: <br>';
        echo '<input type="text" name="name" maxlength="100" size="50" value="'.$notice['name'].'"><br>';
        echo '<textarea id="markItUp" cols="35" rows="20" name="text">'.$notice['text'].'</textarea><br>';

        $checked = $notice['protect'] ? ' checked' : '';

        echo '<input name="protect" id="protect" type="checkbox" value="1" '.$checked.'> <label for="protect">Системный шаблон</label><br>';

        echo '<input type="submit" value="Изменить"></form></div><br>';

    } else {
        showError('Ошибка! Шаблона для редактирования не существует!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/notice">Вернуться</a><br>';
break;

/**
 * Сохранение шаблона
 */
case 'save':

    $token = check(Request::input('token'));
    $name = isset($_POST['name']) ? check($_POST['name']) : '';
    $text = isset($_POST['text']) ? check($_POST['text']) : '';
    $protect = ! empty($_POST['protect']) ? 1 : 0;

    $validator = new Validator();
    $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
        ->length($name, 5, 100, 'Слишком длинный или короткий заголовок шаблона!')
        ->length($text, 10, 65000, 'Слишком длинный или короткий текст шаблона!');

    if ($validator->isValid()) {

        $note = [
            'name'       => $name,
            'text'       => $text,
            'user_id'    => getUser('id'),
            'protect'    => $protect,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
        ];

        $notice = Notice::updateOrCreate(
            ['id' => $id],
            $note
        );

        setFlash('success', 'Шаблон успешно сохранен!');
        redirect('/admin/notice?act=edit&id='.$notice->id);

    } else {
        showError($validator->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/notice?act=edit&amp;id='.$id.'">Вернуться</a><br>';
break;

/**
 * Удаление шаблона
 */
case 'del':

    $token = check(Request::input('token'));

    $notice = Notice::query()->find($id);

    $validator = new Validator();
    $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
        ->notEmpty($notice, 'Не найден шаблон для удаления!')
        ->empty($notice['protect'], 'Запрещено удалять защищенный шаблон!');

    if ($validator->isValid()) {

        $notice->delete();

        setFlash('success', 'Выбранный шаблон успешно удален!');
        redirect('/admin/notice');

    } else {
        showError($validator->getErrors());
    }

    echo '<i class="fa-arrow-circle-left"></i> <a href="/admin/notice">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

view(setting('themes').'/foot');
