<?php

namespace App\Http\Middleware;

use App\Models\Login;
use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class CookieAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /*if (empty(defaultSetting('app_installed')) && file_exists(HOME . '/install/')) {
            return redirect('install/index.php');
        }*/

        $this->cookieAuth($request);
        $this->setSetting($request);

        return $next($request);
    }

    /**
     * Авторизует по кукам
     *
     * @param Request $request
     *
     * @return void
     */
    private function cookieAuth(Request $request): void
    {
        if ($request->hasCookie('login') &&
            $request->hasCookie('password') &&
            $request->session()->missing('id')
        ) {
            $login    = $request->cookie('login');
            $password = $request->cookie('password');

            $user = getUserByLogin($login);

            if ($user && $login === $user->login && $password === md5($user->password . config('app.key'))) {
                $request->session()->put('id', $user->id);
                $request->session()->put('password', md5(config('app.key') . $user->password));
                $request->session()->put('online');

                $user->saveVisit(Login::COOKIE);
            }
        }
    }

    /**
     * Устанавливает настройки
     *
     * @param Request $request
     *
     * @return void
     */
    private function setSetting(Request $request): void
    {
        $user = getUser();

        $userSets['language'] = $user->language ?? defaultSetting('language');
        $userSets['themes'] = $user->themes ?? defaultSetting('themes');

        if ($request->session()->has('language')) {
            $userSets['language'] = $request->session()->get('language');
        }

        if (! file_exists(RESOURCES . '/lang/' . $userSets['language'])) {
            $userSets['language'] = defaultSetting('language');
        }

        if (! file_exists(HOME . '/themes/' . $userSets['themes'])) {
            $userSets['themes'] = defaultSetting('themes');
        }

        Setting::setUserSettings($userSets);

        App::setLocale($userSets['language']);

        if ($user) {
            $user->checkAccess();
            $user->updatePrivate();
            $user->gettingBonus();
        }

        /* Установка сессионных переменных */
        if ($request->session()->missing('hits')) {
            $request->session()->put('hits', 0);
        }
    }
}
