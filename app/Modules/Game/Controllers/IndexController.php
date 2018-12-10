<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

class IndexController extends \App\Controllers\BaseController
{
    /**
     * Главная страница
     */
    public function index(): string
    {
        return view('Game::index');
    }
}
