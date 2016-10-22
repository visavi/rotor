<?php
App::view($config['themes'].'/index');

include (DATADIR."/main/games.dat");

App::view($config['themes'].'/foot');
