<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#            Skype  :  vantuzilla             #
#            Phone  :  +79167407574           #
#---------------------------------------------#
include_once __DIR__.'/../app/start.php';

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

    include_once (APP.$target[0]);

} else {
    App::abort(404);
}
if (isset($_SESSION['input'])) unset($_SESSION['input']);


// Изменить поля в таблицах key = relate, order => sort, topic_forums => topic_id итд
