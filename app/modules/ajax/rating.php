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

DBM::run()->delete('pollings', ['relate_type' => 'post', 'time' => ['<', SITETIME]]);

$post = DBM::run()->selectFirst('posts', ['id' => $id, 'user' => ['<>', $log]]);
if (! $post) {
    exit(json_encode(['status' => 'error']));
}

$polling = DBM::run()->selectFirst(
    'pollings', [
        'relate_type' => 'post',
        'relate_id' => $id,
        'user' => $log,
    ]
);

$cancel = false;

if ($polling) {
    if ($polling['vote'] == $vote) {
        exit(json_encode(['status' => 'error']));
    } else {
        DBM::run()->delete('pollings', ['relate_type' => 'post', 'relate_id' => $id, 'user' => $log]);
        $cancel = true;
    }
} else {

    $poll = DBM::run()->insert('pollings', [
        'relate_type' => $type,
        'relate_id' => $id,
        'user' => $log,
        'vote' => $vote,
        'time' => $expiresrating,
    ]);
}

$post = DBM::run()->update(
    'posts',
    ['rating' => [($vote == '1') ? '+' : '-', 1]], ['id' => $id]
);

$post = DBM::run()->selectFirst('posts', ['id' => $id]);

echo json_encode(['status' => 'success', 'cancel' => $cancel, 'count' => $post['rating']]);
