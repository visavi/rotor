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

header('Content-type: application/json');
header('Content-Disposition: inline; filename="private.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$count = (!empty($_REQUEST['count'])) ? abs(intval($_REQUEST['count'])) : 10;

if (!empty($key)){

	$user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `users_apikey`=? LIMIT 1;", array($key));
	if (!empty($user)){

		$query = DB::run() -> query("SELECT * FROM `inbox` WHERE `inbox_user`=? ORDER BY `inbox_time` DESC LIMIT ".$count.";", array($user['users_login']));
		$inbox = $query -> fetchAll();
		$total = count($inbox);

		if ($total > 0) {

			$messages = array();
			foreach ($inbox as $data) {

				$data['inbox_text'] = bb_code($data['inbox_text']);
				$data['inbox_text'] = str_replace('<img src="/images/', '<img src="'.$config['home'].'/images/', $data['inbox_text']);

				$messages[] = array(
					'author' => $data['inbox_author'],
					'text'   => $data['inbox_text'],
					'time'   => $data['inbox_time'],
				);
			}

			echo json_encode(array(
				'total' => $total,
				'messages' => $messages
			));

		} else {echo json_encode(array('error'=>'nomessages'));}
	} else {echo json_encode(array('error'=>'nouser'));}
} else {echo json_encode(array('error'=>'nokey'));}

?>
