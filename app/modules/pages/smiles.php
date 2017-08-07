<?php
App::view(Setting::get('themes').'/index');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

//show_title('Список смайлов');

$total = Smile::count();
$page = App::paginate(Setting::get('smilelist'), $total);

if ($total > 0) {
    $smiles = Smile::orderBy(Capsule::raw('CHAR_LENGTH(`code`)'))
        ->orderBy('name')
        ->limit(Setting::get('smilelist'))
        ->offset($page['offset'])
        ->get();

    foreach($smiles as $smile) {
        echo '<img src="/uploads/smiles/'.$smile['name'].'" alt="" /> — <b>'.$smile['code'].'</b><br />';
    }

    App::pagination($page);

    echo 'Всего cмайлов: <b>'.$total.'</b><br /><br />';
} else {
    show_error('Смайлы не найдены!');
}

App::view(Setting::get('themes').'/foot');
