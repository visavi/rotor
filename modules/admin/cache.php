<?php
App::view($config['themes'].'/index');

$start = abs(intval(Request::input('start', 0)));

if (is_admin(array(101))) {
show_title('Очистка кэша');

switch ($act):
############################################################################################
##                                     Список файлов                                      ##
############################################################################################
case 'index':

    echo '<i class="fa fa-eraser fa-2x"></i> <b>Файлы</b> / <a href="/admin/cache/image">Изображения</a><br /><br />';

    $cachefiles = glob(DATADIR.'/temp/*.dat');
    $total = count($cachefiles);

    if (is_array($cachefiles) && $total>0){
        foreach ($cachefiles as $file) {

        echo '<i class="fa fa-file-text-o"></i> <b>'.basename($file).'</b>  ('.read_file($file).' / '.date_fixed(filemtime($file)).')<br />';
        }

        echo '<br />Всего файлов: '. $total .'<br /><br />';

        echo '<i class="fa fa-trash-o"></i> <a href="/admin/cache/clear?token='.$_SESSION['token'].'">Очистить кэш</a><br />';
    } else {
        show_error('Файлов еще нет!');
    }
break;

############################################################################################
##                                  Список изображений                                    ##
############################################################################################
case 'image':
    $view = (isset($_GET['view'])) ? 1 : 0;

    echo '<i class="fa fa-eraser fa-2x"></i> <a href="/admin/cache">Файлы</a> / <b>Изображения</b><br /><br />';

    $cachefiles = glob(BASEDIR.'/upload/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
    $total = count($cachefiles);

    $totals = ($total>50 && $view!=1) ? 50 : $total;

    if (is_array($cachefiles) && $totals>0){
        for ($i=0; $i<$totals; $i++) {

        echo '<i class="fa fa-picture-o"></i> <b>'.basename($cachefiles[$i]).'</b>  ('.read_file($cachefiles[$i]).' / '.date_fixed(filemtime($cachefiles[$i])).')<br />';
        }

        if ($total>$totals){
            echo '<br /><b><a href="/admin/cache/image?view=1">Показать все</a></b>';
        }

        echo '<br />Всего изображений: '. $total .'<br /><br />';

        echo '<i class="fa fa-trash-o"></i> <a href="/admin/cache/clearimage?token='.$_SESSION['token'].'">Очистить кэш</a><br />';
    } else {
        show_error('Изображений еще нет!');
    }
break;

############################################################################################
##                                    Очистка файлов                                       ##
############################################################################################
case 'clear':

    $token = check($_GET['token']);

    if ($token == $_SESSION['token']) {

        clearCache();

        App::setFlash('success', 'Кэш-файлы успешно удалены!');
        redirect("/admin/cache");

    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    render('includes/back', array('link' => '/admin/cache', 'title' => 'Вернуться'));
break;

############################################################################################
##                                 Очистка изображений                                    ##
############################################################################################
case 'clearimage':

    $token = check($_GET['token']);

    if ($token == $_SESSION['token']) {

        $cachefiles = glob(BASEDIR.'/upload/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
        $total = count($cachefiles);

        if (is_array($cachefiles) && $total>0){
            foreach ($cachefiles as $file) {

                unlink ($file);
            }
        }

        App::setFlash('success', 'Изображения успешно удалены!');
        redirect("/admin/cache/image");

    } else {
        show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }


    render('includes/back', array('link' => '/admin/cache/image', 'title' => 'Вернуться'));
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect('/');
}

App::view($config['themes'].'/foot');
