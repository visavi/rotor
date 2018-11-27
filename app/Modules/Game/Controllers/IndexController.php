<?php

namespace App\Modules\Game\Controllers;

use App\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * Возвращает шаблонизатор
     *
     * @return \Illuminate\View\Factory|\Jenssegers\Blade\Blade
     */
    protected function view()
    {
        return blade()->addNamespace('game', APP . '/Modules/Game/views');
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        return $this->view->make('game::index');
    }
}
