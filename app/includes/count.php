<?php

// -------------------- Вывод статистики ------------------------------//
$week_day = date('w');
$arr_week = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];

$count = stats_counter();
$online = stats_online();

if (file_exists(STORAGE.'/temp/counter7.dat')) {
	$host_data = unserialize(file_get_contents(STORAGE.'/temp/counter7.dat'));
} else {
	$host_data = array_fill(0, 6, 0);
}

array_push($host_data, $count['dayhosts']);
// ----------------------------------------------------------------------//
$img = imagecreatefrompng(HOME.'/assets/img/images/counter.png');
$color = imagecolorallocate($img, 0, 0, 0);
$color2 = imagecolorallocate($img, 102, 102, 102);

if ($online[1] >= 0 && $online[1] < 10) $pos = 65;
if ($online[1] >= 10 && $online[1] < 100) $pos = 52;
if ($online[1] >= 100 && $online[1] < 200) $pos = 44;
if ($online[1] >= 200 && $online[1] < 1000) $pos = 40;

$colors = [[191, 109, 232], [87, 164, 246], [0, 203, 189], [0, 199, 68], [149, 217, 0], [255, 255, 0], [255, 181, 0]];

$max = max($host_data);
if ($max == 0) $max = 1;

$per_host = [];
foreach ($host_data as $value) {
	$per_host[] = $value * 0.90 / $max;
}

$coll = 11;
$im = 14;
$x1 = 2;
$y2 = 15;
$x2 = $x1 + $coll;

foreach ($per_host as $key => $percent) {
	$week_day++;

	if ($week_day > 6) {
		$week_day -= 7;
	}

	$y1 = round($im - $im * $percent + 1);

	if (!empty($percent)) {
		$col = imagecolorallocate($img, $colors[$key][0], $colors[$key][1], $colors[$key][2]);
		imagefilledrectangle($img, $x1-1, $y1, $x2-2, $y2, $col);
	}

	imagettftext($img, 6, 0, $x1, 15, $color, HOME.'/assets/fonts/font.ttf', $arr_week[$week_day]);

	$x1 += $coll;
	$x2 += $coll;
}

imagettftext($img, 6, 0, 13, 23, $color2, HOME.'/assets/fonts/font4.ttf', $count['dayhosts']);
imagettftext($img, 6, 0, 13, 29, $color2, HOME.'/assets/fonts/font4.ttf', $count['dayhits']);
imagettftext($img, 12, 0, $pos, 29, $color2, HOME.'/assets/fonts/font7.ttf', $online[1]);

imagepng($img, HOME.'/uploads/counters/counter_new.png');
imagedestroy($img);

rename(HOME.'/uploads/counters/counter_new.png', HOME.'/uploads/counters/counter.png');
