<?php

$tid = param('tid');

$topic = Topic::find($tid);

if (empty($topic)) {
    App::abort('default', 'Данной темы не существует!');
}

$posts = Post::where('topic_id', $tid)
    ->with('user')
    ->orderBy('created_at')
    ->get();

App::view('forum/print', compact('topic', 'posts'));
