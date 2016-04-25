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

$config['chatpost'] = 10;
$config['shutnik'] = 1; // Шутник включен
$config['magnik'] = 1; // Умник включен
$config['botnik'] = 1; // Бот включен

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';
$start = (isset($_GET['start'])) ? abs(intval($_GET['start'])) : 0;
$name = (isset($_GET['name'])) ? '[b]' . check($_GET['name']) . '[/b], ' : '';

show_title('Мини-чат');

if ($act == 'index') {

	echo '<a href="#down"><img src="/images/img/downs.gif" alt="image" /></a> ';
	echo '<a href="#form">Написать</a> / ';
	echo '<a href="index.php?rand=' . mt_rand(100, 999) . '">Обновить</a>';
	if (is_admin()) {
		echo ' / <a href="/admin/minichat.php?start=' . $start . '">Управление</a>';
	}
	echo '<hr />';

	// ---------------------------------------------------------------//
	if (! file_exists(DATADIR . "/temp/chat.dat")){
		touch(DATADIR . "/temp/chat.dat");
	}

	$file = file(DATADIR . "/temp/chat.dat");
	$file = array_reverse($file);
	$total = count($file);

	if ($total > 0) {

		if (is_user()) {
			// --------------------------генерация анекдота------------------------------------------------//
			if ($config['shutnik'] == 1) {
				$anfi = file("bots/chat_shut.php");
				$an_rand = array_rand($anfi);
				$anshow = trim($anfi[$an_rand]);

				$tifi = file(DATADIR . "/temp/chat.dat");
				$tidw = explode("|", end($tifi));

				if (SITETIME > ($tidw[3] + 180) && empty($tidw[6])) {
					$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
					$antext = no_br($anshow . '|Весельчак||' . SITETIME . '|Opera|127.0.0.2|1|' . $tidw[7] . '|' . $tidw[8] . '|' . $unifile . '|');

					write_files(DATADIR . "/temp/chat.dat", "$antext\r\n");
				}
			}
			// ------------------------------- Ответ на вопрос ----------------------------------//
			if ($config['magnik'] == 1) {
			$mmagfi = file(DATADIR . "/temp/chat.dat");
			$mmagshow = explode("|", end($mmagfi));

			if ($mmagshow[8] != "" && SITETIME > $mmagshow[7]) {
				$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
				$magtext = no_br('На вопрос никто не ответил, правильный ответ был: [b]' . $mmagshow[8] . '[/b]! Следующий вопрос через 1 минуту|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 60) . '||' . $unifile . '|');

				write_files(DATADIR . "/temp/chat.dat", "$magtext\r\n");
			}
			// ------------------------------  Новый вопрос  -------------------------------//
			$magfi = file("bots/chat_mag.php");
			$mag_rand = array_rand($magfi);
			$magshow = $magfi[$mag_rand];
			$magstr = explode("|", $magshow);

			if (empty($mmagshow[8]) && SITETIME > $mmagshow[7] && $magstr[0] != "") {
				$strlent = utf_strlen($magstr[1]);

				if ($strlent > 1 && $strlent < 5) {
				$podskazka = "$strlent буквы";
				} else {
				$podskazka = "$strlent букв";
				}

				$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
				$magtext = no_br('Вопрос всем: ' . $magstr[0] . ' - (' . $podskazka . ')|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 600) . '|' . $magstr[1] . '|' . $unifile . '|');

				write_files(DATADIR . "/temp/chat.dat", "$magtext\r\n");
			}
			}
			// ----------------------------  Подключение бота  -----------------------------------------//
			if ($config['botnik'] == 1) {
			if (empty($_SESSION['botochat'])) {
				$hellobots = array('Приветик', 'Здравствуй', 'Хай', 'Добро пожаловать', 'Салют', 'Hello', 'Здарова');
				$hellobots_rand = array_rand($hellobots);
				$hellobots_well = $hellobots[$hellobots_rand];

				$mmagfi = file(DATADIR . "/temp/chat.dat");
				$mmagshow = explode("|", end($mmagfi));

				$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
				$weltext = no_br($hellobots_well . ', ' . nickname($log) . '!|Настюха||' . SITETIME . '|SIE-S65|127.0.0.2|0|' . $mmagshow[7] . '|' . $mmagshow[8] . '|' . $unifile . '|');

				write_files(DATADIR . "/temp/chat.dat", "$weltext\r\n");

				$_SESSION['botochat'] = 1;
			}
			}

			$countstr = counter_string(DATADIR . "/temp/chat.dat");
			if ($countstr >= $config['maxpostchat']) {
			delete_lines(DATADIR . "/temp/chat.dat", array(0, 1, 2, 3, 4));
			}
		}

		if ($start < 0 || $start >= $total) {
		$start = 0;
		}
		if ($total < $start + $config['chatpost']) {
		$end = $total;
		} else {
		$end = $start + $config['chatpost'];
		}
		for ($i = $start; $i < $end; $i++) {
		$data = explode("|", $file[$i]);

		$useronline = user_online($data[1]);
		$useravatars = user_avatars($data[1]);

		if ($data[1] == 'Вундер-киндер') {
			$useravatars = '<img src="img/mag.gif" alt="image" /> ';
			$useronline = '<img src="/images/img/on.gif" alt="image">';
		}
		if ($data[1] == 'Настюха') {
			$useravatars = '<img src="img/bot.gif" alt="image" /> ';
			$useronline = '<img src="/images/img/on.gif" alt="image">';
		}
		if ($data[1] == 'Весельчак') {
			$useravatars = '<img src="img/shut.gif" alt="image" /> ';
			$useronline = '<img src="/images/img/on.gif" alt="image">';
		}

		echo '<div class="b">';
		echo '<div class="img">' . $useravatars . '</div>';

		echo '<b><a href="index.php?name=' . nickname($data[1]) . '#form">' . nickname($data[1]) . '</a></b>  <small>(' . date_fixed($data[3]) . ')</small><br />';
		echo user_title($data[1]) . ' ' . $useronline . '</div>';
		echo '<div>' . bb_code($data[0]) . '<br />';
		echo '<span class="data">(' . $data[4] . ', ' . $data[5] . ')</span></div>';
		}

		page_strnavigation('index.php?', $config['chatpost'], $start, $total);

	} else {
		show_error('Сообщений нет, будь первым!');
	}

	if (is_user()) {
		echo '<div class="form" id="form">';
		echo '<form action="index.php?act=add" method="post">';
		echo '<b>Сообщение:</b><br />';
		echo '<textarea id="markItUp" cols="25" rows="5" name="msg">' . $name . '</textarea><br />';
		echo '<input type="submit" value="Добавить" /></form></div>';
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}
}

############################################################################################
##                                  Добавление сообщения                                  ##
############################################################################################
if ($act == 'add') {

	$msg = check($_POST['msg']);

	$config['header'] = 'Добавление сообщения';
	$config['newtitle'] = 'Мини-чат - Добавление сообщения';

	if (is_user()) {
		if (utf_strlen($msg) > 3 && utf_strlen($msg) < 1000) {
			if (is_quarantine($log)) {
				if (is_flood($log)) {

					$msg = antimat($msg);

					$file = file(DATADIR . "/temp/chat.dat");
					$data = explode("|", end($file));

					$unifile = unifile(DATADIR . "/temp/chat.dat", 9);

					if (!isset($data[7])) $data[7] = '';
					if (!isset($data[8])) $data[8] = '';

					$text = no_br($msg . '|' . $log . '||' . SITETIME . '|' . $brow . '|' . $ip . '|0|' . $data[7] . '|' . $data[8] . '|' . $unifile . '|');

					write_files(DATADIR . "/temp/chat.dat", "$text\r\n");

					$countstr = counter_string(DATADIR . "/temp/chat.dat");
					if ($countstr >= $config['maxpostchat']) {
						delete_lines(DATADIR . "/temp/chat.dat", array(0, 1, 2, 3, 4));
					}

					DB::run() -> query("UPDATE `users` SET `users_point`=`users_point`+1, `users_money`=`users_money`+5 WHERE `users_login`=?", array($log));

					// --------------------------------------------------------------------------//
					if ($config['botnik'] == 1) {
						include_once "bots/chat_bot.php";

						if ($mssg != "") {
							$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
							$text = no_br($mssg . '|' . $namebots . '||' . SITETIME . '|MOT-V3|L-O-V-E|0|' . $data[7] . '|' . $data[8] . '|' . $unifile . '|');

							write_files(DATADIR . "/temp/chat.dat", "$text\r\n");
						}
					}
					// --------------------------------------------------------------------------//
					if ($config['magnik'] == 1) {
						if (!empty($data[8]) && stristr($msg, $data[8])) {
							$unifile = unifile(DATADIR . "/temp/chat.dat", 9);
							$text = no_br('Молодец ' . nickname($log) . '! Правильный ответ [b]' . $data[8] . '[/b]! Следующий вопрос через 1 минуту|Вундер-киндер||' . SITETIME . '|Opera|127.0.0.3|0|' . (SITETIME + 60) . '||' . $unifile . '|');

							write_files(DATADIR . "/temp/chat.dat", "$text\r\n");
						}
					}

					notice('Сообщение успешно добавлено!');
					redirect("index.php");

				} else {
					show_error('Антифлуд! Разрешается отправлять сообщения раз в ' . flood_period() . ' секунд!');
				}
			} else {
			show_error('Карантин! Вы не можете писать в течении ' . round($config['karantin'] / 3600) . ' часов!');
			}
		} else {
			show_error('Ошибка, слишком длинное или короткое сообщение!');
		}
	} else {
		show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
	}

echo '<img src="/images/img/back.gif" alt="image" /> <a href="index.php">Вернуться</a><br /><br />';

}

echo '<a href="#up"><img src="/images/img/ups.gif" alt="image" /></a> ';
echo '<a href="/pages/rules.php">Правила</a> / ';
echo '<a href="/pages/smiles.php">Смайлы</a> / ';
echo '<a href="/pages/tags.php">Теги</a><br /><br />';

echo '<img src="/images/img/homepage.gif" alt="image" /> <a href="/index.php">На главную</a>';

include_once ("../themes/footer.php");
