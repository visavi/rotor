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

Polling::where('relate_type', 'post')
    ->where_lt('time', SITETIME)
    ->delete_many();

$post = Post::where_not_equal('user', $log)->find_one($id);
if (! $post) {
    exit(json_encode(['status' => 'error']));
}

$polling = Polling::where('relate_type', 'post')
    ->where('relate_id', $id)
    ->where('user', $log)
    ->find_one();

$cancel = false;

if ($polling) {
    if ($polling['vote'] == $vote) {
        exit(json_encode(['status' => 'error']));
    } else {

        $polling->delete();
        $cancel = true;
    }
} else {

    $poll = Polling::create();
    $poll->set([
        'relate_type' => $type,
        'relate_id' => $id,
        'user' => $log,
        'vote' => $vote,
        'time' => $expiresrating,
    ]);
    $poll->save();
}

$operation = ($vote == '1') ? '+' : '-';
$post->set_expr('rating', "rating $operation 1");
$post->save();

$post = Post::find_one($id);

echo json_encode(['status' => 'success', 'cancel' => $cancel, 'count' => $post['rating']]);
