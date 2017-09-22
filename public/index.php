<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  admin@visavi.net       #
#             Site  :  http://visavi.net      #
#            Skype  :  vantuzilla             #
#            Phone  :  +79167407574           #
#---------------------------------------------#

use App\Classes\Registry;

include_once __DIR__.'/../app/start.php';
include_once APP.'/redirects.php';

$router = Registry::get('router')->match();

if ($router['target'] && is_callable($router['target'])) {

    call_user_func_array($router['target'], $router['params']);

} elseif ($router['target']) {

    $target     = explode('@', $router['target']);
    $action     = $router['params']['action'] ?? $target[1];
    $controller = 'App\\Controllers\\'.$target[0];

    call_user_func_array([new $controller, $action], $router['params']);
} else {
    abort(404);
}

// setting expiresmail, editstatus
// users confirmreg, ban, emails, nickname
