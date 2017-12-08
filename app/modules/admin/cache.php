<?php
view(setting('themes').'/index');

if (isAdmin([101])) {
//show_title('Очистка кэша');

switch ($action):
############################################################################################
##                                     Список файлов                                      ##
############################################################################################
case 'index':

    echo '<i class="fa fa-eraser fa-2x"></i> <b>Файлы</b> / <a href="/admin/cache/image">Изображения</a><br><br>';

    $cachefiles = glob(STORAGE.'/temp/*.dat');
    $total = count($cachefiles);

    if (is_array($cachefiles) && $total>0){
        foreach ($cachefiles as $file) {

        echo '<i class="fa fa-file-alt"></i> <b>'.basename($file).'</b>  ('.formatFileSize($file).' / '.dateFixed(filemtime($file)).')<br>';
        }

        echo '<br>Всего файлов: '. $total .'<br><br>';

        echo '<i class="fa fa-trash-alt"></i> <a href="/admin/cache/clear?token='.$_SESSION['token'].'">Очистить кэш</a><br>';
    } else {
        showError('Файлов еще нет!');
    }
break;

############################################################################################
##                                  Список изображений                                    ##
############################################################################################
case 'image':
    $view = (isset($_GET['view'])) ? 1 : 0;

    echo '<i class="fa fa-eraser fa-2x"></i> <a href="/admin/cache">Файлы</a> / <b>Изображения</b><br><br>';

    $cachefiles = glob(UPLOADS.'/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
    $total = count($cachefiles);

    $totals = ($total>50 && $view!=1) ? 50 : $total;

    if (is_array($cachefiles) && $totals>0){
        for ($i=0; $i<$totals; $i++) {

        echo '<i class="fa fa-image"></i> <b>'.basename($cachefiles[$i]).'</b>  ('.formatFileSize($cachefiles[$i]).' / '.dateFixed(filemtime($cachefiles[$i])).')<br>';
        }

        if ($total>$totals){
            echo '<br><b><a href="/admin/cache/image?view=1">Показать все</a></b>';
        }

        echo '<br>Всего изображений: '. $total .'<br><br>';

        echo '<i class="fa fa-trash-alt"></i> <a href="/admin/cache/clearimage?token='.$_SESSION['token'].'">Очистить кэш</a><br>';
    } else {
        showError('Изображений еще нет!');
    }
break;

############################################################################################
##                                    Очистка файлов                                       ##
############################################################################################
case 'clear':

    $token = check($_GET['token']);

    if ($token == $_SESSION['token']) {

        clearCache();

        setFlash('success', 'Кэш-файлы успешно удалены!');
        redirect("/admin/cache");

    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/cache">Вернуться</a><br>';
break;

############################################################################################
##                                 Очистка изображений                                    ##
############################################################################################
case 'clearimage':

    $token = check($_GET['token']);

    if ($token == $_SESSION['token']) {

        $cachefiles = glob(UPLOADS.'/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
        $total = count($cachefiles);

        if (is_array($cachefiles) && $total>0){
            foreach ($cachefiles as $file) {

                unlink ($file);
            }
        }

        setFlash('success', 'Изображения успешно удалены!');
        redirect("/admin/cache/image");

    } else {
        showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
    }

    echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/cache/image">Вернуться</a><br>';
break;

endswitch;

echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>';

} else {
    redirect('/');
}

view(setting('themes').'/foot');
