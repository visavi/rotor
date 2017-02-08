<?php

include_once (APP.'/views/advert/forum.blade.php');

$forums = Forum::where('parent_id', 0)
    ->with('lastTopic.lastPost.user')
    ->with('children')
    ->orderBy('sort')
    ->get();

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

App::view('forum/index', compact('forums'));
