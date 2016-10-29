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

    echo '<img src="/images/img/document.gif" alt="image" /> ';
    echo '<a href="/user/'.App::getUsername().'">Моя анкета</a> / ';
    echo '<a href="/profile">Мой профиль</a> / ';
    echo '<a href="/account">Мои данные</a> / ';
    echo '<b>Настройки</b><hr />';

    echo '<div class="form">';
    echo '<form method="post" action="setting?act=edit&amp;uid='.$_SESSION['token'].'">';

    echo 'Wap-тема по умолчанию:<br />';
    echo '<select name="themes">';
    echo '<option value="0">Автоматически</option>';
    $globthemes = glob(BASEDIR."/themes/*", GLOB_ONLYDIR);
    foreach ($globthemes as $themes) {
        $selected = ($udata['users_themes'] == basename($themes)) ? ' selected="selected"' : '';
        echo '<option value="'.basename($themes).'"'.$selected.'>'.basename($themes).'</option>';
    }
    echo '</select><br />';

    echo 'Сообщений в гостевой:<br /><input name="postguest" value="'.$udata['users_postguest'].'" /><br />';
    echo 'Новостей на стр.:<br /><input name="postnews" value="'.$udata['users_postnews'].'" /><br />';
    echo 'Писем в привате на стр.:<br /><input name="postprivat" value="'.$udata['users_postprivat'].'" /><br />';
    echo 'Сообщений в форуме:<br /><input name="postforum" value="'.$udata['users_postforum'].'" /><br />';
    echo 'Тем в форуме:<br /><input name="themesforum" value="'.$udata['users_themesforum'].'" /><br />';
    echo 'Объявлений на стр.:<br /><input name="postboard" value="'.$udata['users_postboard'].'" /><br />';

    $arrtimezone = range(-12, 12);

    echo 'Временной сдвиг:<br />';
    echo '<select name="timezone">';
    foreach($arrtimezone as $zone) {
        $selected = ($udata['users_timezone'] == $zone) ? ' selected="selected"' : '';
        echo '<option value="'.$zone.'"'.$selected.'>'.$zone.'</option>';
    }
    echo '</select> - '.date_fixed(SITETIME, 'H:i').'<br />';

    $checked = ($udata['users_ipbinding'] == 1) ? ' checked="checked"' : '';
    echo '<input name="ipbinding" id="ipbinding" type="checkbox" value="1"'.$checked.' title="IP привязывается к сессии, улучшается надежность" /> <label for="ipbinding">Привязка к IP</label><br />';

    $checked = ($udata['users_privacy'] == 1) ? ' checked="checked"' : '';
    echo '<input name="privacy" id="privacy" type="checkbox" value="1"'.$checked.' title="Писать в приват и на стену смогут только пользователи из контактов" /> <label for="privacy">Режим приватности</label><br />';

    $checked = (! empty($udata['users_subscribe'])) ? ' checked="checked"' : '';
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
    $postguest = (isset($_POST['postguest'])) ? abs(intval($_POST['postguest'])) : 0;
    $postnews = (isset($_POST['postnews'])) ? abs(intval($_POST['postnews'])) : 0;
    $postprivat = (isset($_POST['postprivat'])) ? abs(intval($_POST['postprivat'])) : 0;
    $postforum = (isset($_POST['postforum'])) ? abs(intval($_POST['postforum'])) : 0;
    $themesforum = (isset($_POST['themesforum'])) ? abs(intval($_POST['themesforum'])) : 0;
    $postboard = (isset($_POST['postboard'])) ? abs(intval($_POST['postboard'])) : 0;
    $timezone = (isset($_POST['timezone'])) ? check($_POST['timezone']) : 0;
    $ipbinding = (empty($_POST['ipbinding'])) ? 0 : 1;
    $privacy = (empty($_POST['privacy'])) ? 0 : 1;
    $subscribe = (! empty($_POST['subscribe'])) ? generate_password(32) : '';

    $validation = new Validation();

    $validation -> addRule('equal', array($uid, $_SESSION['token']), 'Неверный идентификатор сессии, повторите действие!')
        -> addRule('regex', array($themes, '|^[a-z0-9_\-]+$|i'), 'Недопустимое название темы!', true)
        -> addRule('numeric', $postguest, 'Количество сообщений в гостевой. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('numeric', $postnews, 'Количество новостей на страницу. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('numeric', $postprivat, 'Количество приватных сообщений. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('numeric', $postforum, 'Количество сообщения в форуме. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('numeric', $themesforum, 'Количество тем в форуме. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('numeric', $postboard, 'Количество объявлений на страницу. (Допустимое значение от 3 до 50)!', true, 3, 50)
        -> addRule('regex', array($timezone, '|^[\-\+]{0,1}[0-9]{1,2}$|'), 'Недопустимое значение временного сдвига. (Допустимый диапазон -12 — +12 часов)!', true);

    if ($validation->run()) {
        if (file_exists(BASEDIR."/themes/$themes/index.php") || $themes==0) {

            $user = DBM::run()->update('users', array(
                'users_themes'      => $themes,
                'users_postguest'   => $postguest,
                'users_postnews'    => $postnews,
                'users_postprivat'  => $postprivat,
                'users_postforum'   => $postforum,
                'users_themesforum' => $themesforum,
                'users_postboard'   => $postboard,
                'users_timezone'    => $timezone,
                'users_ipbinding'   => $ipbinding,
                'users_privacy'     => $privacy,
                'users_subscribe'   => $subscribe,
            ), array(
                'users_login' => $log
            ));

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
