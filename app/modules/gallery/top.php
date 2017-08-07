<?php

$page = abs(intval(Request::input('page', 1)));
$sort = check(Request::input('sort', 'rating'));

switch ($sort) {
    case 'rating': $order = 'rating';
        break;
    case 'comments': $order = 'comments';
        break;
    default: $order = 'rating';
}

$total = Photo::count();
$page = App::paginate(Setting::get('fotolist'), $total);

$photos = Photo::orderBy($order, 'desc')
    ->offset($page['offset'])
    ->limit(Setting::get('fotolist'))
    ->with('user')
    ->get();

App::view('gallery/top', compact('photos', 'page', 'order'));
