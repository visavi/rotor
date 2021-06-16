<?php

namespace App\Http\Middleware;

use App\Models\Login;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AuthenticateCookie
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
        if (Route::has('install') && $request->is('install*')) {
            return $next($request);
        }

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
        if (
            $request->hasCookie('login') &&
            $request->hasCookie('password') &&
            $request->session()->missing('id')
        ) {
            $login    = $request->cookie('login');
            $password = $request->cookie('password');

            $user = getUserByLogin($login);

            if ($user && $login === $user->login && $password === $user->password) {
                $request->session()->put('id', $user->id);
                $request->session()->put('password', $user->password);
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

        $language = $user->language ?? defaultSetting('language');
        $theme = $user->themes ?? defaultSetting('themes');

        if ($request->session()->has('language')) {
            $language = $request->session()->get('language');
        }

        if (! file_exists(resource_path('lang/' . $language))) {
            $language = defaultSetting('language');
        }

        if (! file_exists(public_path('themes/' . $theme))) {
            $theme = defaultSetting('themes');
        }

        App::setLocale($language);
        View::addLocation(public_path('themes/' . $theme . '/views'));

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
