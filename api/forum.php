<?php
#---------------------------------------------#
# ********* RotorCMS ********* #
# Author : Vantuz #
# Email : visavi.net@mail.ru #
# Site : http://visavi.net #
# ICQ : 36-44-66 #
# Skype : vantuzilla #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');

header('Content-type: application/json');
header('Content-Disposition: inline; filename="forum.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$topic_id = (!empty($_REQUEST['topic_id'])) ? abs(intval($_REQUEST['topic_id'])) : null;

if (!empty($key)){

	$user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `users_apikey`=? LIMIT 1;", array($key));
	$topic = DB::run() -> queryFetch("SELECT `topics`.*, `forums`.`forums_id`, `forums`.`forums_title`, `forums`.`forums_parent` FROM `topics` LEFT JOIN `forums` ON `topics`.`topics_forums_id`=`forums`.`forums_id` WHERE `topics_id`=? LIMIT 1;", array($topic_id));
	
	if (!empty($user)){
	
		if (!empty($topic)) {

			$querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC;", array($topic_id));
			$topic['posts'] = $querypost->fetchAll();
			
			$messages = array();
			foreach ($topic['posts'] as $data) {
			
				$data['posts_text'] = bb_code($data['posts_text']);
				
				$messages[] = array(
					'author' => $data['posts_user'],
					'text'   => $data['posts_text'],
					'time'   => $data['posts_time']
				);
			}
			
			echo json_encode(array(
				'topic_id' => $topic['topics_id'],
				'topic_author' => $topic['topics_author'],
				'topic_title' => $topic['topics_title'],
				'messages' => $messages
			));
			
		} else {echo json_encode(array('error'=>'notopic'));}	
	} else {echo json_encode(array('error'=>'nouser'));}
} else {echo json_encode(array('error'=>'nokey'));}

?>
