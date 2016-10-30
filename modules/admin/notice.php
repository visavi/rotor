<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$id = (isset($_GET['id'])) ? abs(intval($_GET['id'])) : 0;

if (! is_admin(array(101))) redirect('/admin/');

show_title('Шаблоны писем');

switch ($act):

/**
 * Главная страница
 */
case 'index':

    $total = DBM::run()->count('notice');

    if ($total > 0) {

        $notices = DBM::run()->select('notice', null, null, null, array('notice_id'=>'ASC'));

        foreach ($notices as $notice) {

            echo '<div class="b">';

            echo '<img src="/assets/img/images/mail.gif" alt="image" /> <b><a href="/admin/notice?act=edit&amp;id='.$notice['notice_id'].'">'.$notice['notice_name'].'</a></b>';
            if (empty($notice['notice_protect'])) {
                echo ' (<a href="/admin/notice?act=del&amp;id='.$notice['notice_id'].'&amp;uid='.$_SESSION['token'].'">Удалить</a>)';
            } else {
                echo ' (Системный шаблон)';
            }
            echo '</div>';

            echo '<div>Изменено: ';

            if (!empty($notice['notice_user'])){
                echo profile($notice['notice_user']);
            }

            echo ' ('.date_fixed($notice['notice_time']).')';

            echo '</div>';
        }

        echo '<br />Всего шаблонов: '.$total.'<br /><br />';

    } else {
        show_error('Шаблонов еще нет!');
    }
    echo '<img src="/assets/img/images/open.gif" alt="image" /> <a href="/admin/notice?act=new">Добавить</a><br />';
break;

/**
 * Coздание шаблона
 */
case 'new':
    show_title('Новый шаблон');

    echo '<div class="form">';
    echo '<form action="/admin/notice?act=save&amp;uid='.$_SESSION['token'].'" method="post">';

    echo 'Название: <br />';
    echo '<input type="text" name="name" maxlength="100" size="50" /><br />';
    echo '<textarea id="markItUp" cols="35" rows="20" name="text"></textarea><br />';
    echo '<input name="protect" id="protect" type="checkbox" value="1" /> <label for="protect">Системный шаблон</label><br />';

    echo '<input type="submit" value="Сохранить" /></form></div><br />';

    render('includes/back', array('link' => '/admin/notice', 'title' => 'Вернуться'));
break;

/**
 * Редактирование шаблона
 */
case 'edit':
    $notice = DBM::run()->selectFirst('notice', array('notice_id' => $id));

    if (! empty($notice)) {

        if (! empty($notice['notice_protect'])) {
            echo '<div class="info"><img src="/assets/img/images/warning.gif" alt="image" /> <b>Вы редактируете системный шаблон</b></div><br />';
        }

        echo '<div class="form">';
        echo '<form action="/admin/notice?act=save&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

        echo 'Название: <br />';
        echo '<input type="text" name="name" maxlength="100" size="50" value="'.$notice['notice_name'].'" /><br />';
        echo '<textarea id="markItUp" cols="35" rows="20" name="text">'.$notice['notice_text'].'</textarea><br />';

        $checked = $notice['notice_protect'] ? ' checked="checked"' : '';

        echo '<input name="protect" id="protect" type="checkbox" value="1" '.$checked.' /> <label for="protect">Системный шаблон</label><br />';

        echo '<input type="submit" value="Изменить" /></form></div><br />';

    } else {
        show_error('Ошибка! Шаблона для редактирования не существует!');
    }

    render('includes/back', array('link' => '/admin/notice', 'title' => 'Вернуться'));
break;

/**
 * Сохранение шаблона
 */
case "save":

    $uid = ! empty($_GET['uid']) ? check($_GET['uid']) : 0;
    $name = isset($_POST['name']) ? check($_POST['name']) : '';
    $text = isset($_POST['text']) ? check($_POST['text']) : '';
    $protect = ! empty($_POST['protect']) ? 1 : 0;

    $validation = new Validation();

    $validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('string', $name, 'Слишком длинный или короткий заголовок шаблона!', true, 5, 100)
        -> addRule('string', $text, 'Слишком длинный или короткий текст шаблона!', true, 10, 65000);

    if ($validation->run()) {

        $notice = DBM::run()->selectFirst('notice', array('notice_id' => $id));

        $note = array(
            'notice_name'    => $name,
            'notice_text'    => str_replace('&#37;', '%', $text),
            'notice_user'    => $log,
            'notice_protect' => $protect,
            'notice_time'    => SITETIME,
        );

        if (empty($notice)) {

            $id = DBM::run()->insert('notice', $note);

        } else {

            $note = DBM::run()->update('notice', $note,
                array('notice_id' => $id)
            );
        }

        notice('Шаблон успешно сохранен!');
        redirect("/admin/notice?act=edit&id=$id");

    } else {
        show_error($validation->getErrors());
    }

    render('includes/back', array('link' => '/admin/notice?act=edit&amp;id='.$id, 'title' => 'Вернуться'));
break;

/**
 * Удаление шаблона
 */
case 'del':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;

    $notice = DBM::run()->selectFirst('notice', array('notice_id' => $id));

    $validation = new Validation();

    $validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('not_empty', $notice, 'Не найден шаблон для удаления!')
        -> addRule('empty', $notice['notice_protect'], 'Запрещено удалять защищенный шаблон!');

    if ($validation->run()) {

        $delete = DBM::run()->delete('notice', array('notice_id' => $id));

        notice('Выбранный шаблон успешно удален!');
        redirect("/admin/notice");

    } else {
        show_error($validation->getErrors());
    }

    render('includes/back', array('link' => '/admin/notice', 'title' => 'Вернуться'));
break;

endswitch;

render('includes/back', array('link' => '/admin/', 'title' => 'В админку', 'icon' => 'panel.gif'));

App::view($config['themes'].'/foot');
