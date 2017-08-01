<?php

$id    = abs(intval(Request::input('id')));
$type  = check(Request::input('type'));
$vote  = check(Request::input('vote'));
$token = check(Request::input('token'));

// Время хранения голосов
$expiresRating = SITETIME + 3600 * 24 * 30;

if (! is_user()) {
    exit(json_encode(['status' => 'error', 'message' => 'not authorized']));
}

if ($token != $_SESSION['token']) {
    exit(json_encode(['status' => 'error', 'message' => 'invalid token']));
}

if (! in_array($vote, [-1, 1])) {
    exit(json_encode(['status' => 'error', 'message' => 'invalid rating']));
}

Polling::where('relate_type', $type)
    ->where('created_at', '<', SITETIME)
    ->delete();

$post = $type::where('user_id', '<>', App::getUserId())->find($id);
if (! $post) {
    exit(json_encode(['status' => 'error', 'message' => 'message not found']));
}

$polling = Polling::where('relate_type', $type)
    ->where('relate_id', $id)
    ->where('user_id', App::getUserId())
    ->first();

$cancel = false;

if ($polling) {
    if ($polling['vote'] == $vote) {
        exit(json_encode(['status' => 'error']));
    } else {

        $polling->delete();
        $cancel = true;
    }
} else {
    $poll = Polling::create([
        'relate_type' => $type,
        'relate_id'   => $id,
        'user_id'     => App::getUserId(),
        'vote'        => $vote,
        'created_at'  => $expiresRating,
    ]);
}

$operation = ($vote == '1') ? '+' : '-';

$post->update(['rating' => Capsule::raw("rating $operation 1")]);
$post = $type::find($id);

echo json_encode([
    'status' => 'success',
    'cancel' => $cancel,
    'rating' => format_num($post['rating'])
]);
