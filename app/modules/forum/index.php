<?php

include_once (APP.'/views/advert/forum.blade.php');

$forums = Forum::order_by_asc('sort')->find_many();

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

$output = [];

foreach ($forums as $row) {
    $id = $row['id'];
    $fp = $row['parent'];
    $output[$fp][$id] = $row;
}

App::view('forum/index', ['forums' => $output]);
