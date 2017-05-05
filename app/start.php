<?php

require __DIR__.'/bootstrap.php';

ob_start();
session_name('SID');
session_start();

date_default_timezone_set(App::setting('timezone'));

/**
 * Проверка на ip-бан
 */
if (file_exists(STORAGE.'/temp/ipban.dat')) {
    $arrbanip = unserialize(file_get_contents(STORAGE.'/temp/ipban.dat'));
} else {
    $arrbanip = save_ipban();
}

if (is_array($arrbanip) && count($arrbanip) > 0) {

    foreach($arrbanip as $ipdata) {
        $ipmatch = 0;
        $ipsplit = explode('.', App::getClientIp());
        $dbsplit = explode('.', $ipdata);

        for ($i = 0; $i < 4; $i++) {
            if ($ipsplit[$i] == $dbsplit[$i] || $dbsplit[$i] == '*') {
                $ipmatch += 1;
            }
        }

        if ($ipmatch == 4 && ! Request::is('banip', 'captcha')) {
            redirect('/banip');
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

                    $error = new Log();
                    $error->code = 666;
                    $error->request = utf_substr(App::server('REQUEST_URI'), 0, 200);
                    $error->referer = utf_substr(App::server('HTTP_REFERER'), 0, 200);
                    $error->user_id = App::getUserId();
                    $error->ip = App::getClientIp();
                    $error->brow = App::getUserAgent();
                    $error->created_at = SITETIME;
                    $error->save();

                    Capsule::insert(
                        "INSERT IGNORE INTO ban (`ip`, `created_at`) VALUES (?, ?);",
                        [App::getClientIp(), SITETIME]
                    );

                    save_ipban();
                }
            }

            unlink(STORAGE.'/antidos/'.App::getClientIp().'.dat');
        }
    }
}

/**
 * Авторизация по кукам
 */
if (empty($_SESSION['login']) && empty($_SESSION['password'])) {
    if (isset($_COOKIE['login']) && isset($_COOKIE['password'])) {
        $unlog = check($_COOKIE['login']);
        $unpar = check($_COOKIE['password']);

        $user = User::where('login', $unlog)->first();

        if ($user) {
            if ($unlog == $user->login && $unpar == md5($user->password.env('APP_KEY'))) {
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
            redirect('/ban?log='.App::getUsername());
        }
    }

    // Подтверждение регистрации
    if (App::setting('regkeys') > 0 && App::user('confirmreg') > 0 && empty(App::user('ban'))) {
        if (! Request::is('key', 'login', 'logout')) {
            redirect('/key?log='.App::getUsername());
        }
    }

    // Просрочен кредит
    if (App::user('sumcredit') > 0 && SITETIME > App::user('timecredit') && empty(App::user('ban'))) {
        if (Request::path() != 'games/credit') {
            redirect('/games/credit?log='.App::getUsername());
        }
    }
    // ---------------------- Получение ежедневного бонуса -----------------------//
    if (App::user('timebonus') < SITETIME - 82800) {
        var_dump(date('Y-m-d H:i:s', $user['timebonus']), App::getUserId());

        $user = User::where('id', App::getUserId());
        $user->update([
            'timebonus' => SITETIME,
            'money' => Capsule::raw('money + '.App::setting('bonusmoney')),
        ]);

        notice('Получен ежедневный бонус '.moneys(App::setting('bonusmoney')).'!');
    }

    // ------------------ Запись текущей страницы для админов --------------------//
    if (Request::path() == 'admin') {
        DB::run() -> query("INSERT INTO `admlog` (`user_id`, `request`, `referer`, `ip`, `brow`, `created_at`) VALUES (?, ?, ?, ?, ?, ?);", [App::getUserId(), App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getClientIp(), App::getUserAgent(), SITETIME]);

        DB::run() -> query("DELETE FROM `admlog` WHERE `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `admlog` ORDER BY `created_at` DESC LIMIT 500) AS del);");
    }
}

// Сайт закрыт для всех
if (App::setting('closedsite') == 2 && !is_admin() && ! Request::is('closed', 'login')) {
    redirect('/closed');
}

// Сайт закрыт для гостей
if (App::setting('closedsite') == 1 && !is_user() && ! Request::is('register', 'login', 'lostpassword', 'captcha')) {
    notice('Для входа на сайт необходимо авторизоваться!');
    redirect('/login');
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
