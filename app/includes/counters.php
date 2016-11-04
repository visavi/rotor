<?php

$days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);
$hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

DB::run() -> query("DELETE FROM `online` WHERE `time`<?;", array(SITETIME-$config['timeonline']));

$online = stats_online();
if ($online[1] < 150 || is_user()) {
	$newhost = 0;

	if (is_user()) {
		$queryonline = DB::run() -> querySingle("SELECT `id` FROM `online` WHERE `ip`=? OR `user`=? LIMIT 1;", array(App::getClientIp(), App::getUsername()));
		if (empty($queryonline)) {
			DB::run() -> query("INSERT INTO `online` (`ip`, `brow`, `time`, `user`) VALUES (?, ?, ?, ?);", array(App::getClientIp(), App::getUserAgent(), SITETIME, App::getUsername()));
			$newhost = 1;
		} else {
			DB::run() -> query("UPDATE `online` SET `ip`=?, `brow`=?, `time`=?, `user`=? WHERE `id`=? LIMIT 1;", array(App::getClientIp(), App::getUserAgent(), SITETIME, App::getUsername(), $queryonline));
		}
	} else {
		$queryonline = DB::run() -> querySingle("SELECT `id` FROM `online` WHERE `ip`=? LIMIT 1;", array(App::getClientIp()));
		if (empty($queryonline)) {
			DB::run() -> query("INSERT INTO `online` (`ip`, `brow`, `time`) VALUES (?, ?, ?);", array(App::getClientIp(), App::getUserAgent(), SITETIME));
			$newhost = 1;
		} else {
			DB::run() -> query("UPDATE `online` SET `brow`=?, `time`=?, `user`=? WHERE `id`=? LIMIT 1;", array(App::getUserAgent(), SITETIME, '', $queryonline));
		}
	}
	// -----------------------------------------------------------//
	$counts = DB::run() -> queryFetch("SELECT * FROM `counter`;");

	if ($counts['hours'] != $hours) {
		DB::run() -> query("INSERT IGNORE INTO `counter24` (`hour`, `hosts`, `hits`) VALUES (?, ?, ?);", array($hours, $counts['hosts24'], $counts['hits24']));
		DB::run() -> query("UPDATE `counter` SET `hours`=?, `hosts24`=?, `hits24`=?;", array($hours, 0, 0));
		DB::run() -> query("DELETE FROM `counter24` WHERE `hour` < (SELECT MIN(`hour`) FROM (SELECT `hour` FROM `counter24` ORDER BY `hour` DESC LIMIT 24) AS del);");
	}

	if ($counts['days'] != $days) {
		DB::run() -> query("INSERT IGNORE INTO `counter31` (`days`, `hosts`, `hits`) VALUES (?, ?, ?);", array($days, $counts['dayhosts'], $counts['dayhits']));
		DB::run() -> query("UPDATE `counter` SET `days`=?, `dayhosts`=?, `dayhits`=?;", array($days, 0, 0));
		DB::run() -> query("DELETE FROM `counter31` WHERE `days` < (SELECT MIN(`days`) FROM (SELECT `days` FROM `counter31` ORDER BY `days` DESC LIMIT 31) AS del);");
		// ---------------------------------------------------//
		$querycount = DB::run() -> query("SELECT `days`, `hosts` FROM `counter31` ORDER BY `days` DESC LIMIT 6;");
		$counts = $querycount -> fetchAssoc();

		$host_data = array();
		for ($i = 0, $tekdays = $days; $i < 6; $tekdays--, $i++) {
			array_unshift($host_data, (isset($counts[$tekdays])) ? $counts[$tekdays] : 0);
		}

		file_put_contents(STORAGE.'/temp/counter7.dat', serialize($host_data), LOCK_EX);
	}
	// -----------------------------------------------------------//
	if (!empty($newhost)) {
		DB::run() -> query("UPDATE `counter` SET `allhosts`=`allhosts`+1, `allhits`=`allhits`+1, `dayhosts`=`dayhosts`+1, `dayhits`=`dayhits`+1, `hosts24`=`hosts24`+1, `hits24`=`hits24`+1;");
	} else {
		DB::run() -> query("UPDATE `counter` SET `allhits`=`allhits`+1, `dayhits`=`dayhits`+1, `hits24`=`hits24`+1;");
	}
}
