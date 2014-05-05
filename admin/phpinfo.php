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

if (is_admin(array(101, 102))) {
	show_title('PHP-info');

	echo 'PHP version: <b>'.phpversion().'</b><br />';

	if (zend_version()) {
		echo 'Zend version: <b>'.zend_version().'</b><br />';
	}

	if (gd_info()) {
		$gd_info = preg_replace('/[^0-9\.]/', '', gd_info());
		echo 'GD Version: <b>'.$gd_info['GD Version'].'</b><br />';
	}

	$res = DB::run() -> querySingle("SELECT VERSION()");
	echo 'PDO MySQL: <b>'.preg_replace('/[^0-9\.]/', '', $res).'</b><br />';

	$ini = ini_get_all();

	echo '<br /><table width="99%" border="0" cellspacing="0" cellpadding="2">';
	echo '<tr bgcolor="ffff00"><td width="40%">Directive</td><td width="60%">Local Value</td></tr>';

	$q = 0;
	foreach($ini as $inikey => $inivalue) {
		$q++;

		if ($q&1) {
			$bgcolor = "#ffffff";
		} else {
			$bgcolor = "#e0e0e0";
		}

		if (strlen($inivalue['local_value']) > 40) {
			$inivalue['local_value'] = substr($inivalue['local_value'], 0, 40);
			$inivalue['local_value'] .= "...";
		}
		if ($inivalue['local_value'] == "") {
			$inivalue['local_value'] = 'no_value';
		}

		echo '<tr bgcolor="'.$bgcolor.'"><td width="40%">'.$inikey.'</td><td width="60%">'.check($inivalue['local_value']).'</td></tr>';
	}
	echo '</table><br />';

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';
} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
