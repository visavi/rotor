<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('includes/start.php');
require_once ('includes/functions.php');
require_once ('includes/header.php');

$browser_detect = new Mobile_Detect();

// ------------------------ Автоопределение системы -----------------------------//
if (!is_user() || empty($config['themes'])) {
    if (!empty($config['touchthemes'])) {
        if ($browser_detect->isTablet()) {
            $config['themes'] = $config['touchthemes'];
        }
    }

    if (!empty($config['webthemes'])) {
        if (!$browser_detect->isMobile() && !$browser_detect->isTablet()) {
            $config['themes'] = $config['webthemes'];
        }
    }
}

if (empty($config['themes'])) {
    $config['themes'] = 'default';
}

/*if ($config['closedsite'] == 2 && !is_admin() && !strsearch($php_self, array('/pages/closed.php', '/input.php'))) {
    redirect('/pages/closed.php');
}

if ($config['closedsite'] == 1 && !is_user() && !strsearch($php_self, array('/pages/login.php', '/pages/registration.php', '/mail/lostpassword.php', '/input.php'))) {
    notice('Для входа на сайт необходимо авторизоваться!');
    redirect('/login');
}*/

$params = App::router('params');
$target = App::router('target');

if ($target && is_callable($target)) {

    call_user_func_array($target, $params);

} elseif ($target) {

    $target = explode('@', $target);

    $act = isset($target[1]) ? $target[1] : 'index';

    if (isset($params['action'])) {
        $act = $params['action'];
    }

    include_once (BASEDIR.$target[0]);

} else {
    App::abort(404);
}
if (isset($_SESSION['input'])) unset($_SESSION['input']);


// Удалить карантин с сайта
// Удалить дайджест (таблица visit)
// Удалить $config['cache']
