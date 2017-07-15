<?php

$blogs = CatsBlog::orderBy('sort')
    ->with('new')
    ->get()
    ->all();

if (! $blogs) {
    App::abort('default', 'Разделы блогов еще не созданы!');
}

App::view('blog/index', compact('blogs'));
