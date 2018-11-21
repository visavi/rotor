<?php

namespace App\Modules\Test\Controllers;

use App\Controllers\BaseController;

class IndexController extends BaseController
{
    /**
     * @var \Illuminate\View\Factory|\Jenssegers\Blade\Blade
     */
    private $view;

    public function __construct()
    {
        $this->view = blade()->addNamespace('test', APP . '/Modules/Test/views');

        parent::__construct();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        return $this->view->make('test::index');
    }
}
