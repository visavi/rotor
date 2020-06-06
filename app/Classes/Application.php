<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Setting;
use DI\Container;
use FastRoute\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Application
{
    /**
     * Application constructor
     */
    public function __construct()
    {
        if (empty(defaultSetting('app_installed')) && file_exists(HOME . '/install/')) {
            redirect('/install/index.php');
        }

        session_start();
        date_default_timezone_set(defaultSetting('timezone'));

        $this->cookieAuth();
        $this->setSetting();
    }

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
        $container->set(Request::class, request());

        return $container->call($controller, $params);
    }

    /**
     * Возвращает роутеры
     *
     * @return array
     */
    private function getRouter(): array
    {
        $dispatcher = require APP . '/routes.php';

        return $dispatcher->dispatch(request()->getMethod(), request()->getPathInfo());
    }

    /**
     * Авторизует по кукам
     *
     * @return void
     */
    private function cookieAuth(): void
    {
        if (empty($_SESSION['id']) && isset($_COOKIE['login'], $_COOKIE['password'])) {
            $login    = check($_COOKIE['login']);
            $password = check($_COOKIE['password']);

            $user = getUserByLogin($login);

            if ($user && $login === $user->login && $password === md5($user->password . config('APP_KEY'))) {
                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(config('APP_KEY') . $user->password);
                $_SESSION['online']   = null;

                $user->saveVisit();
            }
        }
    }

    /**
     * Устанавливает настройки
     *
     * @return void
     */
    private function setSetting(): void
    {
        $user = getUser();

        $userSets['language'] = $user ? $user['language'] : defaultSetting('language');
        $userSets['themes'] = $user ? $user['themes'] : defaultSetting('themes');

        if (isset($_SESSION['language'])) {
            $userSets['language'] = $_SESSION['language'];
        }

        if (! file_exists(RESOURCES . '/lang/' . $userSets['language'])) {
            $userSets['language'] = defaultSetting('language');
        }

        if (! file_exists(HOME . '/themes/' . $userSets['themes'])) {
            $userSets['themes'] = defaultSetting('themes');
        }

        Setting::setUserSettings($userSets);

        if ($user) {
            $user->checkAccess();
            $user->updatePrivate();
            $user->gettingBonus();
        }

        /* Установка сессионных переменных */
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = Str::random(8);
        }

        if (empty($_SESSION['protect'])) {
            $_SESSION['protect'] = mt_rand(10000, 99999);
        }

        if (empty($_SESSION['hits'])) {
            $_SESSION['hits'] = 0;
        }
    }
}
