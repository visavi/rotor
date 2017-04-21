<?php

require __DIR__.'/bootstrap.php';

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

        $checkuser = DB::run() -> queryFetch("SELECT * FROM `users` WHERE `login`=? LIMIT 1;", [$unlog]);

        if (!empty($checkuser)) {
            if ($unlog == $checkuser['login'] && $unpar == md5($checkuser['password'].env('APP_KEY'))) {
                session_regenerate_id(1);

                $_SESSION['id'] = $checkuser['id'];
                $_SESSION['login'] = $unlog; // TODO удалить
                $_SESSION['password'] = md5(env('APP_KEY').$checkuser['password']);

                $authorization = DB::run() -> querySingle("SELECT `id` FROM `login` WHERE `user`=? AND `time`>? LIMIT 1;", [$unlog, SITETIME-30]);

                if (empty($authorization)) {
                    DB::run() -> query("INSERT INTO `login` (`user`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?);", [$unlog, App::getClientIp(), App::getUserAgent(), SITETIME]);
                    DB::run() -> query("DELETE FROM `login` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `login` WHERE `user`=? ORDER BY `time` DESC LIMIT 50) AS del);", [$unlog, $unlog]);
                }

                DB::run() -> query("UPDATE `users` SET `visits`=`visits`+1, `timelastlogin`=? WHERE `login`=? LIMIT 1;", [SITETIME, $unlog]);
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

//ob_start('ob_processing');

/**
 * Операции с пользователями
 */
if ($udata = is_user()) {

    Registry::set('user', $udata);

    $log = App::user('login');
    $setting['themes'] = App::user('themes');

    // Забанен
    if (App::user('ban')) {
        if (! Request::is('ban', 'rules', 'logout')) {
            redirect('/ban?log='.$log);
        }
    }

    // Подтверждение регистрации
    if (App::setting('regkeys') > 0 && App::user('confirmreg') > 0 && empty(App::user('ban'))) {
        if (! Request::is('key', 'login', 'logout')) {
            redirect('/key?log='.$log);
        }
    }

    // Просрочен кредит
    if (App::user('sumcredit') > 0 && SITETIME > App::user('timecredit') && empty(App::user('ban'))) {
        if (Request::path() != 'games/credit') {
            redirect('/games/credit?log='.$log);
        }
    }

    // ---------------------- Получение ежедневного бонуса -----------------------//
    if (App::user('timebonus') < time() - 82800) {

        $user = User::where('id', App::getUserId());
        $user->update([
            'timebonus' => SITETIME,
            'money' => Capsule::raw('money + '.App::setting('bonusmoney')),
        ]);

        notice('Получен ежедневный бонус '.moneys(App::setting('bonusmoney')).'!');
    }

    // ------------------ Запись текущей страницы для админов --------------------//
    if (Request::path() == 'admin') {
        DB::run() -> query("INSERT INTO `admlog` (`user`, `request`, `referer`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$log, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getClientIp(), App::getUserAgent(), SITETIME]);

        DB::run() -> query("DELETE FROM `admlog` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `admlog` ORDER BY `time` DESC LIMIT 500) AS del);");
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

if (! is_user() || empty(App::setting('themes'))) {
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
