<?php

namespace App\Classes;

use Illuminate\Http\Request;
use FastRoute\Dispatcher;

class Application
{
    /**
     * Запускает приложение
     */
    public function run(): void
    {
        $router = $this->getRouter();

        switch ($router[0]) {
            case Dispatcher::FOUND:
                echo $this->call($router);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                abort(405);
                break;

            default:
                abort(404);
                break;
        }

       // $router['params']['request'] = Request::createFromGlobals();
    }

    /**
     * Вызывает контроллер
     *
     * @param array $router
     * @return mixed
     */
    private function call($router)
    {
        [, $controller, $params] = $router;
        $action = $params['action'] ?? $controller[1];

        return \call_user_func_array([new $controller[0], $action], $params);
    }

    /**
     * Возвращает роутеры
     *
     * @return array
     */
    private function getRouter(): array
    {
        $dispatcher = require APP . '/routes.php';

        return $dispatcher->dispatch(server('REQUEST_METHOD'), server('PHP_SELF'));
    }
}
