<?php

namespace App\Classes;

class App
{
    /**
     * Запускает приложение
     */
    public static function run()
    {
        include_once APP.'/redirects.php';

        $router = Registry::get('router')->match();

        if ($router['target'] && is_callable($router['target'])) {

            echo call_user_func_array($router['target'], $router['params']);

        } elseif ($router['target']) {

            $target     = explode('@', $router['target']);
            $action     = $router['params']['action'] ?? $target[1];
            $controller = 'App\\Controllers\\'.$target[0];

            echo call_user_func_array([new $controller, $action], $router['params']);
        } else {
            abort(404);
        }
    }
}
