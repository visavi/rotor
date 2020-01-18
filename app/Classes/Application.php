<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Setting;
use App\Models\User;
use DI\Container;
use FastRoute\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mobile_Detect;

class Application
{
    /** @var array */
    public $settings;

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
     * Устанавливает настройки
     *
     * @return void
     */
    private function setSetting(): void
    {
        $userSets = [];
        $user     = getUser();
        $browser  = new Mobile_Detect();
        $isWeb    = ! $browser->isMobile() && ! $browser->isTablet();

        $userSets['themes'] = $user ? $user['themes'] : 0;
        $userSets['language'] = $user ? $user['language'] : defaultSetting('language');

        if (empty($userSets['themes']) && defaultSetting('webthemes') && $isWeb) {
            $userSets['themes'] = defaultSetting('webthemes');
        }

        if (! file_exists(HOME . '/themes/' . $userSets['themes'])) {
            $userSets['themes'] = 'default';
        }

        if (isset($_SESSION['language']) && ! $user) {
            $userSets['language'] = $_SESSION['language'];
        }

        if (empty($userSets['language']) || ! file_exists(RESOURCES . '/lang/' . $userSets['language'])) {
            $userSets['language'] = 'ru';
        }

        Setting::setUserSettings($userSets);

        /* Установка сессионных переменных */
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = Str::random(8);
        }

        if (empty($_SESSION['protect'])) {
            $_SESSION['protect'] = mt_rand(1000, 99999);
        }

        if (empty($_SESSION['hits'])) {
            $_SESSION['hits'] = 0;
        }
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
                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(config('APP_KEY') . $user->password);
                $_SESSION['online']   = null;

                $user->saveVisit();
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
        /** @var User $user */
        if ($user = getUser()) {
            $request = request();

            // Забанен
            if ($user->level === User::BANNED && ! $request->is('ban', 'rules', 'logout')) {
                redirect('/ban?user=' . $user->login);
            }

            // Подтверждение регистрации
            if ($user->level === User::PENDED && defaultSetting('regkeys') && ! $request->is('key', 'ban', 'login', 'logout')) {
                redirect('/key?user=' . $user->login);
            }

            $user->updatePrivate();

            // Получение ежедневного бонуса
            if ($user->timebonus < strtotime('-23 hours', SITETIME)) {
                $user->increment('money', defaultSetting('bonusmoney'));
                $user->update(['timebonus' => SITETIME]);

                setFlash('success', __('main.daily_bonus', ['money' => plural(defaultSetting('bonusmoney'), defaultSetting('moneyname'))]));
            }
        }
    }
}
