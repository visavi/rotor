<?php

include_once (APP.'/views/advert/forum.blade.php');

$forums = NewForum::where('parent_id', 0)
    ->with('countTopic')
    ->with('countPost')
    ->with('children.countTopic')
    ->with('children.countPost')
    ->with('lastTopic.lastPost.user')
    ->orderBy('sort')
    ->get();

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

App::view('forum/index', compact('forums'));
