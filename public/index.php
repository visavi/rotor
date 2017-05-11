<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  admin@visavi.net       #
#             Site  :  http://visavi.net      #
#            Skype  :  vantuzilla             #
#            Phone  :  +79167407574           #
#---------------------------------------------#
include_once __DIR__.'/../app/start.php';
include_once APP.'/redirects.php';

$router = Registry::get('router')->match();

if ($router['target'] && is_callable($router['target'])) {

    call_user_func_array($router['target'], $router['params']);

} elseif ($router['target']) {

    $target = explode('@', $router['target']);

    $act = isset($target[1]) ? $target[1] : 'index';

    if (isset($router['params']['action'])) {
        $act = $router['params']['action'];
    }

    include_once (APP.'/modules/'.$target[0]);

} else {
    App::abort(404);
}
if (isset($_SESSION['input'])) unset($_SESSION['input']);
