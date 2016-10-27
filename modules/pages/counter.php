<?php
App::view($config['themes'].'/index');

$currhour = date("G", SITETIME);
$currday = date("j", SITETIME);

switch ($act):
############################################################################################
##                                   Вывод статистики                                     ##
############################################################################################
	case 'index':

		echo '<h1>Количество посещений</h1>';

		$online = stats_online();
		$count = stats_counter();

		echo 'Всего посетителей на сайте: <b>'.$online[1].'</b><br />';
		echo 'Всего авторизованных: <b>'.$online[0].'</b><br />';
		echo 'Всего гостей: <b>'.($online[1] - $online[0]).'</b><br /><br />';

		echo 'Хостов сегодня: <b>'.$count['count_dayhosts'].'</b><br />';
		echo 'Хитов сегодня: <b>'.$count['count_dayhits'].'</b><br />';
		echo 'Всего хостов: <b>'.$count['count_allhosts'].'</b><br />';
		echo 'Всего хитов: <b>'.$count['count_allhits'].'</b><br /><br />';

		echo 'Хостов за текущий час: <b>'.$count['count_hosts24'].'</b><br />';
		echo 'Хитов за текущий час: <b>'.$count['count_hits24'].'</b><br /><br />';

		$counts24 = DB::run() -> queryFetch("SELECT SUM(`count_hosts`) AS `hosts`, SUM(`count_hits`) AS `hits` FROM `counter24`;");

		echo 'Хостов за 24 часа: <b>'.($counts24['hosts'] + $count['count_hosts24']).'</b><br />';
		echo 'Хитов за 24 часа: <b>'.($counts24['hits'] + $count['count_hits24']).'</b><br /><br />';

		$counts31 = DB::run() -> queryFetch("SELECT SUM(`count_hosts`) AS `hosts`, SUM(`count_hits`) AS `hits` FROM `counter31`;");

		echo 'Хостов за месяц: <b>'.($counts31['hosts'] + $count['count_dayhosts']).'</b><br />';
		echo 'Хитов за месяц: <b>'.($counts31['hits'] + $count['count_dayhits']).'</b><br /><br />';

		echo 'Динамика за неделю<br />';
		include_once(BASEDIR.'/includes/counter7.php');

		echo 'Динамика за сутки<br />';
		include_once(BASEDIR.'/includes/counter24.php');

		echo 'Динамика за месяц<br />';
		include_once(BASEDIR.'/includes/counter31.php');

		echo '<a href="/counter/24">Статистика по часам</a><br />';
		echo '<a href="/counter/31">Статистика по дням </a><br /><br />';
	break;

	############################################################################################
	##                                Статистика за 24 часа                                   ##
	############################################################################################
	case '24':

		echo '<h1>Статистика по часам</h1>';

		echo 'Динамика за сутки<br />';
		include_once(BASEDIR.'/includes/counter24.php');

		if ($currhour > 0) {
			$hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

			$querycount = DB::run() -> query("SELECT * FROM `counter24` ORDER BY `count_hour` DESC;");
			$counts = $querycount -> fetchAll();

			$arrhits = array();
			$arrhosts = array();
			$hits_data = array();
			$host_data = array();

			foreach ($counts as $val) {
				$arrhits[$val['count_hour']] = $val['count_hits'];
				$arrhosts[$val['count_hour']] = $val['count_hosts'];
			}

			for ($i = 0, $tekhours = $hours; $i < 24; $tekhours -= 1, $i++) {
				if (isset($arrhits[$tekhours])) {
					$hits_data[$tekhours] = $arrhits[$tekhours];
				} else {
					$hits_data[$tekhours] = 0;
				}

				if (isset($arrhosts[$tekhours])) {
					$host_data[$tekhours] = $arrhosts[$tekhours];
				} else {
					$host_data[$tekhours] = 0;
				}
			}

			$hits_data = array_reverse($hits_data, true);
			$host_data = array_reverse($host_data, true);

			echo '<b>Время — Хосты / Хиты</b><br />';
			for ($i = 0, $tekhours = $hours; $i < $currhour; $tekhours -= 1, $i++) {
				echo date_fixed(floor(($tekhours-1) * 3600), 'H:i').' - '.date_fixed(floor($tekhours * 3600), 'H:i').' — <b>'.$host_data[$tekhours].'</b> / <b>'.$hits_data[$tekhours].'</b><br />';
			}

			echo '<br />';
		} else {
			show_error('Статистика за текущие сутки еще не обновилась!');
		}

		echo '<i class="fa fa-arrow-circle-left"></i> <a href="/counter">Вернуться</a><br />';
	break;

	############################################################################################
	##                                  Статистика за месяц                                   ##
	############################################################################################
	case '31':

		echo '<h1>Статистика по дням</h1>';

		echo 'Динамика за месяц<br />';
		include_once(BASEDIR.'/includes/counter31.php');

		if ($currday > 1) {
			$days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

			$querycount = DB::run() -> query("SELECT * FROM `counter31` ORDER BY `count_days` DESC;");
			$counts = $querycount -> fetchAll();

			$arrhits = array();
			$arrhosts = array();
			$hits_data = array();
			$host_data = array();

			foreach ($counts as $val) {
				$arrhits[$val['count_days']] = $val['count_hits'];
				$arrhosts[$val['count_days']] = $val['count_hosts'];
			}

			for ($i = 0, $tekdays = $days; $i < 31; $tekdays -= 1, $i++) {
				if (isset($arrhits[$tekdays])) {
					$hits_data[$tekdays] = $arrhits[$tekdays];
				} else {
					$hits_data[$tekdays] = 0;
				}

				if (isset($arrhosts[$tekdays])) {
					$host_data[$tekdays] = $arrhosts[$tekdays];
				} else {
					$host_data[$tekdays] = 0;
				}
			}

			$hits_data = array_reverse($hits_data, true);
			$host_data = array_reverse($host_data, true);

			echo '<b>Дата — Хосты / Хиты</b><br />';
			for ($i = 1, $tekdays = $days; $i < $currday; $tekdays -= 1, $i++) {
				echo date_fixed(floor(($tekdays-1) * 86400), 'd.m').' - '.date_fixed(floor($tekdays * 86400), 'd.m').' — <b>'.$host_data[$tekdays].'</b> / <b>'.$hits_data[$tekdays].'</b><br />';
			}

			echo '<br />';
		} else {
			show_error('Статистика за текущий месяц еще не обновилась!');
		}

		echo '<i class="fa fa-arrow-circle-left"></i> <a href="/counter">Вернуться</a><br />';
	break;

endswitch;

App::view($config['themes'].'/foot');
