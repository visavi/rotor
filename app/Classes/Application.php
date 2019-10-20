<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\User;
use DI\Container;
use FastRoute\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mobile_Detect;

class Application
{
    public function __construct()
    {
        if (config('APP_NEW') && file_exists(HOME . '/install/')) {
            redirect('/install/index.php');
        }

        session_start();
        date_default_timezone_set(setting('timezone'));

        $this->cookieAuth();
        $this->checkAuth();
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

        return $dispatcher->dispatch(server('REQUEST_METHOD'), server('PHP_SELF'));
    }

    /**
     * Авторизует по кукам
     *
     * @return void
     */
    private function cookieAuth(): void
    {
        if (empty($_SESSION['id']) && isset($_COOKIE['login'], $_COOKIE['password'])) {

            $cookLogin = check($_COOKIE['login']);
            $cookPass  = check($_COOKIE['password']);

            $user = getUserByLogin($cookLogin);

            if ($user && $cookLogin === $user->login && $cookPass === md5($user->password . config('APP_KEY'))) {
                session_regenerate_id(true);

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(config('APP_KEY') . $user->password);

                User::saveVisit($user);
            }
        }
    }

    /**
     * Проверяет пользователя
     *
     * @return void
     */
    private function checkAuth(): void
    {
        $request = request();

        if ($user = checkAuth()) {

            Registry::set('user', $user);

            setSetting([
                'themes'   => $user->themes,
                'language' => $user->language,
            ]);

            // Забанен
            if ($user->level === User::BANNED && ! $request->is('ban', 'rules', 'logout')) {
                redirect('/ban?user=' . $user->login);
            }

            // Подтверждение регистрации
            if ($user->level === User::PENDED && setting('regkeys') && ! $request->is('key', 'ban', 'login', 'logout')) {
                redirect('/key?user=' . $user->login);
            }

            // Получение ежедневного бонуса
            if ($user->timebonus < strtotime('-23 hours', SITETIME)) {

                $user->increment('money', setting('bonusmoney'));
                $user->update(['timebonus' => SITETIME]);

                setFlash('success', 'Получен ежедневный бонус ' . plural(setting('bonusmoney'), setting('moneyname')) . '!');
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
        $browser = new Mobile_Detect();

        if (! getUser() || empty(setting('themes'))) {
            if (! empty(setting('webthemes')) && ! $browser->isMobile() && ! $browser->isTablet()) {
                setSetting(['themes' => setting('webthemes')]);
            }
        }

        if (empty(setting('themes')) || ! file_exists(HOME . '/themes/' . setting('themes'))) {
            setSetting(['themes' => 'default']);
        }

        if (isset($_SESSION['language']) && ! getUser()) {
            setSetting(['language' => $_SESSION['language']]);
        }

        if (empty(setting('language')) || ! file_exists(RESOURCES . '/lang/' . setting('language'))) {
            setSetting(['language' => 'ru']);
        }

        /* Установка сессионных переменных */
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = Str::random(8);
        }

        if (empty($_SESSION['protect'])) {
            $_SESSION['protect'] = mt_rand(1000, 99999);
        }
    }
}
