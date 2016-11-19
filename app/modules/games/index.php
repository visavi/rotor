<?php
App::view($config['themes'].'/index');

include (APP.'/views/main/games.blade.php');

App::view($config['themes'].'/foot');
