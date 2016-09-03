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

if (App::router('target') && is_callable(App::router('target'))) {

    call_user_func_array(App::router('target'), $params);

} elseif (App::router('target')) {

    $target = explode('@', App::router('target'));
    $act = isset($params['action']) ? $params['action'] : isset($target[1]) ? $target[1] : 'index';

    include_once (BASEDIR.$target[0]);

} else {
    App::abort(404);
}
if (isset($_SESSION['input'])) unset($_SESSION['input']);


// Удалить карантин с сайта
