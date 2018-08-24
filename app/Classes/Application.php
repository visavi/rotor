<?php

namespace App\Classes;

class Application
{
    /**
     * Запускает приложение
     */
    public static function run()
    {
        $router = self::getRouter();

        if (! $router['target']) {
            abort(404);
        }

        if (\is_callable($router['target'])) {
            $call = \call_user_func_array($router['target'], $router['params']);
        } else {
            list($controller, $action) = self::getController($router);
            $call = \call_user_func_array([new $controller, $action], $router['params']);
        }

        echo $call;
    }

    /**
     * Подготовливает пути из роутов
     *
     * @param $router
     * @return array
     */
    private static function getController($router)
    {
        $target = explode('@', $router['target']);
        $action = $router['params']['action'] ?? $target[1];

        return ['App\\Controllers\\'.$target[0], $action];
    }

    /**
     * Возвращает роутеры
     *
     * @return array
     */
    private static function getRouter()
    {
        return Registry::get('router')->match();
    }
}
