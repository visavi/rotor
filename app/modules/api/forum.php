<?php

header('Content-type: application/json');
header('Content-Disposition: inline; filename="forum.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$id = (!empty($_REQUEST['id'])) ? abs(intval($_REQUEST['id'])) : null;

if (!empty($key)){

    $user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `users_apikey`=? LIMIT 1;", array($key));
    if (!empty($user)){

        $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `topics_id`=? LIMIT 1;", array($id));
        if (!empty($topic)) {

            $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `posts_topics_id`=? ORDER BY `posts_time` ASC;", array($id));
            $posts = $querypost->fetchAll();

            $messages = array();
            foreach ($posts as $post) {

                $post['posts_text'] = bb_code(str_replace('<img src="/images/', '<img src="'.$config['home'].'/images/', $post['posts_text']));

                $messages[] = array(
                    'author' => $post['posts_user'],
                    'text'   => $post['posts_text'],
                    'time'   => $post['posts_time']
                );
            }

            echo json_encode(array(
                'id' => $topic['topics_id'],
                'author' => $topic['topics_author'],
                'title' => $topic['topics_title'],
                'messages' => $messages
            ));

        } else {echo json_encode(array('error'=>'notopic'));}
    } else {echo json_encode(array('error'=>'nouser'));}
} else {echo json_encode(array('error'=>'nokey'));}
