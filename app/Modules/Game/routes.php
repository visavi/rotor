<?php

use FastRoute\RouteCollector;

/* Игры */
$r->addGroup('/games', function (RouteCollector $r) {
    $r->get('', [App\Modules\Game\Controllers\IndexController::class, 'index']);
    $r->get('/dice', [App\Modules\Game\Controllers\IndexController::class, 'dice']);
    $r->get('/dice/go', [App\Modules\Game\Controllers\IndexController::class, 'go']);
});
