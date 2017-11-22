<?php

namespace App\Classes;

class Application
{
    /**
     * Инициализирует приложение
     *
     * @return static
     */
    public static function init()
    {
        include_once APP.'/redirects.php';

        return new static();
    }

    /**
     * Запускает приложение
     */
    public function run()
    {
        $router = $this->getRouter();

        if (! $router['target']) {
            abort(404);
        }

        if (is_callable($router['target'])) {
            $call = call_user_func_array($router['target'], $router['params']);
        } else {
            $target     = explode('@', $router['target']);
            $action     = $router['params']['action'] ?? $target[1];
            $controller = 'App\\Controllers\\'.$target[0];

            $call = call_user_func_array([new $controller, $action], $router['params']);
        }

        echo $call;
    }

    /**
     * Возвращает роутеры
     *
     * @return array
     */
    private function getRouter()
    {
        return Registry::get('router')->match();
    }
}
