<?php
App::view($config['themes'].'/index');

include (STORAGE."/main/games.dat");

App::view($config['themes'].'/foot');
