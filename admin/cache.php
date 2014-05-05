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

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

if (is_admin(array(101))) {
	show_title('Очистка кэша');

	switch ($act):
	############################################################################################
	##                                     Список файлов                                      ##
	############################################################################################
		case 'index':

			echo '<img src="/images/img/eraser.gif" alt="image" /> <b>Файлы</b> / <a href="cache.php?act=image">Изображения</a><br /><br />';

			$cachefiles = glob(DATADIR.'/temp/*.dat');
			$total = count($cachefiles);

			if (is_array($cachefiles) && $total>0){
				foreach ($cachefiles as $file) {

				echo '<img src="/images/img/layer.gif" alt="image" /> <b>'.basename($file).'</b>  ('.read_file($file).' / '.date_fixed(filemtime($file)).')<br />';
				}

				echo '<br />Всего файлов: '. $total .'<br /><br />';

				echo '<img src="/images/img/error.gif" alt="image" /> <a href="cache.php?act=del&amp;uid='.$_SESSION['token'].'">Очистить кэш</a><br />';
			} else {
				show_error('Файлов еще нет!');
			}
		break;

	############################################################################################
	##                                  Список изображений                                    ##
	############################################################################################
	case 'image':
		$view = (isset($_GET['view'])) ? 1 : 0;

		echo '<img src="/images/img/eraser.gif" alt="image" /> <a href="cache.php">Файлы</a> / <b>Изображения</b><br /><br />';

		$cachefiles = glob(BASEDIR.'/upload/thumbnail/*.{gif,png,jpg,jpeg}', GLOB_BRACE);
		$total = count($cachefiles);

		$totals = ($total>50 && $view!=1) ? 50 : $total;

		if (is_array($cachefiles) && $totals>0){
			for ($i=0; $i<$totals; $i++) {

			echo '<img src="/images/img/gallery.gif" alt="image" /> <b>'.basename($cachefiles[$i]).'</b>  ('.read_file($cachefiles[$i]).' / '.date_fixed(filemtime($cachefiles[$i])).')<br />';
			}

			if ($total>$totals){
				echo '<br /><b><a href="cache.php?act=image&amp;view=1">Показать все</a></b>';
			}

			echo '<br />Всего изображений: '. $total .'<br /><br />';

			echo '<img src="/images/img/error.gif" alt="image" /> <a href="cache.php?act=delimage&amp;uid='.$_SESSION['token'].'">Очистить кэш</a><br />';
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

			$cachefiles = glob(DATADIR.'/temp/*.dat');
			$cachefiles = array_diff($cachefiles, array(DATADIR.'/temp/checker.dat', DATADIR.'/temp/counter7.dat'));

			if (is_array($cachefiles) && count($cachefiles)>0){
				foreach ($cachefiles as $file) {

					unlink ($file);
				}
			}

			// Авто-кэширование данных
			save_navigation();
			save_ipban();

			$_SESSION['note'] = 'Кэш-файлы успешно удалены!';
			redirect("cache.php");

		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="cache.php">Вернуться</a><br />';
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

			$_SESSION['note'] = 'Изображения успешно удалены!';
			redirect("cache.php?act=image");

		} else {
			show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="cache.php?act=image">Вернуться</a><br />';
	break;

	default:
		redirect("cache.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
