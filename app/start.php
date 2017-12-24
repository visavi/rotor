<?php

use App\Classes\Registry;
use App\Classes\Request;
use App\Models\Login;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

require __DIR__.'/bootstrap.php';

ob_start();
session_name('SID');
session_start();

date_default_timezone_set(setting('timezone'));

/**
 * Авторизация по кукам
 */
if (empty($_SESSION['id']) && empty($_SESSION['password'])) {
    if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {

        $cookLogin = check($_COOKIE['login']);
        $cookPass = check($_COOKIE['password']);

        $user = User::query()->where('login', $cookLogin)->first();

        if ($user) {
            if ($cookLogin == $user->login && $cookPass == md5($user->password.env('APP_KEY'))) {
                session_regenerate_id(1);

                $_SESSION['id'] = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY').$user->password);

                $authorization = Login::query()
                    ->where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::query()->create([
                        'user_id' => $user->id,
                        'ip'      => getIp(),
                        'brow'    => getBrowser(),
                        'created_at' => SITETIME,
                    ]);
                }

                $user->update([
                    'visits'        => DB::raw('visits + 1'),
                    'timelastlogin' => SITETIME
                ]);
            }
        }
    }
}

/**
 * Установка сессионных переменных
 */
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = str_random(8);
}
if (empty($_SESSION['protect'])) {
    $_SESSION['protect'] = mt_rand(1000, 99999);
}

/**
 * Операции с пользователями
 */
if ($user = checkAuth()) {

    Registry::set('user', $user);

    $setting['themes'] = $user->themes;
    $setting['lang']   = $user->lang;

    // Забанен
    if ($user->level == User::BANNED) {
        if (! Request::is('ban', 'rules', 'logout')) {
            redirect('/ban?user='.$user->login);
        }
    }

    // Подтверждение регистрации
    if (setting('regkeys') && $user->level == User::PENDED) {
        if (! Request::is('key', 'ban', 'login', 'logout')) {
            redirect('/key?user='.$user->login);
        }
    }

    // ---------------------- Получение ежедневного бонуса -----------------------//
    if ($user->timebonus < SITETIME - 82800) {
        $user->update([
            'timebonus' => SITETIME,
            'money' => DB::raw('money + '.setting('bonusmoney')),
        ]);

        setFlash('success', 'Получен ежедневный бонус '.plural(setting('bonusmoney'), setting('moneyname')).'!');
    }
}

/**
 * Автоопределение системы
 */
$browser_detect = new Mobile_Detect();

if (! getUser() || empty($setting['themes'])) {
    if (! empty(setting('webthemes'))) {
        if (! $browser_detect->isMobile() && ! $browser_detect->isTablet()) {
            $setting['themes'] = setting('webthemes');
        }
    }
}

if (empty($setting['themes']) || ! file_exists(HOME.'/themes/'.$setting['themes'])) {
    $setting['themes'] = 'default';
}

if (empty($setting['lang']) || ! file_exists(RESOURCES.'/lang/'.$setting['lang'])) {
    $setting['lang'] = 'ru';
}

$files = glob(APP.'/plugins/*.php');
foreach ($files as $file) {
    require_once $file;
}

if (isset($setting)) {
    setSetting($setting);
}
