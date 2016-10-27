<?php

$imagecache = '/upload/counters/counter31.gif';
if (!file_exists($imagecache) || date_fixed(@filemtime($imagecache), "dmY") != date_fixed(SITETIME, "dmY")){

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
			$hits_data[] = $arrhits[$tekdays];
		} else {
			$hits_data[] = 0;
		}

		if (isset($arrhosts[$tekdays])) {
			$host_data[] = $arrhosts[$tekdays];
		} else {
			$host_data[] = 0;
		}
	}
	// --------------------------------------------------//
	$maxhit = 0;
	$max_index = 0;
	foreach ($hits_data as $index => $value) {
		if ($value > $maxhit) {
			$maxhit = $value;
			$max_index = $index;
		}
	}

	$maxhost = max($host_data);
	if ($maxhit == 0) {
		$maxhit = 1;
	}
	// процентное соотношение хитов
	$per_hit = array();
	foreach ($hits_data as $value) {
		$per_hit[] = $value * 0.90 / $maxhit;
	}
	// процентное соотношение хостов
	$per_host = array();
	foreach ($host_data as $value) {
		$per_host[] = $value * 2.90 / $maxhit;
	}
	$img = imageCreateFromGIF(BASEDIR.'/images/img/counter31.gif');
	// линейный
	$color1 = imageColorAllocate($img, 44, 191, 228);
	$color2 = imageColorAllocate($img, 0, 0, 120);
	$color_red = imageColorAllocate($img, 200, 0, 0);

	$image = 47;
	$coll = 3;
	$x1 = 154;
	$x2 = $x1 - 3;
	$y1_hits = (int)($image - $image * $per_hit[0] + 7);
	$y1_host = (int)($image - $image * $per_host[0] + 7);

	$counth = count($hits_data);
	if ($counth > 31) {
		$counth = 31;
	}

	imageTTFtext($img, 6, 0, 50, 7, $color_red, BASEDIR.'/assets/fonts/font.ttf', 'max. '.$maxhost.' / '.$maxhit);

	for($i = 1;$i < $counth;$i++) {
		// хиты
		$y2_hits = (int)($image - $image * $per_hit[$i] + 7);
		imageLine($img, $x1 + 1, $y1_hits, $x2, $y2_hits, $color1);
		// хосты
		$y2_host = (int)($image - $image * $per_host[$i] + 7);
		imageLine($img, $x1 + 1, $y1_host, $x2, $y2_host, $color2);

		if ($hits_data[$i] != 0 && $i == $max_index) {
			imageLine($img, $x2-1, $y2_hits-2, $x2-1, $y2_hits + 42, $color_red);
		}
		$y1_hits = $y2_hits;
		$y1_host = $y2_host;
		$x1 -= $coll + 2;
		$x2 -= $coll + 2;
	}
	//Header("Content-type: image/gif");
	ImageGIF($img, BASEDIR.$imagecache);
	ImageDestroy($img);
}

echo '<img src="'.$imagecache.'?'.date_fixed(SITETIME, "dmY").'" alt="Месяц" /><br /><br />';
