<?php
if (! is_user()) {
    exit(json_encode(['status' => 'error', 'message' => 'Вы не авторизованы']));
}

$expiresrating = SITETIME + 3600 * 24 * 30; // 30 дней

$id = abs(intval(Request::input('id')));
$type = check(Request::input('type'));
$vote = check(Request::input('vote'));

if (! in_array($vote, [1, -1])) {
    exit(json_encode(['status' => 'error']));
}

Polling::where('relate_type', $type)
    ->where('created_at', '<', SITETIME)
    ->delete();

$post = Post::where('user_id', '<>', App::getUserId())->find($id);
if (! $post) {
    exit(json_encode(['status' => 'error']));
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
        'relate_id' => $id,
        'user_id' => App::getUserId(),
        'vote' => $vote,
        'created_at' => $expiresrating,
    ]);
}

$operation = ($vote == '1') ? '+' : '-';

$post->update(['rating' => Capsule::raw("rating $operation 1")]);
$post = Post::find($id);

echo json_encode(['status' => 'success', 'cancel' => $cancel, 'count' => $post['rating']]);
