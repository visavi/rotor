<?php

namespace App\Classes;

use App\Models\Login;
use App\Models\User;
use DI\Container;
use FastRoute\Dispatcher;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Mobile_Detect;

class Application
{
    public function __construct()
    {
        ob_start();
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
        $container->set(Request::class, Request::createFromGlobals());

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

            $user = User::query()->where('login', $cookLogin)->first();

            if ($user && $cookLogin === $user->login && $cookPass === md5($user->password . env('APP_KEY'))) {
                session_regenerate_id(1);

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY') . $user->password);

                $authorization = Login::query()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::query()->create([
                        'user_id'    => $user->id,
                        'ip'         => getIp(),
                        'brow'       => getBrowser(),
                        'created_at' => SITETIME,
                    ]);
                }

                $user->update([
                    'visits'     => DB::connection()->raw('visits + 1'),
                    'updated_at' => SITETIME
                ]);
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
        $request = Request::createFromGlobals();

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
            if ($user->timebonus < SITETIME - 82800) {
                $user->update([
                    'timebonus' => SITETIME,
                    'money'     => DB::connection()->raw('money + ' . setting('bonusmoney')),
                ]);

                setFlash('success', 'Получен ежедневный бонус ' . plural(setting('bonusmoney'), setting('moneyname')) . '!');
            }
        }
    }

    /**
     * Устанавливает настройки
     *
     * @return void
     * @throws \Exception
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

        if (empty(setting('language')) || ! file_exists(RESOURCES . '/lang/' . setting('language'))) {
            setSetting(['language' => 'ru']);
        }

        /* Установка сессионных переменных */
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = str_random(8);
        }

        if (empty($_SESSION['protect'])) {
            $_SESSION['protect'] = random_int(1000, 99999);
        }
    }
}
