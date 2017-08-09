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
    $action = $target[1] ?? $router['params']['action'];

    call_user_func_array([new $target[0], $action], $router['params']);
} else {
    App::abort(404);
}
