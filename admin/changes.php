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

if (is_admin()) {
	show_title('Новости RotorCMS');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			if (file_exists(DATADIR."/temp/changes.dat")) {
				$data = file_get_contents(DATADIR."/temp/changes.dat");

				if (is_serialized($data)) {
					$data = unserialize($data);

					echo 'Актуальная версия RotorCMS: <b>'.$data['version'].'</b><br />';
					echo 'Версия сайта: <b>'.$config['rotorversion'].'</b><br /><br />';

					$total = count($data['changes']);

					if ($total > 0) {
						if ($start < 0 || $start >= $total) {
							$start = 0;
						}
						if ($total < $start + $config['postchanges']) {
							$end = $total;
						} else {
							$end = $start + $config['postchanges'];
						}
						for ($i = $start; $i < $end; $i++) {
							echo '<div class="b">';
							echo '<img src="/images/img/edit.gif" alt="image" /> ';
							echo '<b>'.$data['changes'][$i]['changes_title'].'</b><small> (ver. '.$data['changes'][$i]['changes_ver'].')</small></div>';
							echo '<div>'.bb_code($data['changes'][$i]['changes_text']).'<br />';
							echo 'Обновлено: '.date_fixed($data['changes'][$i]['changes_time']).'</div>';
						}

						page_strnavigation('changes.php?', $config['postchanges'], $start, $total);
					} else {
						show_error('Новостей еще нет!');
					}
				} else {
					show_error('Ошибка! Не удалось загрузить новости!');
				}
			} else {
				show_error('Ошибка! Не удалось загрузить новости!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="changes.php?act=reload">Обновить</a><br />';
		break;

		############################################################################################
		##                                    Проверка лицензии                                   ##
		############################################################################################
		case 'verifi':

			$servername = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			license_verification();

			echo 'Проверить наличие лицензии на сайте <b><a href="http://visavi.net/rotorcms/index.php?act=check&amp;site='.$servername.'">VISAVI.NET</a></b><br /><br />';
			break;

		############################################################################################
		##                                  Обновление новостей                                   ##
		############################################################################################
		case 'reload':

			if (@copy("http://visavi.net/rotorcms/rotor.txt", DATADIR."/temp/changes.dat")) {
			} else {
				$data = curl_connect("http://visavi.net/rotorcms/rotor.txt", 'Mozilla/5.0', $config['proxy']);
				file_put_contents(DATADIR."/temp/changes.dat", $data);
			}

			$_SESSION['note'] = 'Новости RotorCMS успешно обновлены!';
			redirect("changes.php");

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="changes.php">Вернуться</a><br />';
		break;

	default:
		redirect("changes.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
