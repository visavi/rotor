<?php
App::view(Setting::get('themes').'/index');

if (! preg_match('|^[a-z0-9_\-]+$|i', $action)) {
    App::abort(404);
}

if (! file_exists(APP.'/views/main/'.$action.'.blade.php') || (! is_user() && $action == 'menu')){
    App::abort(404);
}

include (APP.'/views/main/'.$action.'.blade.php');

App::view(Setting::get('themes').'/foot');
