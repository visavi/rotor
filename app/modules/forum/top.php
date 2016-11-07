<?php

$start = abs(intval(Request::input('start', 0)));

$total = DB::run() -> querySingle("SELECT count(*) FROM topics;");

if ($total > 0) {
    if ($start >= $total) {
        $start = last_page($total, $config['forumtem']);
    }

    $querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `closed`=? ORDER BY `posts` DESC LIMIT ".$start.", ".$config['forumtem'].";", [0]);
    $topics = $querytopic->fetchAll();

    App::view('forum/top', compact('topics', 'start', 'total'));

} else {
    show_error('Созданных тем еще нет!');
}
