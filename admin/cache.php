<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;

if (is_admin(array(101))) {
show_title('Очистка кэша');

switch ($act):
############################################################################################
##                                     Список файлов                                      ##
############################################################################################
case 'index':

	echo '<i class="fa fa-eraser fa-2x"></i> <b>Файлы</b> / <a href="cache.php?act=image">Изображения</a><br /><br />';

	$cachefiles = glob(DATADIR.'/temp/*.dat');
	$total = count($cachefiles);

	if (is_array($cachefiles) && $total>0){
		foreach ($cachefiles as $file) {

		echo '<i class="fa fa-file-text-o"></i> <b>'.basename($file).'</b>  ('.read_file($file).' / '.date_fixed(filemtime($file)).')<br />';
		}

		echo '<br />Всего файлов: '. $total .'<br /><br />';

		echo '<i class="fa fa-trash-o"></i> <a href="cache.php?act=del&amp;uid='.$_SESSION['token'].'">Очистить кэш</a><br />';
	} else {
		show_error('Файлов еще нет!');
	}
break;

############################################################################################
##                                  Список изображений                                    ##
############################################################################################
case 'image':
	$view = (isset($_GET['view'])) ? 1 : 0;

	echo '<i class="fa fa-eraser fa-2x"></i> <a href="cache.php">Файлы</a> / <b>Изображения</b><br /><br />';

	$cachefiles = glob(BASEDIR.'/upload/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
	$total = count($cachefiles);

	$totals = ($total>50 && $view!=1) ? 50 : $total;

	if (is_array($cachefiles) && $totals>0){
		for ($i=0; $i<$totals; $i++) {

		echo '<i class="fa fa-picture-o"></i> <b>'.basename($cachefiles[$i]).'</b>  ('.read_file($cachefiles[$i]).' / '.date_fixed(filemtime($cachefiles[$i])).')<br />';
		}

		if ($total>$totals){
			echo '<br /><b><a href="cache.php?act=image&amp;view=1">Показать все</a></b>';
		}

		echo '<br />Всего изображений: '. $total .'<br /><br />';

		echo '<i class="fa fa-trash-o"></i> <a href="cache.php?act=delimage&amp;uid='.$_SESSION['token'].'">Очистить кэш</a><br />';
	} else {
		show_error('Изображений еще нет!');
	}
break;

############################################################################################
##                                    Очистка файлов                                       ##
############################################################################################
case 'del':

	$uid = check($_GET['uid']);

	if ($uid == $_SESSION['token']) {

		clearCache();

		notice('Кэш-файлы успешно удалены!');
		redirect("cache.php");

	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}

	render('includes/back', array('link' => '/admin/cache.php', 'title' => 'Вернуться'));
break;

############################################################################################
##                                 Очистка изображений                                    ##
############################################################################################
case 'delimage':

	$uid = check($_GET['uid']);

	if ($uid == $_SESSION['token']) {

		$cachefiles = glob(BASEDIR.'/upload/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
		$total = count($cachefiles);

		if (is_array($cachefiles) && $total>0){
			foreach ($cachefiles as $file) {

				unlink ($file);
			}
		}

		notice('Изображения успешно удалены!');
		redirect("cache.php?act=image");

	} else {
		show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
	}


	render('includes/back', array('link' => '/admin/cache.php?act=image', 'title' => 'Вернуться'));
break;

default:
	redirect("cache.php");
endswitch;

echo '<i class="fa fa-wrench"></i> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
