<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Login;
use App\Models\Setting;

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

        $this->cookieAuth();
        $this->setSetting();
    }





    /**
     * Авторизует по кукам
     *
     * @return void
     */
    private function cookieAuth(): void
    {
        if (empty($_SESSION['id']) && isset($_COOKIE['login'], $_COOKIE['password'])) {
            $login    = $_COOKIE['login'];
            $password = $_COOKIE['password'];

            $user = getUserByLogin($login);

            if ($user && $login === $user->login && $password === md5($user->password . config('app.key'))) {
                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(config('app.key') . $user->password);
                $_SESSION['online']   = null;

                $user->saveVisit(Login::COOKIE);
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

        $userSets['language'] = $user->language ?? defaultSetting('language');
        $userSets['themes'] = $user->themes ?? defaultSetting('themes');

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
        if (empty($_SESSION['hits'])) {
            $_SESSION['hits'] = 0;
        }
    }
}
