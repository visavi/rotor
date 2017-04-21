<?php
App::view(App::setting('themes').'/index');

include (APP.'/views/main/games.blade.php');

App::view(App::setting('themes').'/foot');
