<?php

header('Content-type: application/json');
header('Content-Disposition: inline; filename="forum.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$id = (!empty($_REQUEST['id'])) ? abs(intval($_REQUEST['id'])) : null;

if (!empty($key)){

    $user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `apikey`=? LIMIT 1;", [$key]);
    if (!empty($user)){

        $topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$id]);
        if (!empty($topic)) {

            $querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `topic_id`=? ORDER BY `time` ASC;", [$id]);
            $posts = $querypost->fetchAll();

            $messages = [];
            foreach ($posts as $post) {

                $post['text'] = App::bbCode(str_replace('<img src="/images/', '<img src="'.$config['home'].'/images/', $post['text']));

                $messages[] = [
                    'author' => $post['user'],
                    'text'   => $post['text'],
                    'time'   => $post['time']
                ];
            }

            echo json_encode([
                'id' => $topic['id'],
                'author' => $topic['author'],
                'title' => $topic['title'],
                'messages' => $messages
            ]);

        } else {echo json_encode(['error'=>'notopic']);}
    } else {echo json_encode(['error'=>'nouser']);}
} else {echo json_encode(['error'=>'nokey']);}
