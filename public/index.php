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

// Временно для переадресации старых ссылок
if ($_SERVER['REQUEST_URI']) {

    $parse = parse_url($_SERVER['REQUEST_URI']);

    if (isset($parse['path'])) {
        if (strpos($parse['path'], '/upload/') !== false) {
            $parse['path'] = str_replace('/upload/', '/uploads/', $parse['path']);
            redirect($parse['path'], true);
        }
    }

    if (isset($parse['path']) && isset($parse['query'])) {

        parse_str($parse['query'], $output);

        if ($parse['path'] == '/forum/topic.php' && isset($output['tid']) && is_numeric($output['tid'])){
           redirect('/topic/'.$output['tid'], true);
        }

        if ($parse['path'] == '/forum/print.php' && isset($output['tid']) && is_numeric($output['tid'])){
            redirect('/topic/'.$output['tid'].'/print', true);
        }

        if ($parse['path'] == '/forum/rss.php' && isset($output['tid']) && is_numeric($output['tid'])){
            redirect('/topic/'.$output['tid'].'/rss', true);
        }

        if ($parse['path'] == '/blog/print.php' && isset($output['id']) && is_numeric($output['id'])){
            redirect('/blog/print?id='.$output['id'], true);
        }

        if (
            $parse['path'] == '/load/down.php' &&
            isset($output['act']) &&
            $output['act'] == 'view' &&
            isset($output['id']) &&
            is_numeric($output['id'])
        ){
            redirect('/load/down?act=view&id='.$output['id'], true);
        }
    }
}

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
