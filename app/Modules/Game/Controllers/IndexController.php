<?php

declare(strict_types=1);

namespace App\Modules\Game\Controllers;

use App\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * Возвращает шаблонизатор
     *
     * @param string $view
     * @param array  $params
     * @return string
     */
    protected function view(string $view, array $params = []): string
    {
        $blade = blade()->addNamespace('game', APP . '/Modules/Game/views');

        return $blade->make($view, $params)->render();
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        return $this->view('game::index');
    }
}
