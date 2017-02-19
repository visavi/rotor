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

// Временно для переадресации старых ссылок
if ($_SERVER['REQUEST_URI']) {

    $parse = parse_url($_SERVER['REQUEST_URI']);

    if (isset($parse['path'])) {
        if (strpos($parse['path'], '/upload/') !== false) {
            $parse['path'] = str_replace('/upload/', '/uploads/', $parse['path']);
            redirect($parse['path'], true);
        }
    }

    if (isset($parse['path']) && $parse['path'] == '/news/rss.php'){
        redirect('/news/rss', true);
    }

    if (isset($parse['path']) && ($parse['path'] == '/services/' || $parse['path'] == '/services')){
        redirect('/files', true);
    }

    if (isset($parse['path']) && isset($parse['query'])) {

        parse_str($parse['query'], $output);

        if ($parse['path'] == '/forum/forum.php' && isset($output['fid']) && is_numeric($output['fid'])){
            redirect('/forum/'.$output['fid'], true);
        }

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
            $parse['path'] == '/forum/active.php' &&
            isset($output['act']) &&
            isset($output['uz'])
        ){
            redirect('/forum/active/'.$output['act'].'?user='.$output['uz'], true);
        }

        if (
            $parse['path'] == '/load/active.php' &&
            isset($output['act']) &&
            isset($output['uz'])
        ){
            redirect('/load/active?act='.$output['act'].'&uz='.$output['uz'], true);
        }

        if (
            $parse['path'] == '/blog/active.php' &&
            isset($output['act']) &&
            isset($output['uz'])
        ){
            redirect('/blog/active?act='.$output['act'].'&uz='.$output['uz'], true);
        }

        if (
            $parse['path'] == '/blog/blog.php' &&
            isset($output['act']) &&
            $output['act'] == 'view' &&
            isset($output['id']) &&
            is_numeric($output['id'])
        ){
            redirect('/blog/blog?act=view&id='.$output['id'], true);
        }

        if (
            $parse['path'] == '/load/zip.php' &&
            isset($output['act']) &&
            $output['act'] == 'preview' &&
            isset($output['id']) &&
            is_numeric($output['id']) &&
            isset($output['view']) &&
            isset($output['img'])
        ){
            redirect('/load/zip?act=preview&id='.$output['id'].'&view='.$output['view'].'&img=1', true);
        }

        if (
            $parse['path'] == '/load/zip.php' &&
            isset($output['act']) &&
            $output['act'] == 'preview' &&
            isset($output['id']) &&
            is_numeric($output['id']) &&
            isset($output['view']) &&
            is_numeric($output['view'])
        ){
            redirect('/load/zip?act=preview&id='.$output['id'].'&view='.$output['view'], true);
        }

        if ($parse['path'] == '/load/zip.php' && isset($output['id']) && is_numeric($output['id'])){
            redirect('/load/zip?id='.$output['id'], true);
        }


        if (
            $parse['path'] == '/load/down.php' &&
            isset($output['act']) &&
            isset($output['id']) &&
            is_numeric($output['id'])
        ){
            redirect('/load/down?act='.$output['act'].'&id='.$output['id'], true);
        }

        if ($parse['path'] == '/gallery/index.php' && isset($output['act']) &&
            $output['act'] == 'view' && isset($output['gid']) && is_numeric($output['gid'])){
            redirect('/gallery?act=view&gid='.$output['gid'], true);
        }

        if ($parse['path'] == '/gallery/album.php' && isset($output['act']) &&
            $output['act'] == 'photo' && isset($output['uz'])){
            redirect('/gallery/album?act=photo&uz='.$output['uz'], true);
        }

        if ($parse['path'] == '/gallery/comments.php' && isset($output['act']) &&
            $output['act'] == 'comments' && isset($output['uz'])){
            redirect('/gallery/comments?act=comments&uz='.$output['uz'], true);
        }

        if ($parse['path'] == '/pages/wall.php' && isset($output['uz'])){
            redirect('/wall?uz='.$output['uz'], true);
        }

        if ($parse['path'] == '/pages/user.php' && isset($output['uz'])){
            redirect('/user/'.$output['uz'], true);
        }
    }

}
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


// Удалить resmiles
