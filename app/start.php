<?php

require __DIR__.'/bootstrap.php';

session_name('SID');
session_start();

if (!file_exists(STORAGE.'/temp/setting.dat')) {
    $settings = DBM::run()->select('setting');
    $config = array_pluck($settings, 'value', 'name');
    file_put_contents(STORAGE.'/temp/setting.dat', serialize($config), LOCK_EX);
}
$config = unserialize(file_get_contents(STORAGE.'/temp/setting.dat'));

date_default_timezone_set($config['timezone']);

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

        for($i = 0; $i < 4; $i++) {
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
if (!empty($config['doslimit'])) {
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
        if (counter_string(STORAGE.'/antidos/'.App::getClientIp().'.dat') > $config['doslimit']) {

            if (!empty($config['errorlog'])){
                $banip = DB::run() -> querySingle("SELECT `id` FROM `ban` WHERE `ip`=? LIMIT 1;", [App::getClientIp()]);
                if (empty($banip)) {

                    DBM::run()->insert('error', [
                        'num' => 666,
                        'request' => utf_substr(App::server('REQUEST_URI'), 0, 200),
                        'referer' => utf_substr(App::server('HTTP_REFERER'), 0, 200),
                        'username' => App::getUsername(),
                        'ip' => App::getClientIp(),
                        'brow' => App::getUserAgent(),
                        'time' => SITETIME,
                    ]);

                    DB::run() -> query("INSERT IGNORE INTO ban (`ip`, `time`) VALUES (?, ?);", [App::getClientIp(), SITETIME]);
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

                $_SESSION['ip'] = App::getClientIp();
                $_SESSION['login'] = $unlog;
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
$log = '';
if (empty($_SESSION['protect'])) {
    $_SESSION['protect'] = rand(1000, 9999);
}
if (empty($_SESSION['counton'])) {
    $_SESSION['counton'] = 0;
}
if (!isset($_SESSION['token'])) {
    if (!empty($config['session'])){
        $_SESSION['token'] = generate_password(6);
    } else {
        $_SESSION['token'] = 0;
    }
}
ob_start('ob_processing');

/**
 * Операции с пользователями
 */
if ($udata = is_user()) {

    Registry::set('user', $udata);

    $log = $udata['login'];
    $config['themes'] = $udata['themes'];

    // Забанен
    if ($udata['ban']) {
        if (!strsearch(App::server('PHP_SELF'), ['/ban', '/rules', '/logout'])) {
            redirect('/ban?log='.$log);
        }
    }

    // Подтверждение регистрации
    if ($config['regkeys'] > 0 && $udata['confirmreg'] > 0 && empty($udata['ban'])) {
        if (!strsearch(App::server('PHP_SELF'), ['/key', '/login', '/logout'])) {
            redirect('/key?log='.$log);
        }
    }

    // Просрочен кредит
    if ($udata['sumcredit'] > 0 && SITETIME > $udata['timecredit'] && empty($udata['ban'])) {
        if (!strstr(App::server('PHP_SELF'), '/games/credit')) {
            redirect('/games/credit?log='.$log);
        }
    }

    // ---------------------- Получение ежедневного бонуса -----------------------//
    if (isset($udata['timebonus']) && $udata['timebonus'] < time() - 82800) {  // Получение бонуса каждые 23 часа
        DB::run() -> query("UPDATE `users` SET `timebonus`=?, `money`=`money`+? WHERE `login`=? LIMIT 1;", [SITETIME, $config['bonusmoney'], $log]);
        notice('Получен ежедневный бонус '.moneys($config['bonusmoney']).'!');
    }

    // ------------------ Запись текущей страницы для админов --------------------//
    if (strstr(App::server('PHP_SELF'), '/admin')) {
        DB::run() -> query("INSERT INTO `admlog` (`user`, `request`, `referer`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?, ?, ?);", [$log, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getClientIp(), App::getUserAgent(), SITETIME]);

        DB::run() -> query("DELETE FROM `admlog` WHERE `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `admlog` ORDER BY `time` DESC LIMIT 500) AS del);");
    }
}

// Сайт закрыт для всех
if ($config['closedsite'] == 2 && !is_admin() && !strsearch(App::server('PHP_SELF'), ['/closed', '/login'])) {
    redirect('/closed');
}

// Сайт закрыт для гостей
if ($config['closedsite'] == 1 && !is_user() && !strsearch(App::server('PHP_SELF'), ['/login', '/register', '/lostpassword', '/captcha'])) {
    notice('Для входа на сайт необходимо авторизоваться!');
    redirect('/login');
}

/**
 * Автоопределение системы
 */
$browser_detect = new Mobile_Detect();

if (!is_user() || empty($config['themes'])) {
    if (!empty($config['touchthemes'])) {
        if ($browser_detect->isTablet()) {
            $config['themes'] = $config['touchthemes'];
        }
    }

    if (!empty($config['webthemes'])) {
        if (!$browser_detect->isMobile() && !$browser_detect->isTablet()) {
            $config['themes'] = $config['webthemes'];
        }
    }
}

if (empty($config['themes']) || !file_exists(HOME.'/themes/'.$config['themes'])) {
    $config['themes'] = 'default';
}

$files = glob(APP.'/functions/*.php');
foreach ($files as $file) {
    require_once $file;
}

Registry::set('config', $config);
