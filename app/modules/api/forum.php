<?php

header('Content-type: application/json');
header('Content-Disposition: inline; filename="forum.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$id = (!empty($_REQUEST['id'])) ? abs(intval($_REQUEST['id'])) : null;

if (!empty($key)){

    $user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `apikey`=? LIMIT 1;", array($key));
    if (!empty($user)){

        $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", array($id));
        if (!empty($topic)) {

            $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `topics_id`=? ORDER BY `time` ASC;", array($id));
            $posts = $querypost->fetchAll();

            $messages = array();
            foreach ($posts as $post) {

                $post['text'] = bb_code(str_replace('<img src="/images/', '<img src="'.$config['home'].'/images/', $post['text']));

                $messages[] = array(
                    'author' => $post['user'],
                    'text'   => $post['text'],
                    'time'   => $post['time']
                );
            }

            echo json_encode(array(
                'id' => $topic['id'],
                'author' => $topic['author'],
                'title' => $topic['title'],
                'messages' => $messages
            ));

        } else {echo json_encode(array('error'=>'notopic'));}
    } else {echo json_encode(array('error'=>'nouser'));}
} else {echo json_encode(array('error'=>'nokey'));}
