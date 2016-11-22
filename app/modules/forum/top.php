<?php

$total = DB::run() -> querySingle("SELECT count(*) FROM topics;");

if ($total > 0) {
    $page = App::paginate(App::setting('forumtem'), $total);

    $querytopic = DB::run() -> query("SELECT * FROM `topics` WHERE `closed`=? ORDER BY `posts` DESC LIMIT ".$page['offset'].", ".$config['forumtem'].";", [0]);
    $topics = $querytopic->fetchAll();

    App::view('forum/top', compact('topics', 'page'));

} else {
    show_error('Созданных тем еще нет!');
}
