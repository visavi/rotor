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
        $blade = blade()->addNamespace('game', APP . '/Modules/Game/resources/views');

        return $blade->make($view, $params)->render();
    }

    /**
     * Translate the given message.
     *
     * @param  string  $key
     * @param  array   $replace
     * @param  string  $locale
     * @return string
     */
    protected function trans($key, array $replace = [], $locale = null)
    {
        $trans = translator(); //->addNamespace('game', APP . '/Modules/Game/resources/lang');
        $trans->addNamespace('game', APP . '/Modules/Game/resources/lang');

        return $trans->trans($key, $replace, $locale);
    }

    /**
     * Главная страница
     */
    public function index(): string
    {

        var_dump(get_class_methods($this->trans('game::test.test')));

        return $this->view('game::index');
    }
}
