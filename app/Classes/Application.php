<?php

namespace App\Classes;

use DI\Container;
use FastRoute\Dispatcher;
use Illuminate\Http\Request;

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

        if (isset($params['action'])) {
            $controller[1] = $params['action'];
        }

        $container = new Container();
        $container->set(Request::class, Request::createFromGlobals());

        try {
            return $container->call($controller, $params);
        } catch (\Exception $e) {
            return \call_user_func_array([new $controller[0], $controller[1]], $params);
        }
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
