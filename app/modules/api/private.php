<?php

header('Content-type: application/json');
header('Content-Disposition: inline; filename="private.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;
$count = (!empty($_REQUEST['count'])) ? abs(intval($_REQUEST['count'])) : 10;

if (!empty($key)){

    $user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `users_apikey`=? LIMIT 1;", array($key));
    if (!empty($user)){

        $query = DB::run() -> query("SELECT * FROM `inbox` WHERE `user`=? ORDER BY `time` DESC LIMIT ".$count.";", array($user['users_login']));
        $inbox = $query -> fetchAll();
        $total = count($inbox);

        if ($total > 0) {

            $messages = array();
            foreach ($inbox as $data) {

                $data['text'] = bb_code(str_replace('<img src="/images/', '<img src="'.$config['home'].'/images/', $data['text']));

                $messages[] = array(
                    'author' => $data['author'],
                    'text'   => $data['text'],
                    'time'   => $data['time'],
                );
            }

            echo json_encode(array(
                'total' => $total,
                'messages' => $messages
            ));

        } else {echo json_encode(array('error'=>'nomessages'));}
    } else {echo json_encode(array('error'=>'nouser'));}
} else {echo json_encode(array('error'=>'nokey'));}
