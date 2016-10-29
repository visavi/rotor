<?php

$days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);
$hours = floor((gmmktime(date("H"), 0, 0, date("m"), date("d"), date("Y")) - gmmktime((date("Z") / 3600), 0, 0, 1, 1, 1970)) / 3600);

DB::run() -> query("DELETE FROM `online` WHERE `online_time`<?;", array(SITETIME-$config['timeonline']));

$online = stats_online();
if ($online[1] < 150 || is_user()) {
	$newhost = 0;

	if (is_user()) {
		$queryonline = DB::run() -> querySingle("SELECT `online_id` FROM `online` WHERE `online_ip`=? OR `online_user`=? LIMIT 1;", array(App::getClientIp(), App::getUsername()));
		if (empty($queryonline)) {
			DB::run() -> query("INSERT INTO `online` (`online_ip`, `online_brow`, `online_time`, `online_user`) VALUES (?, ?, ?, ?);", array(App::getClientIp(), App::getUserAgent(), SITETIME, App::getUsername()));
			$newhost = 1;
		} else {
			DB::run() -> query("UPDATE `online` SET `online_ip`=?, `online_brow`=?, `online_time`=?, `online_user`=? WHERE `online_id`=? LIMIT 1;", array(App::getClientIp(), App::getUserAgent(), SITETIME, App::getUsername(), $queryonline));
		}
	} else {
		$queryonline = DB::run() -> querySingle("SELECT `online_id` FROM `online` WHERE `online_ip`=? LIMIT 1;", array(App::getClientIp()));
		if (empty($queryonline)) {
			DB::run() -> query("INSERT INTO `online` (`online_ip`, `online_brow`, `online_time`) VALUES (?, ?, ?);", array(App::getClientIp(), App::getUserAgent(), SITETIME));
			$newhost = 1;
		} else {
			DB::run() -> query("UPDATE `online` SET `online_brow`=?, `online_time`=?, `online_user`=? WHERE `online_id`=? LIMIT 1;", array(App::getUserAgent(), SITETIME, '', $queryonline));
		}
	}
	// -----------------------------------------------------------//
	$counts = DB::run() -> queryFetch("SELECT * FROM `counter`;");

	if ($counts['count_hours'] != $hours) {
		DB::run() -> query("INSERT IGNORE INTO `counter24` (`count_hour`, `count_hosts`, `count_hits`) VALUES (?, ?, ?);", array($hours, $counts['count_hosts24'], $counts['count_hits24']));
		DB::run() -> query("UPDATE `counter` SET `count_hours`=?, `count_hosts24`=?, `count_hits24`=?;", array($hours, 0, 0));
		DB::run() -> query("DELETE FROM `counter24` WHERE `count_hour` < (SELECT MIN(`count_hour`) FROM (SELECT `count_hour` FROM `counter24` ORDER BY `count_hour` DESC LIMIT 24) AS del);");
	}

	if ($counts['count_days'] != $days) {
		DB::run() -> query("INSERT IGNORE INTO `counter31` (`count_days`, `count_hosts`, `count_hits`) VALUES (?, ?, ?);", array($days, $counts['count_dayhosts'], $counts['count_dayhits']));
		DB::run() -> query("UPDATE `counter` SET `count_days`=?, `count_dayhosts`=?, `count_dayhits`=?;", array($days, 0, 0));
		DB::run() -> query("DELETE FROM `counter31` WHERE `count_days` < (SELECT MIN(`count_days`) FROM (SELECT `count_days` FROM `counter31` ORDER BY `count_days` DESC LIMIT 31) AS del);");
		// ---------------------------------------------------//
		$querycount = DB::run() -> query("SELECT `count_days`, `count_hosts` FROM `counter31` ORDER BY `count_days` DESC LIMIT 6;");
		$counts = $querycount -> fetchAssoc();

		$host_data = array();
		for ($i = 0, $tekdays = $days; $i < 6; $tekdays--, $i++) {
			array_unshift($host_data, (isset($counts[$tekdays])) ? $counts[$tekdays] : 0);
		}

		file_put_contents(DATADIR.'/temp/counter7.dat', serialize($host_data), LOCK_EX);
	}
	// -----------------------------------------------------------//
	if (!empty($newhost)) {
		DB::run() -> query("UPDATE `counter` SET `count_allhosts`=`count_allhosts`+1, `count_allhits`=`count_allhits`+1, `count_dayhosts`=`count_dayhosts`+1, `count_dayhits`=`count_dayhits`+1, `count_hosts24`=`count_hosts24`+1, `count_hits24`=`count_hits24`+1;");
	} else {
		DB::run() -> query("UPDATE `counter` SET `count_allhits`=`count_allhits`+1, `count_dayhits`=`count_dayhits`+1, `count_hits24`=`count_hits24`+1;");
	}
}
