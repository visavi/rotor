<?php

$imagecache = '/upload/counters/counter7.gif';
if (!file_exists($imagecache) || date_fixed(@filemtime($imagecache), "dmY") != date_fixed(SITETIME, "dmY")){

	$week_day = date("w") - 1;
	$arr_week = array('вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб');
	$days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

	$querycount = DB::run() -> query("SELECT `count_days`, `count_hosts` FROM `counter31` ORDER BY `count_days` DESC LIMIT 7;");
	$counts = $querycount -> fetchAssoc();

	$host_data = array();
	for ($i = 0, $tekdays = $days; $i < 7; $tekdays--, $i++) {
		$host_data[] = (isset($counts[$tekdays])) ? $counts[$tekdays] : 0;
	}

	$host_data = array_reverse($host_data);
	// ---------------------------------------------//
	$max = max($host_data);
	if ($max == 0) $max = 1;

	$per_host = array();
	foreach ($host_data as $value) {
		$per_host[] = $value * 0.90 / $max;
	}

	$img = imageCreateFromGIF(HOME.'/assets/img/images/counter7.gif');

	$imageW = 47;
	$collW = 14;

	$x1 = 12;
	$y2 = 59;
	$x2 = $x1 + $collW;
	$colorBlack = imageColorAllocate($img, 0, 0, 120);

	foreach ($per_host as $index => $percent) {
		$week_day++;
		if ($week_day > 6) {
			$week_day -= 7;
		}

		if ($index / 2 == (int)($index / 2)) {
			$color = imageColorAllocate($img, 249, 243, 70);
			$color2 = imageColorAllocate($img, 242, 226, 42);
			$color3 = imageColorAllocate($img, 226, 210, 34);
		} else {
			$color = imageColorAllocate($img, 11, 215, 252);
			$color2 = imageColorAllocate($img, 7, 203, 239);
			$color3 = imageColorAllocate($img, 7, 187, 219);
		}

		$y1 = round($imageW - $imageW * $percent + 12);
		imageFilledRectangle($img, $x1, $y1, $x2, $y2, $color);

		$points = array(0 => $x1, // x1
			1 => $y1, // y1
			2 => $x1 + 3, // x2
			3 => $y1-5, // y2
			4 => $x1 + $collW + 3, // x3
			5 => $y1-5, // y3
			6 => $x2, // x4
			7 => $y1, // y4
			);

		imageFilledPolygon($img, $points, 4, $color2);

		$points = array(0 => $x2, // x1
			1 => $y1, // y1
			2 => $x1 + $collW + 3, // x2
			3 => $y1-5, // y2
			4 => $x1 + $collW + 3, // x3
			5 => $y2-5, // y3
			6 => $x2, // x4
			7 => $y2, // y4
			);

		imageFilledPolygon($img, $points, 4, $color3);
		// imageTTFtext($img, 7, 90, $x1+8, 50, $colorBlack, HOME.'/assets/fonts/font.ttf', $host_data[$index]);
		imagestringup($img, 1, $x1 + 3, 52, $host_data[$index], $colorBlack);
		imageTTFtext($img, 6, 0, $x1 + 3, 66, $colorBlack, HOME.'/assets/fonts/font.ttf', $arr_week[$week_day]);

		$x1 += $collW;
		$x2 += $collW;
	}
	//Header("Content-type: image/gif");
	ImageGIF($img, BASEDIR.$imagecache);
	ImageDestroy($img);
}

echo '<img src="'.$imagecache.'?'.date_fixed(SITETIME, "dmY").'" alt="Неделя" /><br /><br />';
