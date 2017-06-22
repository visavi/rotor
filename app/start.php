<?php

require __DIR__.'/bootstrap.php';

ob_start();
session_name('SID');
session_start();

date_default_timezone_set(App::setting('timezone'));

/**
 * Проверка на ip-бан
 */
if ($ipBan = App::ipBan()) {

    $ipSplit = explode('.', App::getClientIp());

    foreach($ipBan as $ip) {
        $matches = 0;
        $dbSplit = explode('.', $ip);

        foreach($ipSplit as $key => $split) {
            if (isset($dbSplit[$key]) && ($split == $dbSplit[$key] || $dbSplit[$key] == '*')) {
                $matches += 1;
            }
        }

        if ($matches == 4 && ! Request::is('banip', 'captcha')) {
            App::redirect('/banip');
        }
    }
}

/**
 * Счетчик запросов
 */
if (App::setting('doslimit')) {
    if (is_writeable(STORAGE.'/antidos')) {

        $dosfiles = glob(STORAGE.'/antidos/*.dat');
        foreach ($dosfiles as $filename) {
            $array_filemtime = @filemtime($filename);
            if ($array_filemtime < (time() - 60)) {
                @unlink($filename);
            }
        }
        // -------------------------- Проверка на время -----------------------------//
        if (file_exists(STORAGE.'/antidos/'.App::getClientIp().'.dat')) {
            $file_dos = file(STORAGE.'/antidos/'.App::getClientIp().'.dat');
            $file_str = explode('|', $file_dos[0]);
            if ($file_str[0] < (time() - 60)) {
                @unlink(STORAGE.'/antidos/'.App::getClientIp().'.dat');
            }
        }
        // ------------------------------ Запись логов -------------------------------//
        $write = time().'|'.App::server('REQUEST_URI').'|'.App::server('HTTP_REFERER').'|'.App::getUserAgent().'|'.App::getUsername().'|';
        write_files(STORAGE.'/antidos/'.App::getClientIp().'.dat', $write."\r\n", 0, 0666);
        // ----------------------- Автоматическая блокировка ------------------------//
        if (counter_string(STORAGE.'/antidos/'.App::getClientIp().'.dat') > App::setting('doslimit')) {

            if (!empty(App::setting('errorlog'))){

                $banip = Ban::where('ip', App::getClientIp())->first();

                if (! $banip) {

                    Log::create([
                        'code'       => 666,
                        'request'    => utf_substr(App::server('REQUEST_URI'), 0, 200),
                        'referer'    => utf_substr(App::server('HTTP_REFERER'), 0, 200),
                        'user_id'    => App::getUserId(),
                        'ip'         => App::getClientIp(),
                        'brow'       => App::getUserAgent(),
                        'created_at' => SITETIME,

                    ]);

                    Capsule::insert(
                        "INSERT IGNORE INTO ban (`ip`, `created_at`) VALUES (?, ?);",
                        [App::getClientIp(), SITETIME]
                    );

                    App::ipBan(true);
                }
            }

            unlink(STORAGE.'/antidos/'.App::getClientIp().'.dat');
        }
    }
}

/**
 * Авторизация по кукам
 */
if (empty($_SESSION['id']) && empty($_SESSION['password'])) {
    if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {

        $cookLogin = check($_COOKIE['login']);
        $cookPass = check($_COOKIE['password']);

        $user = User::where('login', $cookLogin)->first();

        if ($user) {
            if ($cookLogin == $user->login && $cookPass == md5($user->password.env('APP_KEY'))) {
                session_regenerate_id(1);

                $_SESSION['id'] = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY').$user->password);

                $authorization = Login::where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::create([
                        'user_id' => $user->id,
                        'ip' => App::getClientIp(),
                        'brow' => App::getUserAgent(),
                        'created_at' => SITETIME,
                    ]);
                }

                $user->update([
                    'visits' => Capsule::raw('visits + 1'),
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
if ($user = is_user()) {

    Registry::set('user', $user);

    $setting['themes'] = App::user('themes');

    // Забанен
    if (App::user('ban')) {
        if (! Request::is('ban', 'rules', 'logout')) {
            App::redirect('/ban?log='.App::getUsername());
        }
    }

    // Подтверждение регистрации
    if (App::setting('regkeys') > 0 && App::user('confirmreg') > 0 && empty(App::user('ban'))) {
        if (! Request::is('key', 'login', 'logout')) {
            App::redirect('/key?log='.App::getUsername());
        }
    }

    // Просрочен кредит
    if (App::user('sumcredit') > 0 && SITETIME > App::user('timecredit') && empty(App::user('ban'))) {
        if (Request::path() != 'games/credit') {
            App::redirect('/games/credit?log='.App::getUsername());
        }
    }
    // ---------------------- Получение ежедневного бонуса -----------------------//
    if (App::user('timebonus') < SITETIME - 82800) {
        $user = User::where('id', App::getUserId());
        $user->update([
            'timebonus' => SITETIME,
            'money' => Capsule::raw('money + '.App::setting('bonusmoney')),
        ]);

        App::setFlash('success', 'Получен ежедневный бонус '.moneys(App::setting('bonusmoney')).'!');
    }

    // ------------------ Запись текущей страницы для админов --------------------//
    if (Request::segment(1) == 'admin') {

        Admlog::create([
            'user_id'    => App::getUserId(),
            'request'    => App::server('REQUEST_URI'),
            'referer'    => App::server('HTTP_REFERER'),
            'ip'         => App::getClientIp(),
            'brow'       => App::getUserAgent(),
            'created_at' => SITETIME,
        ]);

        Capsule::delete('
            DELETE FROM admlog WHERE created_at < (
                SELECT MIN(created_at) FROM (
                    SELECT created_at FROM admlog ORDER BY created_at DESC LIMIT 500
                ) AS del
            );'
        );
    }
}

// Сайт закрыт для всех
if (App::setting('closedsite') == 2 && !is_admin() && ! Request::is('closed', 'login')) {
    App::redirect('/closed');
}

// Сайт закрыт для гостей
if (App::setting('closedsite') == 1 && !is_user() && ! Request::is('register', 'login', 'recovery', 'captcha')) {
    App::setFlash('danger', 'Для входа на сайт необходимо авторизоваться!');
    App::redirect('/login');
}

/**
 * Автоопределение системы
 */
$browser_detect = new Mobile_Detect();

if (! is_user() || empty($setting['themes'])) {
    if (! empty(App::setting('touchthemes'))) {
        if ($browser_detect->isTablet()) {
            $setting['themes'] = App::setting('touchthemes');
        }
    }
    if (! empty(App::setting('webthemes'))) {
        if (! $browser_detect->isMobile() && ! $browser_detect->isTablet()) {
            $setting['themes'] = App::setting('webthemes');
        }
    }
}

if (empty($setting['themes']) || ! file_exists(HOME.'/themes/'.$setting['themes'])) {
    $setting['themes'] = 'default';
}

$files = glob(APP.'/plugins/*.php');
foreach ($files as $file) {
    require_once $file;
}

if (isset($setting)) {
    $setting = array_merge(Registry::get('setting'), $setting);
    Registry::set('setting', $setting);
}
