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
	header('Location: /index.php');
	exit;
}

$querylink = DB::run() -> query("SELECT * FROM `pyramid` ORDER BY `pyramid_id` DESC;");
$links = $querylink -> fetchAll();

if (count($links) > 0) {
	foreach ($links as $data) {
		echo '<img src="/images/img/act.png" alt="image" /> <a href="'.$data['pyramid_link'].'">'.$data['pyramid_name'].'</a><br />';
	}
} else {
	show_error('В списке еще никого нет, будь первым!');
}
?>
