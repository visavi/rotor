<?php

if (! Request::ajax() || ! is_admin()) {
    exit(json_encode(['status' => 'error', 'message' => 'not authorized']));
}

$token = check(Request::input('token'));
$type  = check(Request::input('type'));
$rid   = abs(intval(Request::input('rid')));
$id    = abs(intval(Request::input('id')));

$validation = new Validation();
$validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!');

if ($validation->run()) {
    $delComments = Comment::where('relate_type', $type)
        ->where('relate_id', $rid)
        ->where('id', $id)
        ->delete();

    if ($delComments) {
        $type::where('id', $rid)
            ->update([
                'comments'  => Capsule::raw('comments - '.$delComments),
            ]);
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => current($validation->getErrors())
    ]);
}
