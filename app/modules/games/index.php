<?php
App::view(Setting::get('themes').'/index');

include (APP.'/views/main/games.blade.php');

App::view(Setting::get('themes').'/foot');
