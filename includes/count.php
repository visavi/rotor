<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
	exit(header('Location: /index.php'));
}
// -------------------- Вывод статистики ------------------------------//
$week_day = date('w');
$arr_week = array('вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб');

$count = stats_counter();
$online = stats_online();

if (file_exists(DATADIR.'/temp/counter7.dat')) {
	$host_data = unserialize(file_get_contents(DATADIR.'/temp/counter7.dat'));
} else {
	$host_data = array_fill(0, 6, 0);
}

array_push($host_data, $count['count_dayhosts']);
// ----------------------------------------------------------------------//
$img = imageCreateFromPNG(BASEDIR.'/images/img/counter.png');
$color = imagecolorallocate($img, 0, 0, 0);
$color2 = imagecolorallocate($img, 102, 102, 102);

if ($online[1] >= 0 && $online[1] < 10) $pos = 65;
if ($online[1] >= 10 && $online[1] < 100) $pos = 52;
if ($online[1] >= 100 && $online[1] < 200) $pos = 44;
if ($online[1] >= 200 && $online[1] < 1000) $pos = 40;

$colors = array(array(191, 109, 232), array(87, 164, 246), array(0, 203, 189), array(0, 199, 68), array(149, 217, 0), array(255, 255, 0), array(255, 181, 0));

$max = max($host_data);
if ($max == 0) $max = 1;

$per_host = array();
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
		imagefilledrectangle ($img, $x1-1, $y1, $x2-2, $y2, $col);
	}

	imageTTFtext($img, 6, 0, $x1, 15, $color, BASEDIR.'/assets/fonts/font.ttf', $arr_week[$week_day]);

	$x1 += $coll;
	$x2 += $coll;
}

imageTTFtext($img, 6, 0, 13, 23, $color2, BASEDIR.'/assets/fonts/font4.ttf', $count['count_dayhosts']);
imageTTFtext($img, 6, 0, 13, 29, $color2, BASEDIR.'/assets/fonts/font4.ttf', $count['count_dayhits']);
imageTTFtext($img, 12, 0, $pos, 29, $color2, BASEDIR.'/assets/fonts/font7.ttf', $online[1]);

ImagePNG($img, BASEDIR.'/upload/counters/counter_new.png');
ImageDestroy($img);

rename(BASEDIR.'/upload/counters/counter_new.png', BASEDIR.'/upload/counters/counter.png');
