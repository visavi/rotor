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

if (empty($_GET['uz'])) {
	$uz = check($log);
} else {
	$uz = check($_GET['uz']);
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}

switch ($act):
############################################################################################
##                                  Вывод комментариев                                    ##
############################################################################################
	case 'index':
		show_title('Альбомы пользователей');

		$total = DB::run() -> querySingle("select COUNT(DISTINCT `photo_user`) from `photo`");

		if ($total > 0) {
			if ($start >= $total) {
				$start = last_page($total, $config['photogroup']);
			}

			$page = floor(1 + $start / $config['photogroup']);
			$config['newtitle'] = 'Альбомы пользователей (Стр. '.$page.')';

			$queryphoto = DB::run() -> query("SELECT COUNT(*) AS cnt, SUM(`photo_comments`) AS comments, `photo_user` FROM `photo` GROUP BY `photo_user` ORDER BY cnt DESC LIMIT ".$start.", ".$config['photogroup'].";");

			while ($data = $queryphoto -> fetch()) {

				echo '<img src="/images/img/gallery.gif" alt="image" /> ';
				echo '<b><a href="album.php?act=photo&amp;uz='.$data['photo_user'].'">'.nickname($data['photo_user']).'</a></b> ('.$data['cnt'].' фото / '.$data['comments'].' комм.)<br />';
			}

			page_strnavigation('album.php?', $config['photogroup'], $start, $total);

			echo 'Всего альбомов: <b>'.$total.'</b><br /><br />';

		} else {
			show_error('Альбомов еще нет!');
		}
	break;

	############################################################################################
	##                               Просмотр по пользователям                                ##
	############################################################################################
	case 'photo':

		show_title('Список всех фотографий '.nickname($uz));

		$total = DB::run() -> querySingle("SELECT count(*) FROM `photo` WHERE `photo_user`=?;", array($uz));

		if ($total > 0) {
			if ($start >= $total) {
				$start = last_page($total, $config['fotolist']);
			}

			$page = floor(1 + $start / $config['fotolist']);
			$config['newtitle'] = 'Список всех фотографий '.nickname($uz).' (Стр. '.$page.')';

			$queryphoto = DB::run() -> query("SELECT * FROM `photo` WHERE `photo_user`=? ORDER BY `photo_time` DESC LIMIT ".$start.", ".$config['fotolist'].";", array($uz));

			$moder = ($log == $uz) ? 1 : 0;

			while ($data = $queryphoto -> fetch()) {
				echo '<div class="b"><img src="/images/img/gallery.gif" alt="image" /> ';
				echo '<b><a href="index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.$data['photo_title'].'</a></b> ('.read_file(BASEDIR.'/upload/pictures/'.$data['photo_link']).')<br />';

				if (!empty($moder)) {
					echo '<a href="index.php?act=edit&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">Редактировать</a> / ';
					echo '<a href="index.php?act=delphoto&amp;gid='.$data['photo_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
				}

				echo '</div><div>';
				echo '<a href="index.php?act=view&amp;gid='.$data['photo_id'].'&amp;start='.$start.'">'.resize_image('upload/pictures/', $data['photo_link'], $config['previewsize'], $data['photo_title']).'</a><br />';

				if (!empty($data['photo_text'])){
					echo bb_code($data['photo_text']).'<br />';
				}

				echo 'Добавлено: '.profile($data['photo_user']).' ('.date_fixed($data['photo_time']).')<br />';
				echo '<a href="index.php?act=comments&amp;gid='.$data['photo_id'].'">Комментарии</a> ('.$data['photo_comments'].')';
				echo '</div>';
			}

			page_strnavigation('album.php?act=photo&amp;uz='.$uz.'&amp;', $config['fotolist'], $start, $total);

			echo 'Всего фотографий: <b>'.$total.'</b><br /><br />';
		} else {
			show_error('Фотографий в альбоме еще нет!');
		}

		echo '<img src="/images/img/reload.gif" alt="image" /> <a href="album.php">Альбомы</a><br />';
	break;


default:
	redirect("album.php");
endswitch;

echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">В галерею</a><br />';

include_once ('../themes/footer.php');
?>
