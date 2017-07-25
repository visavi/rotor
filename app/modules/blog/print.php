<?php

$id = param('id');

$blog = Blog::find($id);

if (empty($blog)) {
    App::abort('default', 'Данной статьи не существует!');
}

$blog['text'] = preg_replace('|\[nextpage\](<br * /?>)*|', '', $blog['text']);

App::view('blog/print', compact('blog'));
