<?php

$imagecache = '/upload/counters/counter7.gif';
if (!file_exists($imagecache) || date_fixed(@filemtime($imagecache), "dmY") != date_fixed(SITETIME, "dmY")){

	$week_day = date("w") - 1;
	$arr_week = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
	$days = floor((gmmktime(0, 0, 0, date("m"), date("d"), date("Y")) - gmmktime(0, 0, 0, 1, 1, 1970)) / 86400);

	$querycount = DB::run() -> query("SELECT `days`, `hosts` FROM `counter31` ORDER BY `days` DESC LIMIT 7;");
	$counts = $querycount -> fetchAssoc();

	$host_data = [];
	for ($i = 0, $tekdays = $days; $i < 7; $tekdays--, $i++) {
		$host_data[] = (isset($counts[$tekdays])) ? $counts[$tekdays] : 0;
	}

	$host_data = array_reverse($host_data);
	// ---------------------------------------------//
	$max = max($host_data);
	if ($max == 0) $max = 1;

	$per_host = [];
	foreach ($host_data as $value) {
		$per_host[] = $value * 0.90 / $max;
	}

	$img = imagecreatefromgif(HOME.'/assets/img/images/counter7.gif');

	$imageW = 47;
	$collW = 14;

	$x1 = 12;
	$y2 = 59;
	$x2 = $x1 + $collW;
	$colorBlack = imagecolorallocate($img, 0, 0, 120);

	foreach ($per_host as $index => $percent) {
		$week_day++;
		if ($week_day > 6) {
			$week_day -= 7;
		}

		if ($index / 2 == (int)($index / 2)) {
			$color = imagecolorallocate($img, 249, 243, 70);
			$color2 = imagecolorallocate($img, 242, 226, 42);
			$color3 = imagecolorallocate($img, 226, 210, 34);
		} else {
			$color = imagecolorallocate($img, 11, 215, 252);
			$color2 = imagecolorallocate($img, 7, 203, 239);
			$color3 = imagecolorallocate($img, 7, 187, 219);
		}

		$y1 = round($imageW - $imageW * $percent + 12);
		imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);

		$points = [0 => $x1, // x1
			1 => $y1, // y1
			2 => $x1 + 3, // x2
			3 => $y1-5, // y2
			4 => $x1 + $collW + 3, // x3
			5 => $y1-5, // y3
			6 => $x2, // x4
			7 => $y1, // y4
        ];

		imagefilledpolygon($img, $points, 4, $color2);

		$points = [0 => $x2, // x1
			1 => $y1, // y1
			2 => $x1 + $collW + 3, // x2
			3 => $y1-5, // y2
			4 => $x1 + $collW + 3, // x3
			5 => $y2-5, // y3
			6 => $x2, // x4
			7 => $y2, // y4
        ];

		imagefilledpolygon($img, $points, 4, $color3);
		imagettftext($img, 6, 90, $x1+10, 50, $colorBlack, HOME.'/assets/fonts/font.ttf', $host_data[$index]);
		//imagestringup($img, 1, $x1 + 3, 52, $host_data[$index], $colorBlack);
		imagettftext($img, 6, 0, $x1 + 3, 66, $colorBlack, HOME.'/assets/fonts/font.ttf', $arr_week[$week_day]);

		$x1 += $collW;
		$x2 += $collW;
	}
	//Header("Content-type: image/gif");
	imagegif($img, HOME.$imagecache);
	imagedestroy($img);
}

echo '<img src="'.$imagecache.'?'.date_fixed(SITETIME, "dmY").'" alt="Неделя" /><br /><br />';
