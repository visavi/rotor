<?php
include_once (STORAGE.'/advert/forum.dat');

$forums = DBM::run()->select('forums', null, null, null, ['forums_order'=>'ASC']);

if (empty(count($forums))) {
    App::abort('default', 'Разделы форума еще не созданы!');
}

$output = array();

foreach ($forums as $row) {
    $id = $row['forums_id'];
    $fp = $row['forums_parent'];
    $output[$fp][$id] = $row;
}

App::view('forum/index', ['forums' => $output]);
