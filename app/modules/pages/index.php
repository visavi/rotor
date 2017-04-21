<?php
App::view(App::setting('themes').'/index');

if (! preg_match('|^[a-z0-9_\-]+$|i', $act)) {
    App::abort(404);
}

if (! file_exists(APP.'/views/main/'.$act.'.blade.php') || (! is_user() && $act == 'menu')){
    App::abort(404);
}

include (APP.'/views/main/'.$act.'.blade.php');

App::view(App::setting('themes').'/foot');
