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
