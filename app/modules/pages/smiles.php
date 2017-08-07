<?php

$total = Smile::count();
$page = App::paginate(Setting::get('smilelist'), $total);

$smiles = Smile::orderBy(Capsule::raw('CHAR_LENGTH(`code`)'))
    ->orderBy('name')
    ->limit(Setting::get('smilelist'))
    ->offset($page['offset'])
    ->get();

App::view('pages/smiles', compact('smiles', 'page'));

