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

$error = (isset($_GET['error'])) ? intval($_GET['error']) : '';

if (!empty($config['errorlog'])){

	switch ($error):
	############################################################################################
	##                                       Ошибка 403                                       ##
	############################################################################################
	case '403':

		DB::run()->query("INSERT INTO `error` (`error_num`, `error_request`, `error_referer`, `error_username`, `error_ip`, `error_brow`, `error_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", [403, $request_uri, $http_referer, $username, $ip, $brow, SITETIME]);

		DB::run()->query("DELETE FROM `error` WHERE `error_num`=? AND `error_time` < (SELECT MIN(`error_time`) FROM (SELECT `error_time` FROM `error` WHERE `error_num`=? ORDER BY `error_time` DESC LIMIT ".$config['maxlogdat'].") AS del);", [403, 403]);

		notice('ERROR 403. Недопустимый запрос!');
	break;

	############################################################################################
	##                                       Ошибка 403                                       ##
	############################################################################################
	case '404':
		DB::run()->query("INSERT INTO `error` (`error_num`, `error_request`, `error_referer`, `error_username`, `error_ip`, `error_brow`, `error_time`) VALUES (?, ?, ?, ?, ?, ?, ?);", [404, $request_uri, $http_referer, $username, $ip, $brow, SITETIME]);

		DB::run()->query("DELETE FROM `error` WHERE `error_num`=? AND `error_time` < (SELECT MIN(`error_time`) FROM (SELECT `error_time` FROM `error` WHERE `error_num`=? ORDER BY `error_time` DESC LIMIT ".$config['maxlogdat'].") AS del);", [404, 404]);

		notice('ERROR 404. Извините, но такой страницы не существует!');
	break;

	endswitch;
}

redirect('/');
