<?php
include_once (STORAGE.'/advert/forum.dat');

$forums = DBM::run()->select('forums', null, null, null, ['`order`'=>'ASC']);

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
