<?php

use App\Classes\Registry;
use App\Classes\Request;
use App\Models\Log;
use App\Models\Login;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

require __DIR__.'/bootstrap.php';

ob_start();
session_name('SID');
session_start();

date_default_timezone_set(setting('timezone'));

/**
 * Проверка на ip-бан
 */
if ($ipBan = ipBan()) {

    $ipSplit = explode('.', getClientIp());

    foreach($ipBan as $ip) {
        $matches = 0;
        $dbSplit = explode('.', $ip);

        foreach($ipSplit as $key => $split) {
            if (isset($dbSplit[$key]) && ($split == $dbSplit[$key] || $dbSplit[$key] == '*')) {
                $matches += 1;
            }
        }

        if ($matches == 4 && ! Request::is('banip', 'captcha')) {
            redirect('/banip');
        }
    }
}

/**
 * Счетчик запросов
 */
if (setting('doslimit')) {
    if (is_writeable(STORAGE.'/antidos')) {

        $dosfiles = glob(STORAGE.'/antidos/*.dat');
        foreach ($dosfiles as $filename) {
            $array_filemtime = @filemtime($filename);
            if ($array_filemtime < (time() - 60)) {
                @unlink($filename);
            }
        }
        // -------------------------- Проверка на время -----------------------------//
        if (file_exists(STORAGE.'/antidos/'.getClientIp().'.dat')) {
            $file_dos = file(STORAGE.'/antidos/'.getClientIp().'.dat');
            $file_str = explode('|', $file_dos[0]);
            if ($file_str[0] < (time() - 60)) {
                @unlink(STORAGE.'/antidos/'.getClientIp().'.dat');
            }
        }
        // ------------------------------ Запись логов -------------------------------//
        $write = time().'|'.server('REQUEST_URI').'|'.server('HTTP_REFERER').'|'.getUserAgent().'|'.getUser('login').'|';
        writeFiles(STORAGE.'/antidos/'.getClientIp().'.dat', $write."\r\n", 0, 0666);
        // ----------------------- Автоматическая блокировка ------------------------//
        if (counterString(STORAGE.'/antidos/'.getClientIp().'.dat') > setting('doslimit')) {

            if (!empty(setting('errorlog'))){

                $banip = Ban::where('ip', getClientIp())->first();

                if (! $banip) {

                    Log::query()->create([
                        'code'       => 666,
                        'request'    => utfSubstr(server('REQUEST_URI'), 0, 200),
                        'referer'    => utfSubstr(server('HTTP_REFERER'), 0, 200),
                        'user_id'    => getUser('id'),
                        'ip'         => getClientIp(),
                        'brow'       => getUserAgent(),
                        'created_at' => SITETIME,

                    ]);

                    DB::insert(
                        "insert ignore into ban (`ip`, `created_at`) values (?, ?);",
                        [getClientIp(), SITETIME]
                    );

                    ipBan(true);
                }
            }

            unlink(STORAGE.'/antidos/'.getClientIp().'.dat');
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
                        'ip' => getClientIp(),
                        'brow' => getUserAgent(),
                        'created_at' => SITETIME,
                    ]);
                }

                $user->update([
                    'visits' => DB::raw('visits + 1'),
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

    // Забанен
    if ($user->level == User::BANNED) {
        if (! Request::is('ban', 'rules', 'logout')) {
            redirect('/ban?log='.$user->login);
        }
    }

    // Подтверждение регистрации
    if (setting('regkeys') && $user->level == User::PENDED) {
        if (! Request::is('key', 'ban', 'login', 'logout')) {
            redirect('/key?log='.$user->login);
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
    if (! empty(setting('touchthemes'))) {
        if ($browser_detect->isTablet()) {
            $setting['themes'] = setting('touchthemes');
        }
    }
    if (! empty(setting('webthemes'))) {
        if (! $browser_detect->isMobile() && ! $browser_detect->isTablet()) {
            $setting['themes'] = setting('webthemes');
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
