<?php
App::view($config['themes'].'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Мои настройки');

if (is_user()) {
switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
case 'index':

    echo '<i class="fa fa-book"></i> ';
    echo '<a href="/user/'.App::getUsername().'">Моя анкета</a> / ';
    echo '<a href="/profile">Мой профиль</a> / ';
    echo '<a href="/account">Мои данные</a> / ';
    echo '<b>Настройки</b><hr />';

    echo '<div class="form">';
    echo '<form method="post" action="setting?act=edit&amp;uid='.$_SESSION['token'].'">';

    echo 'Wap-тема по умолчанию:<br />';
    echo '<select name="themes">';
    echo '<option value="0">Автоматически</option>';
    $globthemes = glob(HOME."/themes/*", GLOB_ONLYDIR);
    foreach ($globthemes as $themes) {
        $selected = ($udata['themes'] == basename($themes)) ? ' selected="selected"' : '';
        echo '<option value="'.basename($themes).'"'.$selected.'>'.basename($themes).'</option>';
    }
    echo '</select><br />';

    $arrtimezone = range(-12, 12);

    echo 'Временной сдвиг:<br />';
    echo '<select name="timezone">';
    foreach($arrtimezone as $zone) {
        $selected = ($udata['timezone'] == $zone) ? ' selected="selected"' : '';
        echo '<option value="'.$zone.'"'.$selected.'>'.$zone.'</option>';
    }
    echo '</select> - '.date_fixed(SITETIME, 'H:i').'<br />';

    $checked = ($udata['privacy'] == 1) ? ' checked="checked"' : '';
    echo '<input name="privacy" id="privacy" type="checkbox" value="1"'.$checked.' title="Писать в приват и на стену смогут только пользователи из контактов" /> <label for="privacy">Режим приватности</label><br />';

    $checked = (! empty($udata['subscribe'])) ? ' checked="checked"' : '';
    echo '<input name="subscribe" id="subscribe" type="checkbox" value="1"'.$checked.' title="Получение уведомлений с сайта на email" /> <label for="subscribe">Получать информационные письма</label><br />';

    echo '<input value="Изменить" type="submit" /></form></div><br />';

    echo '* Значение всех полей (max.50)<br /><br />';
break;

############################################################################################
##                                       Изменение                                        ##
############################################################################################
case 'edit':

    $uid = (!empty($_GET['uid'])) ? check($_GET['uid']) : 0;
    $themes = (isset($_POST['themes'])) ? check($_POST['themes']) : '';
    $timezone = (isset($_POST['timezone'])) ? check($_POST['timezone']) : 0;
    $privacy = (empty($_POST['privacy'])) ? 0 : 1;
    $subscribe = (! empty($_POST['subscribe'])) ? generate_password(32) : '';

    $validation = new Validation();

    $validation -> addRule('equal', [$uid, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('regex', [$themes, '|^[a-z0-9_\-]+$|i'], 'Недопустимое название темы!', true)
        -> addRule('regex', [$timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|'], 'Недопустимое значение временного сдвига. (Допустимый диапазон -12 — +12 часов)!', true);

    if ($validation->run()) {
        if (file_exists(HOME."/themes/$themes/index.php") || $themes==0) {

            $user = DBM::run()->update('users', [
                'themes'      => $themes,
                'timezone'    => $timezone,
                'privacy'     => $privacy,
                'subscribe'   => $subscribe,
            ], [
                'login' => $log
            ]);

            notice('Настройки успешно изменены!');
            redirect("/setting");

        } else {
            show_error('Ошибка! Данный скин не установлен на сайте!');
        }
    } else {
        show_error($validation->getErrors());
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/setting">Вернуться</a><br />';
break;

endswitch;
} else {
    show_login('Вы не авторизованы, чтобы изменять настройки, необходимо');
}

App::view($config['themes'].'/foot');
