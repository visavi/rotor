<?php

switch ($act):
/**
 * RSS всех блогов
 */
case 'index':

    $blogs = Blog::orderBy('created_at', 'desc')
        ->limit(15)
        ->get();

    if ($blogs->isEmpty()) {
        App::abort('default', 'Блоги не найдены!');
    }

    App::view('blog/rss', compact('blogs'));
break;

/**
 * RSS комментариев к блогу
 */
case 'comments':

    $id = param('id');
    $blog = Blog::where('id', $id)->with('lastComments')->first();

    if (! $blog) {
        App::abort('default', 'Статья не найдена!');
    }

    App::view('blog/rss_comments', compact('blog'));
break;
endswitch;
