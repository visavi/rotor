<?php

use FastRoute\RouteCollector;

/* Игры */
$r->addGroup('/games', function (RouteCollector $r) {
    $r->get('', [App\Modules\Game\Controllers\IndexController::class, 'index']);

    $r->get('/dice', [App\Modules\Game\Controllers\DiceController::class, 'index']);
    $r->get('/dice/go', [App\Modules\Game\Controllers\DiceController::class, 'go']);

    $r->get('/thimbles', [App\Modules\Game\Controllers\ThimbleController::class, 'index']);
    $r->get('/thimbles/choice', [App\Modules\Game\Controllers\ThimbleController::class, 'choice']);
    $r->get('/thimbles/go', [App\Modules\Game\Controllers\ThimbleController::class, 'go']);
});
