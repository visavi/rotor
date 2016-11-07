<?php

$tid  = isset($params['tid']) ? abs(intval($params['tid'])) : 0;

$topic = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

if (empty($topic)) {
    App::abort('default', 'Данной темы не существует!');
}

$querypost = DB::run() -> query("SELECT * FROM `posts` WHERE `topics_id`=? ORDER BY `time` ASC;", [$tid]);
$posts = $querypost->fetchAll();

App::view('forum/print', compact('topic', 'posts'));
