<?php

header('Content-type: application/json');
header('Content-Disposition: inline; filename="user.json";');

$key = (!empty($_REQUEST['key'])) ? check($_REQUEST['key']) : null;

if (!empty($key)){

    $user = DB::run()->queryFetch("SELECT * FROM `users` WHERE `apikey`=? LIMIT 1;", [$key]);
    if (!empty($user)){

        echo json_encode([
            'login'     => $user['login'],
            'email'     => $user['email'],
            'name'      => $user['name'],
            'country'   => $user['country'],
            'city'      => $user['city'],
            'site'      => $user['site'],
            'icq'       => $user['icq'],
            'skype'     => $user['skype'],
            'gender'    => $user['gender'],
            'birthday'  => $user['birthday'],
            'newwall'   => $user['newwall'],
            'point'     => $user['point'],
            'money'     => $user['money'],
            'ban'       => $user['ban'],
            'allprivat' => user_mail($user['login']),
            'newprivat' => $user['newprivat'],
            'status'    => user_title($user['login']),
            'avatar'    => Setting::get('home').'/uploads/avatars/'.$user['avatar'],
            'picture'   => Setting::get('home').'/uploads/photos/'.$user['picture'],
            'rating'    => $user['rating'],
            'lastlogin' => $user['timelastlogin'],
        ]);

    } else {echo json_encode(['error'=>'nouser']);}
} else {echo json_encode(['error'=>'nokey']);}

