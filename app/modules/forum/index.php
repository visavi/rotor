<?php

include_once (APP.'/views/advert/forum.blade.php');

$forums = Forum::where('parent_id', 0)
    ->with('countTopic', 'countPost', 'children.countTopic', 'lastTopic.lastPost.user')
    ->order_by_asc('sort')
    ->find_many();

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

App::view('forum/index', compact('forums'));
