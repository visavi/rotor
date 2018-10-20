<?php

use App\Classes\Registry;
use App\Models\Login;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;

require __DIR__ . '/bootstrap.php';

ob_start();
session_name('SID');
session_start();

date_default_timezone_set(setting('timezone'));

$request = Request::createFromGlobals();

/**
 * Авторизация по кукам
 */
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
            'visits'     => DB::raw('visits + 1'),
            'updated_at' => SITETIME
        ]);
    }
}

/**
 * Установка сессионных переменных
 */
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = str_random(8);
}
if (empty($_SESSION['protect'])) {
    $_SESSION['protect'] = random_int(1000, 99999);
}

/**
 * Операции с пользователями
 */
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

    // ---------------------- Получение ежедневного бонуса -----------------------//
    if ($user->timebonus < SITETIME - 82800) {
        $user->update([
            'timebonus' => SITETIME,
            'money'     => DB::raw('money + ' . setting('bonusmoney')),
        ]);

        setFlash('success', 'Получен ежедневный бонус ' . plural(setting('bonusmoney'), setting('moneyname')) . '!');
    }
}

/**
 * Автоопределение системы
 */
$browser_detect = new Mobile_Detect();

if (! getUser() || empty(setting('themes'))) {
    if (! empty(setting('webthemes')) && ! $browser_detect->isMobile() && ! $browser_detect->isTablet()) {
        setSetting(['themes' => setting('webthemes')]);
    }
}

if (empty(setting('themes')) || ! file_exists(HOME . '/themes/' . setting('themes'))) {
    setSetting(['themes' => 'default']);
}

if (empty(setting('language')) || ! file_exists(RESOURCES . '/lang/' . setting('language'))) {
    setSetting(['language' => 'ru']);
}

return new \App\Classes\Application();
