<?php

// --------------------------- Функция перевода секунд во время -----------------------------//
function maketime($time) {
    if ($time < 3600) {
        $time = sprintf("%02d:%02d", (int)($time / 60) % 60, $time % 60);
    } else {
        $time = sprintf("%02d:%02d:%02d", (int)($time / 3600) % 24, (int)($time / 60) % 60, $time % 60);
    }
    return $time;
}

// --------------------------- Функция временного сдвига -----------------------------//
function date_fixed($timestamp, $format = "d.m.y / H:i") {
    global $udata;

    if (!is_numeric($timestamp)) {
        $timestamp = SITETIME;
    }
    $shift = $udata['timezone'] * 3600;
    $datestamp = date($format, $timestamp + $shift);

    $today = date("d.m.y", SITETIME + $shift);
    $yesterday = date("d.m.y", strtotime("-1 day", SITETIME + $shift));

    $datestamp = str_replace($today, 'Сегодня', $datestamp);
    $datestamp = str_replace($yesterday, 'Вчера', $datestamp);

    $search = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $replace = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
    $datestamp = str_replace($search, $replace, $datestamp);

    return $datestamp;
}

// --------------- Функция удаление картинки с проверкой -------------------//
function unlink_image($dir, $image) {
    if (!empty($image)) {
        $prename = str_replace('/', '_' ,$dir.$image);

        if (file_exists(HOME.'/'.$dir.$image)) {
            unlink(HOME.'/'.$dir.$image);
        }

        if (file_exists(HOME.'/uploads/thumbnail/'.$prename)) {
            unlink(HOME.'/uploads/thumbnail/'.$prename);
        }
    }
}

// ------------------- Функция полного удаления юзера --------------------//
function delete_users($user) {
    if (!empty($user)){
        $userpic = User::where('login', $user)->first();

        unlink_image('uploads/photos/', $userpic['picture']);
        unlink_image('uploads/avatars/', $userpic['avatar']);

        DB::run() -> query("DELETE FROM `inbox` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `outbox` WHERE `author`=?;", [$user]);
        DB::run() -> query("DELETE FROM `trash` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `contact` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM ignoring WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `rating` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `visit` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `wall` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `notebook` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `banhist` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `note` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `bookmarks` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `login` WHERE `user`=?;", [$user]);
        DB::run() -> query("DELETE FROM `invite` WHERE `user`=? OR `invited`=?;", [$user, $user]);
        DB::run() -> query("DELETE FROM `users` WHERE `login`=?;", [$user]);
    }
}

// ------------------- Функция удаления фотоальбома юзера --------------------//
function delete_album($user) {

    if (!empty($user)){
        $querydel = DB::run() -> query("SELECT `id`, `link` FROM `photo` WHERE `user`=?;", [$user]);
        $arr_photo = $querydel -> fetchAll();

        if (count($arr_photo) > 0) {
            foreach ($arr_photo as $delete) {
                DB::run() -> query("DELETE FROM `photo` WHERE `id`=?;", [$delete['id']]);
                DB::run() -> query("DELETE FROM `commphoto` WHERE `gid`=?;", [$delete['id']]);

                unlink_image('uploads/pictures/', $delete['link']);
            }
        }
    }
}

// --------------- Функция правильного окончания для денег -------------------//
function moneys($sum) {
    global $config;

    $sum = (int)$sum;
    $money = explode(',', $config['moneyname']);
    if (count($money) == 3) {
        $str1 = abs($sum) % 100;
        $str2 = $sum % 10;

        if ($str1 > 10 && $str1 < 20) return $sum.' '.$money[0];
        if ($str2 > 1 && $str2 < 5) return $sum.' '.$money[1];
        if ($str2 == 1) return $sum.' '.$money[2];
    }

    return $sum.' '.$money[0];
}

// --------------- Функция правильного окончания для актива -------------------//
function points($sum) {
    global $config;

    $sum = (int)$sum;
    $score = explode(',', $config['scorename']);
    if (count($score) == 3) {
        $str1 = abs($sum) % 100;
        $str2 = $sum % 10;

        if ($str1 > 10 && $str1 < 20) return $sum.' '.$score[0];
        if ($str2 > 1 && $str2 < 5) return $sum.' '.$score[1];
        if ($str2 == 1) return $sum.' '.$score[2];
    }

    return $sum.' '.$score[0];
}

// ------------------ Функция перекодировки из UTF в WIN --------------------//
function utf_to_win($str) {
    if (function_exists('mb_convert_encoding')) return mb_convert_encoding($str, 'windows-1251', 'utf-8');
    if (function_exists('iconv')) return iconv('utf-8', 'windows-1251', $str);

    $utf8win1251 = ["А" => "\xC0", "Б" => "\xC1", "В" => "\xC2", "Г" => "\xC3", "Д" => "\xC4", "Е" => "\xC5", "Ё" => "\xA8", "Ж" => "\xC6", "З" => "\xC7", "И" => "\xC8", "Й" => "\xC9", "К" => "\xCA", "Л" => "\xCB", "М" => "\xCC",
        "Н" => "\xCD", "О" => "\xCE", "П" => "\xCF", "Р" => "\xD0", "С" => "\xD1", "Т" => "\xD2", "У" => "\xD3", "Ф" => "\xD4", "Х" => "\xD5", "Ц" => "\xD6", "Ч" => "\xD7", "Ш" => "\xD8", "Щ" => "\xD9", "Ъ" => "\xDA",
        "Ы" => "\xDB", "Ь" => "\xDC", "Э" => "\xDD", "Ю" => "\xDE", "Я" => "\xDF", "а" => "\xE0", "б" => "\xE1", "в" => "\xE2", "г" => "\xE3", "д" => "\xE4", "е" => "\xE5", "ё" => "\xB8", "ж" => "\xE6", "з" => "\xE7",
        "и" => "\xE8", "й" => "\xE9", "к" => "\xEA", "л" => "\xEB", "м" => "\xEC", "н" => "\xED", "о" => "\xEE", "п" => "\xEF", "р" => "\xF0", "с" => "\xF1", "т" => "\xF2", "у" => "\xF3", "ф" => "\xF4", "х" => "\xF5",
        "ц" => "\xF6", "ч" => "\xF7", "ш" => "\xF8", "щ" => "\xF9", "ъ" => "\xFA", "ы" => "\xFB", "ь" => "\xFC", "э" => "\xFD", "ю" => "\xFE", "я" => "\xFF"];

    return strtr($str, $utf8win1251);
}

// ------------------ Функция перекодировки из WIN в UTF --------------------//
function win_to_utf($str) {
    if (function_exists('mb_convert_encoding')) return mb_convert_encoding($str, 'utf-8', 'windows-1251');
    if (function_exists('iconv')) return iconv('windows-1251', 'utf-8', $str);

    $win1251utf8 = ["\xC0" => "А", "\xC1" => "Б", "\xC2" => "В", "\xC3" => "Г", "\xC4" => "Д", "\xC5" => "Е", "\xA8" => "Ё", "\xC6" => "Ж", "\xC7" => "З", "\xC8" => "И", "\xC9" => "Й", "\xCA" => "К", "\xCB" => "Л", "\xCC" => "М",
        "\xCD" => "Н", "\xCE" => "О", "\xCF" => "П", "\xD0" => "Р", "\xD1" => "С", "\xD2" => "Т", "\xD3" => "У", "\xD4" => "Ф", "\xD5" => "Х", "\xD6" => "Ц", "\xD7" => "Ч", "\xD8" => "Ш", "\xD9" => "Щ", "\xDA" => "Ъ",
        "\xDB" => "Ы", "\xDC" => "Ь", "\xDD" => "Э", "\xDE" => "Ю", "\xDF" => "Я", "\xE0" => "а", "\xE1" => "б", "\xE2" => "в", "\xE3" => "г", "\xE4" => "д", "\xE5" => "е", "\xB8" => "ё", "\xE6" => "ж", "\xE7" => "з",
        "\xE8" => "и", "\xE9" => "й", "\xEA" => "к", "\xEB" => "л", "\xEC" => "м", "\xED" => "н", "\xEE" => "о", "\xEF" => "п", "\xF0" => "р", "\xF1" => "с", "\xF2" => "т", "\xF3" => "у", "\xF4" => "ф", "\xF5" => "х",
        "\xF6" => "ц", "\xF7" => "ч", "\xF8" => "ш", "\xF9" => "щ", "\xFA" => "ъ", "\xFB" => "ы", "\xFC" => "ь", "\xFD" => "э", "\xFE" => "ю", "\xFF" => "я"];

    return strtr($str, $win1251utf8);
}

// ------------------ Функция преобразования в нижний регистр для UTF ------------------//
function utf_lower($str) {
    if (function_exists('mb_strtolower')) return mb_strtolower($str, 'utf-8');

    $arraytolower = ['А' => 'а', 'Б' => 'б', 'В' => 'в', 'Г' => 'г', 'Д' => 'д', 'Е' => 'е', 'Ё' => 'ё', 'Ж' => 'ж', 'З' => 'з', 'И' => 'и', 'Й' => 'й', 'К' => 'к', 'Л' => 'л', 'М' => 'м', 'Н' => 'н', 'О' => 'о', 'П' => 'п', 'Р' => 'р', 'С' => 'с', 'Т' => 'т', 'У' => 'у', 'Ф' => 'ф', 'Х' => 'х', 'Ц' => 'ц', 'Ч' => 'ч', 'Ш' => 'ш', 'Щ' => 'щ', 'Ь' => 'ь', 'Ъ' => 'ъ', 'Ы' => 'ы', 'Э' => 'э', 'Ю' => 'ю', 'Я' => 'я',
        'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'I' => 'i', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'J' => 'j', 'K' => 'k', 'L' => 'l', 'M' => 'm', 'N' => 'n', 'O' => 'o', 'P' => 'p', 'Q' => 'q', 'R' => 'r', 'S' => 's', 'T' => 't', 'U' => 'u', 'V' => 'v', 'W' => 'w', 'X' => 'x', 'Y' => 'y', 'Z' => 'z'];

    return strtr($str, $arraytolower);
}

// ----------------------- Функция экранирования основных знаков --------------------------//
function check($msg) {
    if (is_array($msg)) {
        foreach($msg as $key => $val) {
            $msg[$key] = check($val);
        }
    } else {
        $msg = htmlspecialchars($msg);
        $search = ['|', '\'', '$', '\\', "\0", "\x00", "\x1A", chr(226) . chr(128) . chr(174)];
        $replace = ['&#124;', '&#39;', '&#36;', '&#92;', '', '', '', ''];

        $msg = str_replace($search, $replace, $msg);
        $msg = stripslashes(trim($msg));
    }

    return $msg;
}

// --------------- Функция правильного вывода веса файла -------------------//
function formatsize($file_size) {
    if ($file_size >= 1048576000) {
        $file_size = round(($file_size / 1073741824), 2)." Gb";
    } elseif ($file_size >= 1024000) {
        $file_size = round(($file_size / 1048576), 2)." Mb";
    } elseif ($file_size >= 1000) {
        $file_size = round(($file_size / 1024), 2)." Kb";
    } else {
        $file_size = round($file_size)." byte";
    }
    return $file_size;
}

// --------------- Функция форматированного вывода размера файла -------------------//
function read_file($file) {
    if (file_exists($file) && is_file($file)) {
        return formatsize(filesize($file));
    } else {
        return 0;
    }
}

// --------------- Функция правильного вывода времени -------------------//
function formattime($file_time, $round = 1) {
    if ($file_time >= 86400) {
        $file_time = round((($file_time / 60) / 60) / 24, $round).' дн.';
    } elseif ($file_time >= 3600) {
        $file_time = round(($file_time / 60) / 60, $round).' час.';
    } elseif ($file_time >= 60) {
        $file_time = round($file_time / 60).' мин.';
    } else {
        $file_time = round($file_time).' сек.';
    }
    return $file_time;
}

// ------------------ Функция антимата --------------------//
function antimat($str) {
    $querymat = DB::run() -> query("SELECT `string` FROM `antimat` ORDER BY CHAR_LENGTH(`string`) DESC;");
    $arrmat = $querymat -> fetchAll(PDO::FETCH_COLUMN);

    if (count($arrmat) > 0) {
        foreach($arrmat as $val) {
            $str = preg_replace('|'.preg_quote($val).'|iu', '***', $str);
        }
    }

    return $str;
}

// ------------------ Функция должности юзера --------------------//
function user_status($level) {
    global $config;

    $name = explode(',', $config['statusname']);

    switch ($level) {
        case '101': $status = $name[0];
            break;
        case '102': $status = $name[1];
            break;
        case '103': $status = $name[2];
            break;
        case '105': $status = $name[3];
            break;
        default: $status = $name[4];
    }

    return $status;
}

// ---------------- Функция кэширования статусов ------------------//
function save_title($time = 0) {
    if (empty($time) || @filemtime(STORAGE.'/temp/status.dat') < time() - $time) {

    $users = User::select('users.id', 'users.status', 'status.name', 'status.color')
        ->leftJoin('status', 'users.point', 'between', Capsule::raw('status.topoint and status.point'))
        ->where('users.point', '>', 0)
        ->get();

        $statuses = [];
        foreach ($users as $user) {
            if (! empty($user['status'])) {
                $statuses[$user['id']] = '<span style="color:#ff0000">'.$user['status'].'</span>';
                continue;
            }

            if (! empty($user['color'])) {
                $statuses[$user['id']] = '<span style="color:'.$user['color'].'">'.$user['name'].'</span>';
                continue;
            }

            $statuses[$user['id']] = $user['name'];
        }

        file_put_contents(STORAGE.'/temp/status.dat', serialize($statuses), LOCK_EX);
    }
}

function user_title($user) {

    global $config;
    static $status;

    if (! $user) {
        return $config['statusdef'];
    }

    if (is_null($status)) {
        save_title(3600);
        $status = unserialize(file_get_contents(STORAGE.'/temp/status.dat'));
    }
    return isset($status[$user->id]) ? $status[$user->id] : $config['statusdef'];
}
// ------------- Функция вывода статусов пользователей -----------//

// --------------- Функция кэширования настроек -------------------//
function save_setting() {
    $queryset = DB::run() -> query("SELECT `name`, `value` FROM `setting`;");
    $config = $queryset -> fetchAssoc();
    file_put_contents(STORAGE."/temp/setting.dat", serialize($config), LOCK_EX);
}

// --------------- Функция кэширования забаненных IP -------------------//
function save_ipban() {
    $querybanip = DB::run() -> query("SELECT `ip` FROM `ban`;");
    $arrbanip = $querybanip -> fetchAll(PDO::FETCH_COLUMN);
    file_put_contents(STORAGE."/temp/ipban.dat", serialize($arrbanip), LOCK_EX);
    return $arrbanip;
}

// ------------------------- Функция времени антифлуда ------------------------------//
function flood_period() {
    global $config, $udata;

    $period = $config['floodstime'];

    if ($udata['point'] < 50) {
        $period = round($config['floodstime'] * 2);
    }
    if ($udata['point'] >= 500) {
        $period = round($config['floodstime'] / 2);
    }
    if ($udata['point'] >= 1000) {
        $period = round($config['floodstime'] / 3);
    }
    if ($udata['point'] >= 5000) {
        $period = round($config['floodstime'] / 6);
    }
    if (is_admin()) {
        $period = 0;
    }

    return $period;
}

// ------------------------- Функция антифлуда ------------------------------//
function is_flood($log, $period = 0) {

    $period = empty($period) ? flood_period() : $period;

    if (empty($period)) return true;

    DB::run() -> query("DELETE FROM `flood` WHERE `time`<?;", [SITETIME]);

    $queryflood = DB::run() -> querySingle("SELECT `id` FROM `flood` WHERE `user`=? AND `page`=? LIMIT 1;", [$log, App::server('PHP_SELF')]);

    if (empty($queryflood)) {
        DB::run() -> query("INSERT INTO `flood` (`user`, `page`, `time`) VALUES (?, ?, ?);", [$log, App::server('PHP_SELF'), SITETIME + $period]);

        return true;
    }

    return false;
}

// ------------------ Функция вывода рейтинга --------------------//
function rating_vote($rating) {

    $rating = round($rating / 0.5) * 0.5;

    $full_stars = floor($rating);
    $half_stars = ceil($rating - $full_stars);
    $empty_stars = 5 - $full_stars - $half_stars;

    $output = '<div class="star-rating fa-lg text-danger">';
    $output .= str_repeat('<i class="fa fa-star"></i>', $full_stars);
    $output .= str_repeat('<i class="fa fa-star-half-o"></i>', $half_stars);
    $output .= str_repeat('<i class="fa fa-star-o"></i>', $empty_stars);
    $output .= '</div>';

    return $output;
}

// --------------- Функция листинга всех файлов и папок ---------------//
function scan_check($dirname) {
    global $arr, $config;

    if (empty($arr['files'])) {
        $arr['files'] = [];
    }
    if (empty($arr['totalfiles'])) {
        $arr['totalfiles'] = 0;
    }
    if (empty($arr['totaldirs'])) {
        $arr['totaldirs'] = 0;
    }

    $files = preg_grep('/^([^.])/', scandir($dirname));

    foreach ($files as $file) {
        if (is_file($dirname.'/'.$file)) {
            $ext = getExtension($file);

            if (!in_array($ext, explode(',',App::setting('nocheck')) )) {
                $arr['files'][] = $dirname.'/'.$file.' - '.date_fixed(filemtime($dirname.'/'.$file), 'j.m.Y / H:i').' - '.read_file($dirname.'/'.$file);
                $arr['totalfiles']++;
            }
        }

        if (is_dir($dirname.'/'.$file)) {
            $arr['files'][] = $dirname.'/'.$file;
            $arr['totaldirs']++;
            scan_check($dirname.'/'.$file);
        }
    }

    return $arr;
}

// --------------- Функция вывода календаря---------------//
function make_calendar($month, $year) {
    $wday = date("w", mktime(0, 0, 0, $month, 1, $year));
    if ($wday == 0) {
        $wday = 7;
    }
    $n = - ($wday-2);
    $cal = [];
    for ($y = 0; $y < 6; $y++) {
        $row = [];
        $notEmpty = false;
        for ($x = 0; $x < 7; $x++, $n++) {
            if (checkdate($month, $n, $year)) {
                $row[] = $n;
                $notEmpty = true;
            } else {
                $row[] = "";
            }
        }
        if (!$notEmpty) break;
        $cal[] = $row;
    }
    return $cal;
}

// --------------- Функция сохранения количества денег  у юзера ---------------//
function save_money($time = 0) {
    if (empty($time) || @filemtime(STORAGE."/temp/money.dat") < time() - $time) {
        $queryuser = DB::run() -> query("SELECT `login`, `money` FROM `users` WHERE `money`>?;", [0]);
        $alluser = $queryuser -> fetchAssoc();
        file_put_contents(STORAGE."/temp/money.dat", serialize($alluser), LOCK_EX);
    }
}

// --------------- Функция подсчета денег у юзера ---------------//
function user_money($login) {
    static $arrmoney;

    if (empty($arrmoney)) {
        save_money(3600);
        $arrmoney = unserialize(file_get_contents(STORAGE."/temp/money.dat"));
    }

    return (isset($arrmoney[$login])) ? $arrmoney[$login] : 0;
}

// --------------- Функция сохранения количества писем ---------------//
function save_usermail($time = 0) {
    if (empty($time) || @filemtime(STORAGE."/temp/usermail.dat") < time() - $time) {
        $querymail = DB::run() -> query("SELECT `user`, COUNT(*) FROM `inbox` GROUP BY `user`;");
        $arrmail = $querymail -> fetchAssoc();
        file_put_contents(STORAGE."/temp/usermail.dat", serialize($arrmail), LOCK_EX);
    }
}

// --------------- Функция подсчета писем у юзера ---------------//
function user_mail($login) {
    save_usermail(3600);
    $arrmail = unserialize(file_get_contents(STORAGE."/temp/usermail.dat"));
    return (isset($arrmail[$login])) ? $arrmail[$login] : 0;
}

// --------------- Функция кэширования аватаров -------------------//
function save_avatar($time = 0) {
    if (empty($time) || @filemtime(STORAGE."/temp/avatars.dat") < time() - $time) {

        $avatars = User::select('id', 'avatar')
            ->where('avatar', '<>', '')
            ->pluck('avatar', 'id')
            ->all();

        file_put_contents(STORAGE."/temp/avatars.dat", serialize($avatars), LOCK_EX);
    }
}

// --------------- Функция вывода аватара пользователя ---------------//
function user_avatars($user) {
    static $avatars;

    if (! $user) {
        return '<img src="/assets/img/images/avatar_guest.png" alt="" /> ';
    }

    if (is_null($avatars)) {
        save_avatar(3600);
        $avatars = unserialize(file_get_contents(STORAGE."/temp/avatars.dat"));
    }

    if (isset($avatars[$user->id]) && file_exists(HOME.'/uploads/avatars/'.$avatars[$user->id])) {
        return '<a href="/user/'.$user->login.'"><img src="/uploads/avatars/'.$avatars[$user->id].'" alt="" /></a> ';
    }

    return '<a href="/user/'.$user->login.'"><img src="/assets/img/images/avatar_default.png" alt="" /></a> ';
}


// --------------- Функция вывода аватара пользователя ---------------//
function userAvatar($user)
{
    if (! $user) {
        return '<img src="/assets/img/images/avatar_guest.png" alt="" /> ';
    }

    if ($user->avatar && file_exists(HOME.'/uploads/avatars/'.$user->avatar)) {
        return '<a href="/user/'.$user->login.'"><img src="/uploads/avatars/'.$user->avatar.'" alt="" /></a> ';
    }

    return '<a href="/user/'.$user->login.'"><img src="/assets/img/images/avatar_default.png" alt="" /></a> ';
}


// --------------- Функция подсчета человек в контакт-листе ---------------//
function user_contact($login) {
    return DB::run() -> querySingle("SELECT count(*) FROM `contact` WHERE `user`=?;", [$login]);
}

// --------------- Функция подсчета человек в игнор-листе ---------------//
function user_ignore($login) {
    return DB::run() -> querySingle("SELECT count(*) FROM ignoring WHERE `user`=?;", [$login]);
}

// --------------- Функция подсчета записей на стене ---------------//
function user_wall($login) {
    return DB::run() -> querySingle("SELECT count(*) FROM `wall` WHERE `user`=?;", [$login]);
}

// ------------------ Функция подсчета пользователей онлайн -----------------//
function stats_online($cache = 30) {
    if (@filemtime(STORAGE."/temp/online.dat") < time()-$cache) {

        $online[0] = Online::whereNotNull('user_id')->count();
        $online[1] = Online::count();

        include_once(APP.'/includes/count.php');

        file_put_contents(STORAGE."/temp/online.dat", serialize($online), LOCK_EX);
    }

    return unserialize(file_get_contents(STORAGE."/temp/online.dat"));
}

// ------------------ Функция вывода пользователей онлайн -----------------//
function show_online() {
    global $config;

    if ($config['onlines'] == 1) {
        $online = stats_online();
        App::view('includes/online', compact('online'));
    }
}

// ------------------ Функция подсчета посещений -----------------//
function stats_counter() {
    if (@filemtime(STORAGE."/temp/counter.dat") < time()-10) {
        $counts = Counter::count();
        file_put_contents(STORAGE."/temp/counter.dat", serialize($counts), LOCK_EX);
    }

    return unserialize(file_get_contents(STORAGE."/temp/counter.dat"));
}

// ------------------ Функция вывода счетчика посещений -----------------//
function show_counter()
{
    global $config;

    /*
     * @TODO Временно, убрать после вывода в шаблоны
     */
    if (isset($_SESSION['note'])) {
        unset($_SESSION['note']);

    }

    if (is_user()) {

        $visitPage = !empty($config['newtitle']) ? $config['newtitle'] : null;

        Visit::updateOrCreate(
            ['user_id' => App::getUserId()],
            [
                'self'       => App::server('PHP_SELF'),
                'page'       => $visitPage,
                'ip'         => App::getClientIp(),
                'count'      => Capsule::raw('count + 1'),
                'updated_at' => SITETIME,
            ]
        );
    }

    include_once (APP."/includes/counters.php");

    if ($config['incount'] > 0) {
        $count = stats_counter();

        App::view('includes/counter', compact('count'));
    }
}

// --------------- Функция вывода количества зарегистрированных ---------------//
function stats_users() {
    if (@filemtime(STORAGE."/temp/statusers.dat") < time()-3600) {
        $total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");
        $new = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `joined`>UNIX_TIMESTAMP(CURDATE());");

        if (empty($new)) {
            $stat = $total;
        } else {
            $stat = $total.'/+'.$new;
        }

        file_put_contents(STORAGE."/temp/statusers.dat", $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statusers.dat");
}

// --------------- Функция вывода количества админов и модеров --------------------//
function stats_admins() {
    if (@filemtime(STORAGE."/temp/statadmins.dat") < time()-3600) {
        $stat = DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `level`>=? AND `level`<=?;", [101, 105]);

        file_put_contents(STORAGE."/temp/statadmins.dat", $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statadmins.dat");
}

// --------------- Функция вывода количества жалоб --------------------//
function stats_spam() {
    return DB::run() -> querySingle("SELECT count(*) FROM `spam`;");
}
// --------------- Функция вывода количества забаненных --------------------//
function stats_banned() {
    return DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `ban`=? AND `timeban`>?;", [1, SITETIME]);
}

// --------------- Функция вывода истории банов --------------------//
function stats_banhist() {
    return DB::run() -> querySingle("SELECT count(*) FROM `banhist`;");
}

// ------------ Функция вывода количества ожидающих регистрации -----------//
function stats_reglist() {
    return DB::run() -> querySingle("SELECT count(*) FROM `users` WHERE `confirmreg`>?;", [0]);
}

// --------------- Функция вывода количества забаненных IP --------------------//
function stats_ipbanned() {
    return DB::run() -> querySingle("SELECT count(*) FROM `ban`;");
}

// --------------- Функция вывода количества фотографий --------------------//
function stats_gallery() {
    if (@filemtime(STORAGE."/temp/statgallery.dat") < time()-900) {
        $total = Photo::count();
        $totalnew = Photo::where('created_at', '>', SITETIME-86400 * 3)->count();

        if (empty($totalnew)) {
            $stat = $total;
        } else {
            $stat = $total.'/+'.$totalnew;
        }

        file_put_contents(STORAGE."/temp/statgallery.dat", $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statgallery.dat");
}

// --------------- Функция вывода количества новостей--------------------//
function stats_allnews() {
    return DB::run() -> querySingle("SELECT count(*) FROM `news`;");
}

// ---------- Функция вывода записей в черном списке ------------//
function stats_blacklist() {
    $query = DB::run() -> query("SELECT `type`, count(*) FROM `blacklist` GROUP BY `type`;");
    $blacklist = $query -> fetchAssoc();
    $list = $blacklist + array_fill(1, 3, 0);
    return $list[1].'/'.$list[2].'/'.$list[3];
}

// --------------- Функция вывода количества заголовков ----------------//
function stats_antimat() {
    return DB::run() -> querySingle("SELECT count(*) FROM `antimat`;");
}

// --------------- Функция вывода количества смайлов ----------------//
function stats_smiles() {
    return DB::run() -> querySingle("SELECT count(*) FROM `smiles`;");
}

// ----------- Функция вывода даты последнего сканирования -------------//
function stats_checker() {
    if (file_exists(STORAGE."/temp/checker.dat")) {
        return date_fixed(filemtime(STORAGE."/temp/checker.dat"), "j.m.y");
    } else {
        return 0;
    }
}

// --------------- Функция вывода количества приглашений --------------//
function stats_invite() {
    $invite = DB::run() -> querySingle("SELECT count(*) FROM `invite` WHERE `used`=?;", [0]);
    $used_invite = DB::run() -> querySingle("SELECT count(*) FROM `invite` WHERE `used`=?;", [1]);
    return $invite.'/'.$used_invite;
}

// --------------- Функция определение онлайн-статуса ---------------//
function user_online($user) {
    static $visits;

    $online = '<i class="fa fa-asterisk text-danger"></i>';

    if (is_null($visits)) {
        if (@filemtime(STORAGE."/temp/visit.dat") < time() - 10) {

            $visits = Visit::select('user_id')
                ->where('updated_at', '>', SITETIME - 600)
                ->pluck('user_id', 'user_id')
                ->all();

            file_put_contents(STORAGE."/temp/visit.dat", serialize($visits), LOCK_EX);
        }

        $visits = unserialize(file_get_contents(STORAGE."/temp/visit.dat"));
    }

    if ($user && isset($visits[$user->id])) {
        $online = '<i class="fa fa-asterisk fa-spin text-success"></i>';
    }

    return $online;
}

// --------------- Функция определение пола пользователя ---------------//
function user_gender($user) {
    static $genders;

    $gender = 'male';

    if (is_null($genders)) {
        if (@filemtime(STORAGE."/temp/gender.dat") < time() - 600) {

            $genders = User::select('id')
                ->where('gender', 2)
                ->pluck('id', 'id')
                ->all();

            file_put_contents(STORAGE."/temp/gender.dat", serialize($genders), LOCK_EX);
        }
        $genders = unserialize(file_get_contents(STORAGE."/temp/gender.dat"));
    }

    if ($user && isset($genders[$user->id])){
        $gender = 'female';
    }

    return '<i class="fa fa-'.$gender.' fa-lg"></i>';
}

// --------------- Функция вывода пользователей онлайн ---------------//
function allonline() {
    if (@filemtime(STORAGE."/temp/allonline.dat") < time()-30) {
        $visits = Visit::select('user_id')
            ->where('updated_at', '>', SITETIME - 600)
            ->orderBy('updated_at', 'desc')
            ->pluck('user_id')
            ->all();

        file_put_contents(STORAGE."/temp/allonline.dat", serialize($visits), LOCK_EX);
    }

    return unserialize(file_get_contents(STORAGE."/temp/allonline.dat"));
}

// ------------------ Функция определение последнего посещения ----------------//
function user_visit($user) {
    $state = '(Оффлайн)';

    $visit = Visit::select('updated_at')->where('user_id', $user->id)->first();

    if ($visit) {
        if ($visit['updated_at'] > SITETIME - 600) {
            $state = '(Сейчас на сайте)';
        } else {
            $state = '(Последний визит: '.date_fixed($visit['updated_at']).')';
        }
    }

    return $state;
}

// ---------- Функция обработки строк данных и ссылок ---------//
function check_string($string) {
    $string = strtolower($string);
    $string = str_replace(['http://www.', 'http://wap.', 'http://', 'https://'], '', $string);
    $string = strtok($string, '/?');
    return $string;
}

// ---------- Аналог функции substr для UTF-8 ---------//
function utf_substr($str, $offset, $length = null) {
    if ($length === null) {
        $length = utf_strlen($str);
    }
    if (function_exists('mb_substr')) return mb_substr($str, $offset, $length, 'utf-8');
    if (function_exists('iconv_substr')) return iconv_substr($str, $offset, $length, 'utf-8');

    $str = utf_to_win($str);
    $str = substr($str, $offset, $length);
    return win_to_utf($str);
}

// ---------------------- Аналог функции strlen для UTF-8 -----------------------//
function utf_strlen($str) {
    if (function_exists('mb_strlen')) return mb_strlen($str, 'utf-8');
    if (function_exists('iconv_strlen')) return iconv_strlen($str, 'utf-8');
    if (function_exists('utf8_decode')) return strlen(utf8_decode($str));
    return strlen(utf_to_win($str));
}

// ----------------------- Функция определения кодировки ------------------------//
function is_utf($str) {
    $len = strlen($str);
    for($i = 0; $i < $len; $i++) {
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c >= 254)) return false;
            elseif ($c >= 252) $bits = 6;
            elseif ($c >= 248) $bits = 5;
            elseif ($c >= 240) $bits = 4;
            elseif ($c >= 224) $bits = 3;
            elseif ($c >= 192) $bits = 2;
            else return false;
            if (($i + $bits) > $len) return false;
            while ($bits > 1) {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) return false;
                $bits--;
            }
        }
    }
    return true;
}

/**
 * Отправка уведомления на email
 * @param  mixed   $to      Получатель
 * @param  string  $subject Тема письма
 * @param  string  $body    Текст сообщения
 * @param  array   $params Дополнительные параметры
 * @return boolean  Результат отправки
 */
function sendMail($to, $subject, $body, $params = []) {
    global $config;

    if (empty($params['from'])) {
        $config['mailusername'] = !empty($config['mailusername']) ? $config['mailusername'] : $config['emails'];
        $params['from'] = [$config['mailusername'] => $config['nickname']];
    }

    $message = Swift_Message::newInstance()
        ->setTo($to)
        ->setSubject($subject)
        ->setBody($body, 'text/html')
        ->setFrom($params['from'])
        ->setReturnPath($config['mailusername']);

    if (isset($params['unsubkey'])) {
        $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$config['mailusername'].'>, <'.$config['home'].'/unsubscribe?key='.$params['unsubkey'].'>');
        $message->setBody($body.'<br /><br />Если вы не хотите получать эти эл. письма, пожалуйста, <a href="'.$config['home'].'/unsubscribe?key='.$params['unsubkey'].'">откажитесь от подписки</a>', 'text/html');
    }

    if ($config['maildriver'] == 'smtp') {
        $mailsecurity = ! empty($config['mailsecurity']) ? $config['mailsecurity'] : null;
        $transport = Swift_SmtpTransport::newInstance($config['mailhost'], $config['mailport'], $mailsecurity)
            ->setUsername($config['mailusername'])
            ->setPassword($config['mailpassword']);
    } else {
        $transport = new Swift_MailTransport();
    }

    $mailer = new Swift_Mailer($transport);
    return $mailer->send($message);
}

// --------------------- Функция вывода навигации в галерее ------------------------//
function photo_navigation($id) {

    if (empty($id)) {
        return false;
    }

    $next_id = DB::run() -> querySingle("SELECT `id` FROM `photo` WHERE `id`>? ORDER BY `id` ASC LIMIT 1;", [$id]);
    $prev_id = DB::run() -> querySingle("SELECT `id` FROM `photo` WHERE `id`<? ORDER BY `id` DESC LIMIT 1;", [$id]);
    return ['next' => $next_id, 'prev' => $prev_id];

}

// --------------------- Функция вывода статистики блогов ------------------------//
function stats_blog() {
    if (@filemtime(STORAGE."/temp/statblog.dat") < time()-900) {
        $totalblog = DB::run() -> querySingle("SELECT SUM(`count`) FROM `catsblog`;");
        $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `blogs` WHERE `time`>?;", [SITETIME-86400 * 3]);

        if (empty($totalnew)) {
            $stat = (int)$totalblog;
        } else {
            $stat = $totalblog.'/+'.$totalnew;
        }

        file_put_contents(STORAGE."/temp/statblog.dat", $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statblog.dat");
}

// --------------------- Функция вывода статистики форума ------------------------//
function stats_forum() {
    if (@filemtime(STORAGE."/temp/statforum.dat") < time()-600) {

        $topics = Topic::count();
        $posts = Post::count();

        file_put_contents(STORAGE."/temp/statforum.dat", $topics.'/'.$posts, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statforum.dat");
}

// --------------------- Функция вывода статистики гостевой ------------------------//
function stats_guest() {
    if (@filemtime(STORAGE."/temp/statguest.dat") < time()-600) {
        global $config;
        $total = DB::run() -> querySingle("SELECT count(*) FROM `guest`;");

        if ($total > ($config['maxpostbook']-10)) {
            $stat = DB::run() -> querySingle("SELECT MAX(`id`) FROM `guest`;");
        } else {
            $stat = $total;
        }

        file_put_contents(STORAGE."/temp/statguest.dat", (int)$stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statguest.dat");
}

// -------------------- Функция вывода статистики админ-чата -----------------------//
function stats_chat() {
    global $config;

    $total = DB::run() -> querySingle("SELECT count(*) FROM `chat`;");

    if ($total > ($config['chatpost']-10)) {
        $total = DB::run() -> querySingle("SELECT MAX(`id`) FROM `chat`;");
    }

    return $total;
}

// ------------------ Функция вывода времени последнего сообщения --------------------//
function stats_newchat() {
    return intval(DB::run() -> querySingle("SELECT MAX(`time`) FROM `chat`;"));
}

// --------------------- Функция вывода статистики загрузок ------------------------//
function stats_load($cats=0) {
    if (empty($cats)){

        if (@filemtime(STORAGE."/temp/statload.dat") < time()-900) {
            $totalloads = DB::run() -> querySingle("SELECT SUM(`count`) FROM `cats`;");
            $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `time`>?;", [1, SITETIME-86400 * 5]);

            if (empty($totalnew)) {
                $stat = intval($totalloads);
            } else {
                $stat = $totalloads.'/+'.$totalnew;
            }
            file_put_contents(STORAGE."/temp/statload.dat", $stat, LOCK_EX);
        }

        return file_get_contents(STORAGE."/temp/statload.dat");

    } else {

        if (@filemtime(STORAGE."/temp/statloadcats.dat") < time()-900) {

        $querydown = DB::run()->query("SELECT `c`.*, (SELECT SUM(`count`) FROM `cats` WHERE `parent`=`c`.`id`) AS `subcnt`, (SELECT COUNT(*) FROM `downs` WHERE `category_id`=`id` AND `active`=? AND `time` > ?) AS `new` FROM `cats` `c` ORDER BY sort ASC;", [1, SITETIME-86400*5]);
        $downs = $querydown->fetchAll();

            if (!empty($downs)){
                foreach ($downs as $data){
                    $subcnt = (empty($data['subcnt'])) ? '' : '/'.$data['subcnt'];
                    $new = (empty($data['new'])) ? '' : '/<span style="color:#ff0000">+'.$data['new'].'</span>';
                    $stat[$data['id']] = $data['count'].$subcnt.$new;
                }
            }
            file_put_contents(STORAGE."/temp/statloadcats.dat", serialize($stat), LOCK_EX);
        }

        $statcats = unserialize(file_get_contents(STORAGE."/temp/statloadcats.dat"));
        return (isset($statcats[$cats])) ? $statcats[$cats] : 0;
    }
}

// --------------------- Функция подсчета непроверенных файлов ------------------------//
function stats_newload() {
    $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=?;", [0]);
    $totalcheck = DB::run() -> querySingle("SELECT count(*) FROM `downs` WHERE `active`=? AND `app`=?;", [0, 1]);

    if (empty($totalcheck)) {
        return intval($totalnew);
    } else {
        return $totalnew.'/+'.$totalcheck;
    }
}

// --------------------- Функция шифровки Email-адреса ------------------------//
function crypt_mail($mail) {
    $output = "";
    $strlen = strlen($mail);
    for ($i = 0; $i < $strlen; $i++) {
        $output .= '&#'.ord($mail[$i]).';';
    }
    return $output;
}

// ------------------- Функция обработки массива (int) --------------------//
function intar($string) {
    if (empty($string)) return false;

    if (is_array($string)) {
        $newstring = array_map('intval', $string);
    } else {
        $newstring = [abs(intval($string))];
    }

    return $newstring;
}

// ------------------- Функция подсчета голосований --------------------//
function stats_votes() {
    if (@filemtime(STORAGE."/temp/statvote.dat") < time()-900) {
        $data = DB::run() -> queryFetch("SELECT count(*) AS `count`, SUM(`count`) AS `sum` FROM `vote` WHERE `closed`=?;", [0]);

        if (empty($data['sum'])) {
            $data['sum'] = 0;
        }

        file_put_contents(STORAGE."/temp/statvote.dat", $data['count'].'/'.$data['sum'], LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statvote.dat");
}

// ------------------- Функция показа даты последней новости --------------------//
function stats_news() {
    if (@filemtime(STORAGE."/temp/statnews.dat") < time()-900) {
        $stat = 0;

        $data = DB::run() -> queryFetch("SELECT `time` FROM `news` ORDER BY `id` DESC LIMIT 1;");

        if ($data > 0) {
            $stat = date_fixed($data['time'], "d.m.y");
            if ($stat == 'Сегодня') {
                $stat = '<span style="color:#ff0000">Сегодня</span>';
            }
        }

        file_put_contents(STORAGE."/temp/statnews.dat", $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statnews.dat");
}

// --------------------------- Функция вывода новостей -------------------------//
function last_news() {
    global $config;

    if ($config['lastnews'] > 0) {

        $query = DB::run()->query("SELECT * FROM `news` WHERE `top`=? ORDER BY `time` DESC LIMIT ".$config['lastnews'].";", [1]);
        $news = $query->fetchAll();

        $total = count($news);

        if ($total > 0) {
            foreach ($news as $data) {
                $data['text'] = str_replace('[cut]', '', $data['text']);
                echo '<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/news/'.$data['id'].'">'.$data['title'].'</a> ('.$data['comments'].') <i class="fa fa-caret-down news-title"></i><br />';

                echo '<div class="news-text" style="display: none;">'.App::bbCode($data['text']).'<br />';
                echo '<a href="/news/'.$data['id'].'/comments">Комментарии</a> ';
                echo '<a href="/news/'.$data['id'].'/end">&raquo;</a></div>';
            }
        }
    }
}

// --------------------- Функция вывода статистики событий ------------------------//
function stats_events() {
    if (@filemtime(STORAGE."/temp/statevents.dat") < time()-900) {
        $total = DB::run() -> querySingle("SELECT count(*) FROM `events`;");
        $totalnew = DB::run() -> querySingle("SELECT count(*) FROM `events` WHERE `time`>?;", [SITETIME-86400 * 3]);

        if (empty($totalnew)) {
            $stat = (int)$total;
        } else {
            $stat = $total.'/+'.$totalnew;
        }

        file_put_contents(STORAGE."/temp/statevents.dat", (int)$stat, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/statevents.dat");
}

// --------------------- Функция получения данных аккаунта  --------------------//
function user($login) {
    if (! empty($login)) {
        return User::where('login', $login)->first();
    }
    return false;
}

// ------------------------- Функция проверки авторизации  ------------------------//
function is_user() {
    static $user = 0;
    if (empty($user)) {
        if (isset($_SESSION['id']) && isset($_SESSION['password'])) {

            $data = User::find(intval($_SESSION['id']));

            if ($data && $_SESSION['password'] == md5(env('APP_KEY').$data['password'])) {
                $user = $data;
            }
        }
    }

    return $user;
}

// ------------------------- Функция проверки администрации  ------------------------//
function is_admin($access = []) {
    if (empty($access)) {
        $access = [101, 102, 103, 105];
    }

    if (is_user()) {
        global $udata;
        if (in_array($udata['level'], $access)) {
            return true;
        }
    }

    return false;
}

// ------------------------- Функция вывода заголовков ------------------------//
function show_title($header, $subheader = false) {
    global $config;
    static $show;

    $config['newtitle'] = $header;
    $config['header'] = $header;
    $config['subheader'] = $subheader;

    if (empty($show)) {
        echo $show = App::view('includes/title', [], true);
    }

    return $config;
}

// ------------------------- Функция вывода ошибок ------------------------//
function show_error($errors) {
    App::view('includes/error', compact('errors'));
}

// ------------------------- Функция вывода предупреждения ------------------------//
function show_login($notice) {
    App::view('includes/login', compact('notice'));
}

// ------------------------- Функция замены заголовков ------------------------//
/**
 * @deprecated
 * Используется только для совместимости со старыми страницами
 * @param $str
 * @return mixed
 */
function ob_processing($str)
{
    global $config;

    if (isset($config['newtitle'])) {
        $str = str_replace('<title>', '<title>'.$config['newtitle'].' - ', $str);
    }

    //TODO: Удалить после переделки всех шаблонов
    $str = str_replace('%HEADER%',  isset($config['header']) ? $config['header'] : '', $str);
    $str = str_replace('%SUBHEADER%', isset($config['subheader']) ? $config['subheader'] : '', $str);

    return $str;
}

// ------------------ Функция вывода иконки расширения --------------------//
function icons($ext) {
    switch ($ext) {
        case 'php':
            $ico = 'file-code-o';
            break;
        case 'ppt':
            $ico = 'file-powerpoint-o';
            break;
        case 'doc':
        case 'docx':
            $ico = 'file-word-o';
            break;
        case 'xls':
        case 'xlsx':
            $ico = 'file-excel-o';
            break;
        case 'txt':
        case 'css':
        case 'dat':
        case 'html':
        case 'htm':
            $ico = 'file-text-o';
            break;
        case 'wav':
        case 'amr':
        case 'mp3':
        case 'mid':
            $ico = 'file-audio-o';
            break;
        case 'zip':
        case 'rar':
        case '7z':
        case 'gz':
            $ico = 'file-archive-o';
            break;
        case '3gp':
        case 'mp4':
            $ico = 'file-video-o';
            break;
        case 'jpg':
        case 'jpeg':
        case 'bmp':
        case 'wbmp':
        case 'gif':
        case 'png':
            $ico = 'file-image-o';
            break;
        case 'ttf':
            $ico = 'font';
            break;
        case 'pdf':
            $ico = 'file-pdf-o';
            break;
        default: $ico = 'file-o';
    }
    return '<i class="fa fa-'.$ico.'"></i>';
}

// ------------------ Функция смешивания ассоциативного массива --------------------//
function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);
    $new = [];

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;
    return true;
}

// --------------- Функция обрезки слов -------------------//
function strip_str($str, $words = 20) {
    return implode(' ', array_slice(explode(' ', strip_tags($str)), 0, $words));
}

// ------------------ Функция вывода пользовательской рекламы --------------------//
function show_advertuser() {
    global $config;

    if (!empty($config['rekusershow'])) {
        if (@filemtime(STORAGE."/temp/rekuser.dat") < time()-1800) {
            save_advertuser();
        }

        $datafile = unserialize(file_get_contents(STORAGE."/temp/rekuser.dat"));
        $total = count($datafile);

        if ($total > 0) {
            if ($config['rekusershow'] > $total) {
                $config['rekusershow'] = $total;
            }

            $quot_rand = array_rand($datafile, $config['rekusershow']);

            if ($config['rekusershow'] > 1) {
                $result = [];
                for($i = 0; $i < $config['rekusershow']; $i++) {
                    $result[] = $datafile[$quot_rand[$i]];
                }
                $result = implode('<br />', $result);
            } else {
                $result = $datafile[$quot_rand];
            }

            return $result.' <small><a href="/reklama" rel="nofollow">[+]</a></small>';
        }
    }
}

// --------------- Функция кэширования пользовательской рекламы -------------------//
function save_advertuser() {
    $queryrek = DB::run() -> query("SELECT * FROM `rekuser` WHERE `time`>?;", [SITETIME]);
    $data = $queryrek -> fetchAll();

    $arraylink = [];

    if (count($data) > 0) {
        foreach ($data as $val) {
            if (!empty($val['color'])) {
                $val['name'] = '<span style="color:'.$val['color'].'">'.$val['name'].'</span>';
            }
            $link = '<a href="'.$val['site'].'" target="_blank" rel="nofollow">'.$val['name'].'</a>';

            if (!empty($val['bold'])) {
                $link = '<b>'.$link.'</b>';
            }

            $arraylink[] = $link;
        }
    }

    file_put_contents(STORAGE."/temp/rekuser.dat", serialize($arraylink), LOCK_EX);
}

// ----------- Функция закачки файла через curl ------------//
function curl_connect($url, $user_agent = 'Mozilla/5.0', $proxy = null) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_REFERER, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
        if ($proxy) curl_setopt ($ch, CURLOPT_PROXY, $proxy);
        $result = curl_exec ($ch);
        curl_close ($ch);
        return $result;
    }
}

// --------------------------- Функция показа событий---------------------------//
function recentevents($show = 5) {

    if (@filemtime(STORAGE."/temp/recentevents.dat") < time()-600) {
        $query = DB::run()->query("SELECT * FROM `events` WHERE `top`=? ORDER BY `time` DESC LIMIT ".$show.";", [1]);
        $events = $query->fetchAll();

        file_put_contents(STORAGE."/temp/recentevents.dat", serialize($events), LOCK_EX);
    }

    $events = unserialize(file_get_contents(STORAGE."/temp/recentevents.dat"));

    if (is_array($events) && count($events) > 0) {
        foreach ($events as $data) {
            echo '<i class="fa fa-circle-o fa-lg text-muted"></i> ';
            echo '<a href="/events?act=read&amp;id='.$data['id'].'">'.$data['title'].'</a> ('.$data['comments'].')<br />';
        }
    }
}

// --------------------------- Функция показа фотографий ---------------------------//
function recentphotos($show = 5) {
    global $config;
    if (@filemtime(STORAGE."/temp/recentphotos.dat") < time()-1800) {

        $recent = Photo::orderBy('time', 'desc')->limit($show)->get();

        file_put_contents(STORAGE."/temp/recentphotos.dat", serialize($recent), LOCK_EX);
    }

    $photos = unserialize(file_get_contents(STORAGE."/temp/recentphotos.dat"));

    if (is_array($photos) && count($photos) > 0) {
        foreach ($photos as $data) {
            echo '<a href="/gallery?act=view&amp;gid='.$data['id'].'">'.resize_image('uploads/pictures/', $data['link'], $config['previewsize'], ['alt' => $data['title'], 'class' => 'img-rounded', 'style' => 'width: 100px; height: 100px;']).'</a>';
        }

        echo '<br />';
    }
}

// --------------- Функция кэширования последних тем форума -------------------//
function recenttopics($show = 5) {
    if (@filemtime(STORAGE."/temp/recenttopics.dat") < time()-180) {
        $topics = Topic::orderBy('updated_at', 'desc')->limit($show)->get();
        file_put_contents(STORAGE."/temp/recenttopics.dat", serialize($topics), LOCK_EX);
    }

    $topics = unserialize(file_get_contents(STORAGE."/temp/recenttopics.dat"));

    if ($topics) {
        foreach ($topics as $topic) {
            echo '<i class="fa fa-circle-o fa-lg text-muted"></i>  <a href="/topic/'.$topic['id'].'">'.$topic['title'].'</a> ('.$topic->posts.')';
            echo '<a href="/topic/'.$topic['id'].'/end">&raquo;</a><br />';
        }
    }
}

// ------------- Функция кэширования последних файлов в загрузках -----------------//
function recentfiles($show = 5) {
    if (@filemtime(STORAGE."/temp/recentfiles.dat") < time()-600) {
        $queryfiles = DB::run() -> query("SELECT * FROM `downs` WHERE `active`=? ORDER BY `time` DESC LIMIT ".$show.";", [1]);
        $recent = $queryfiles -> fetchAll();

        file_put_contents(STORAGE."/temp/recentfiles.dat", serialize($recent), LOCK_EX);
    }

    $files = unserialize(file_get_contents(STORAGE."/temp/recentfiles.dat"));

    if (is_array($files) && count($files) > 0) {
        foreach ($files as $file){

            $filesize = (!empty($file['link'])) ? read_file(HOME.'/uploads/files/'.$file['link']) : 0;
            echo '<i class="fa fa-circle-o fa-lg text-muted"></i>  <a href="/load/down?act=view&amp;id='.$file['id'].'">'.$file['title'].'</a> ('.$filesize.')<br />';
        }
    }
}

// ------------- Функция кэширования последних статей в блогах -----------------//
function recentblogs() {
    if (@filemtime(STORAGE."/temp/recentblog.dat") < time()-600) {
        $queryblogs = DB::run() -> query("SELECT * FROM `blogs` ORDER BY `time` DESC LIMIT 5;");
        $recent = $queryblogs -> fetchAll();

        file_put_contents(STORAGE."/temp/recentblog.dat", serialize($recent), LOCK_EX);
    }

    $blogs = unserialize(file_get_contents(STORAGE."/temp/recentblog.dat"));

    if (is_array($blogs) && count($blogs) > 0) {
        foreach ($blogs as $blog) {
            echo '<i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/blog/blog?act=view&amp;id='.$blog['id'].'">'.$blog['title'].'</a> ('.$blog['comments'].')<br />';
        }
    }
}

// ------------- Функция вывода количества предложений и пожеланий -------------//
function stats_offers() {
    if (@filemtime(STORAGE."/temp/offers.dat") < time()-10800) {
        $offers = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [0]);
        $problems = DB::run() -> querySingle("SELECT count(*) FROM `offers` WHERE `type`=?;", [1]);

        file_put_contents(STORAGE."/temp/offers.dat", $offers.'/'.$problems, LOCK_EX);
    }

    return file_get_contents(STORAGE."/temp/offers.dat");
}

// ------------------------- Функция открытия файла ------------------------//
function fn_open($name, $mode, $method, $level) {
    if ($method == 2) {
        $name = "{$name}.bz2";
        return bzopen($name, $mode);
    } elseif ($method == 1) {
        $name = "{$name}.gz";
        return gzopen($name, "{$mode}b{$level}");
    } else {
        return fopen($name, "{$mode}b");
    }
}

// ------------------------- Функция записи в файл ------------------------//
function fn_write($fp, $str, $method) {
    if ($method == 2) {
        bzwrite($fp, $str);
    } elseif ($method == 1) {
        gzwrite($fp, $str);
    } else {
        fwrite($fp, $str);
    }
}

// ------------------------- Функция закрытия файла ------------------------//
function fn_close($fp, $method) {
    if ($method == 2) {
        bzclose($fp);
    } elseif ($method == 1) {
        gzclose($fp);
    } else {
        fflush($fp);
        fclose($fp);
    }
}

// ------------------ Функция пересчета сообщений и комментарий ---------------//
function restatement($mode) {
    switch ($mode) {
        case 'forum':
            DB::run() -> query("UPDATE `topics` SET `posts`=(SELECT count(*) FROM `posts` WHERE `topics`.`id`=`posts`.`topic_id`);");
            DB::run() -> query("UPDATE `forums` SET `topics`=(SELECT count(*) FROM `topics` WHERE `forums`.`id`=`topics`.`forum_id`);");
            DB::run() -> query("UPDATE `forums` SET `posts`=(SELECT SUM(posts) FROM `topics` WHERE `forums`.`id`=`topics`.`forum_id`);");
            break;

        case 'blog':
            DB::run() -> query("UPDATE `catsblog` SET `count`=(SELECT count(*) FROM `blogs` WHERE `catsblog`.`id`=`blogs`.`category_id`);");
            DB::run() -> query("UPDATE `blogs` SET `comments`=(SELECT count(*) FROM `comments` WHERE relate_type='Blog' AND `blogs`.`id`=`comments`.`relate_id`);");
            break;

        case 'load':
            DB::run() -> query("UPDATE `cats` SET `count`=(SELECT count(*) FROM `downs` WHERE `cats`.`id`=`downs`.`category_id` AND `active`=?);", [1]);
            DB::run() -> query("UPDATE `downs` SET `comments`=(SELECT count(*) FROM `comments` WHERE relate_type='Down' AND `downs`.`id`=`comments`.`relate_id`);");
            break;

        case 'news':
            DB::run() -> query("UPDATE `news` SET `comments`=(SELECT count(*) FROM `comments` WHERE relate_type='News' AND `news`.`id`=`comments`.`relate_id`);");
            break;

        case 'gallery':
            DB::run() -> query("UPDATE `photo` SET `comments`=(SELECT count(*) FROM `comments` WHERE relate_type='Gallery' AND `photo`.`id`=`comments`.`relate_id`);");
            break;

        case 'events':
            DB::run() -> query("UPDATE `events` SET `comments`=(SELECT count(*) FROM `comments` WHERE relate_type='Event' AND `events`.`id`=`comments`.`relate_id`);");
            break;
    }
    return true;
}

// ------------------------ Функция записи в файл ------------------------//
function write_files($filename, $text, $clear = 0, $chmod = 0) {

    if (empty($clear)) {
        file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
    } else {
        file_put_contents($filename, $text, LOCK_EX);
    }

    if (!empty($chmod)) {
        @chmod($filename, $chmod);
    }
}

// ------------------- Функция подсчета строк в файле--------------------//
function counter_string($files) {
    $count_lines = 0;
    if (file_exists($files)) {
        $lines = file($files);
        $count_lines = count($lines);
    }
    return $count_lines;
}

// ------------- Функция кэширования админских ссылок -------------//
function cache_admin_links($cache=10800) {
    if (@filemtime(STORAGE.'/temp/adminlinks.dat') < time()-$cache) {
        $files = array_diff(scandir(APP.'/modules/admin/links'), ['.', '..']);
        $links = [];

        foreach ($files as $file){
            $access = intval(preg_replace('/[^\d]+/', '', $file));
            $links[$access][] = $file;
        }
        file_put_contents(STORAGE.'/temp/adminlinks.dat', serialize($links), LOCK_EX);
    }

    return unserialize(file_get_contents(STORAGE.'/temp/adminlinks.dat'));
}

// ------------- Функция вывода админских ссылок -------------//
function show_admin_links($level = 0) {

    $links = cache_admin_links();

    if (!empty($links[$level])){
        foreach ($links[$level] as $link){
            if (file_exists(APP.'/modules/admin/links/'.$link)){
                include_once(APP.'/modules/admin/links/'.$link);
            }
        }
    }
}

// ------------- Функция кэширования уменьшенных изображений -------------//
function resize_image($dir, $name, $size, $params = []) {
    if (!empty($name) && file_exists(HOME.'/'.$dir.$name)){

        $prename = str_replace('/', '_', $dir.$name);
        $newname = substr($prename, 0, strrpos($prename, '.'));
        $imgsize = getimagesize(HOME.'/'.$dir.$name);

        if (empty($params['alt'])) $params['alt'] = $name;

        if (! isset($params['class'])) {
            $params['class'] = 'img-responsive';
        }

        $strParams = [];
        foreach ($params as $key => $param) {
            $strParams[] = $key.'="'.$param.'"';
        }

        $strParams = implode(' ', $strParams);

        if ($imgsize[0] <= $size && $imgsize[1] <= $size) {
            return '<img src="/'.$dir.$name.'"'.$strParams.' />';
        }

        if (!file_exists(HOME.'/uploads/thumbnail/'.$prename) || filesize(HOME.'/uploads/thumbnail/'.$prename) < 18) {
            $handle = new upload(HOME.'/'.$dir.$name);

            if ($handle -> uploaded) {
                $handle -> file_new_name_body = $newname;
                $handle -> image_resize = true;
                $handle -> image_ratio = true;
                $handle -> image_ratio_no_zoom_in = true;
                $handle -> image_y = $size;
                $handle -> image_x = $size;
                $handle -> file_overwrite = true;
                $handle -> process(HOME.'/uploads/thumbnail/');
            }
        }
        return '<img src="/uploads/thumbnail/'.$prename.'"'.$strParams.' />';
    }

    return '<img src="/assets/img/images/photo.jpg" alt="nophoto" />';
}

// ------------- Функция переадресации -------------//
function redirect($url, $permanent = false){

    if ($permanent){
        header('HTTP/1.1 301 Moved Permanently');
    }

    exit(header('Location: '.$url));
}

// ------------- Функция вывода ссылки на анкету -------------//
function profile($user, $color = false){
    global $config;

    if ($user){
        if ($color){
            return '<a href="/user/'.$user->login.'"><span style="color:'.$color.'">'.$user->login.'</span></a>';
        } else {
            return '<a href="/user/'.$user->login.'">'.$user->login.'</a>';
        }
    }
    return $config['guestsuser'];
}

// ------------- Функция вывода рейтинга -------------//
function format_num($num = 0){

    if ($num > 0) {
        return '<span style="color:#00aa00">+'.$num.'</span>';
    } elseif ($num < 0) {
        return '<span style="color:#ff0000">'.$num.'</span>';
    } else {
        return 0;
   }
}

// ------------- Подключение стилей -------------//
function include_style(){
    echo '<link rel="stylesheet" href="/assets/css/bootstrap.min.css" type="text/css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/css/font-awesome.min.css" type="text/css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/css/prettify.css" type="text/css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/js/markitup/style.css" type="text/css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/css/toastr.min.css" type="text/css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/js/mediaelement/mediaelementplayer.min.css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/js/mediaelement/mejs-skins.css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/js/colorbox/colorbox.css" />'."\r\n";
    echo '<link rel="stylesheet" href="/assets/css/app.css" type="text/css" />'."\r\n";
}

// ------------- Подключение javascript -------------//
function include_javascript(){
    echo '<script type="text/javascript" src="/assets/js/jquery-3.1.1.min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/prettify.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/markitup/jquery.markitup.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/markitup/markitup.set.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/bootbox.min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/toastr.min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/mediaelement/mediaelement-and-player.min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/colorbox/jquery.colorbox-min.js"></script>'."\r\n";
    echo '<script type="text/javascript" src="/assets/js/app.js"></script>'."\r\n";
}

// ------------- Прогресс бар -------------//
function progress_bar($percent, $title = false){

    if (! $title){
        $title = $percent.'%';
    }

    echo '<div class="progress" style="width: 250px;">
        <div class="progress-bar progress-bar-success" style="width: '.$percent.'%;"></div>
        <span style="float:right; color:#000; margin-right:5px;">'.$title.'</span>
    </div>';
}

// ------------- Добавление пользовательского файла в ZIP-архив -------------//
function copyright_archive($filename){

    $readme_file = HOME.'/assets/Visavi_Readme.txt';
    $ext = getExtension($filename);

    if ($ext == 'zip' && file_exists($readme_file)){
        $archive = new PclZip($filename);
        $archive->add($readme_file, PCLZIP_OPT_REMOVE_PATH, dirname($readme_file));

        return true;
    }
}

// ------------- Функция загрузки и обработки изображений -------------//
function upload_image($file, $weight, $size, $new_name = false){

    global $config;

    $handle = new FileUpload($file);

    if ($handle->uploaded) {
        $handle -> image_resize = true;
        $handle -> image_ratio = true;
        $handle -> image_ratio_no_zoom_in = true;
        $handle -> image_y = $config['screensize'];
        $handle -> image_x = $config['screensize'];
        $handle -> file_overwrite = true;

        if ($handle->file_src_name_ext == 'png' ||
            $handle->file_src_name_ext == 'bmp'){
            $handle->image_convert = 'jpg';
        }
        if (!empty($new_name)) {
            $handle -> file_new_name_body = $new_name;
        }
        if (!empty($config['copyfoto'])) {
            $handle -> image_watermark = HOME.'/assets/img/images/watermark.png';
            $handle -> image_watermark_position = 'BR';
        }

        $handle -> ext_check = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
        $handle -> file_max_size = $weight;  // byte
        $handle -> image_max_width = $size;  // px
        $handle -> image_max_height = $size; // px
        $handle -> image_min_width = 100;     // px
        $handle -> image_min_height = 100;    // px

        return $handle;
    }

    return false;
}

// ------------- Функция определения расширения файла -------------//
function getExtension($filename){
    return strtolower(substr(strrchr($filename, '.'), 1));
}

// ----- Функция определения входит ли пользователь в контакты -----//
function is_contact($login, $contact){

    if (user($contact)) {
        $check_contact = DB::run() -> queryFetch("SELECT * FROM `contact` WHERE `user`=? AND `name`=? LIMIT 1;", [$login, $contact]);

        if (!empty($check_contact)){
            return true;
        }
    }
    return false;
}

// ----- Функция определения входит ли пользователь в игнор -----//
function is_ignore($login, $ignore){

    if (user($ignore)) {
        $check_ignore = DB::run() -> queryFetch("SELECT * FROM ignoring WHERE `user`=? AND `name`=? LIMIT 1;", [$login, $ignore]);

        if (!empty($check_ignore)){
            return true;
        }
    }
    return false;
}

// ----- Функция определения приватности у пользователя -----//
function user_privacy($login){
    $privacy = DB::run() -> querySingle("SELECT `privacy` FROM `users` WHERE `login`=? LIMIT 1;", [$login]);
    return ($privacy) ? true : false;
}

// ----- Функция рекурсивного удаления директории -----//
function removeDir($dir){
    if (file_exists($dir)){
        if ($files = glob($dir.'/*')) {
            foreach($files as $file) {
                is_dir($file) ? removeDir($file) : unlink($file);
            }
        }
        rmdir($dir);
    }
}

// ----- Функция отправки приватного сообщения -----//
function send_private($login, $sender, $text, $time = SITETIME){
    if (user($login)) {

        DB::run() -> query("INSERT INTO `inbox` (`user`, `author`, `text`, `time`) VALUES (?, ?, ?, ?);",
        [$login, $sender, $text, $time]);

        DB::run() -> query("UPDATE `users` SET `newprivat`=`newprivat`+1 WHERE `login`=? LIMIT 1;", [$login]);

        save_usermail();
        return true;
    }
    return false;
}

// ----- Функция подготовки приватного сообщения -----//
function text_private($id, $replace = []){

    $message = DB::run() -> querySingle("SELECT `text` FROM `notice` WHERE `id`=? LIMIT 1;", [$id]);

    if (!empty($message)){
        foreach ($replace as $key=>$val){
            $message = str_replace($key, $val, $message);
        }
    } else {
        $message = 'Отсутствует текст сообщения!';
    }
    return $message;
}

// ------------ Функция записи flash уведомлений -----------//
function notice($message, $status = 'success'){
    $_SESSION['note'][$status][] = $message;
}

// ------------ Функция статистики производительности -----------//
function perfomance(){

    if (is_admin() && App::setting('performance')){

        $queries = env('APP_DEBUG') ? getQueryLog() : [];

        App::view('app/_perfomance', compact('queries'));
    }
}

/**
 * Очистка кеш-файлов
 * @return boolean результат выполнения
 */
function clearCache()
{
    $cachefiles = glob(STORAGE.'/temp/*.dat');
    $cachefiles = array_diff($cachefiles, [
        STORAGE.'/temp/checker.dat',
        STORAGE.'/temp/counter7.dat'
    ]);

    if (is_array($cachefiles) && count($cachefiles)>0){
        foreach ($cachefiles as $file) {
            unlink ($file);
        }
    }

    // Авто-кэширование данных
    save_ipban();

    return true;
}

/**
 * Gets the value of an environment variable. Supports boolean, empty and null.
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function env($key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return value($default);
    }
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
    }
    if (strlen($value) > 1 && starts_with($value, '"') && str_finish($value, '"')) {
        return substr($value, 1, -1);
    }
    return $value;
}

/**
 * Возвращает параметры роутов
 * @param  string $key     ключ параметра
 * @param  mixed  $default значение по умолчанию
 * @return mixed           найденный параметр
 */
function param($key, $default = null)
{
    $router = Registry::get('router')->match();
    return isset($router['params'][$key]) ? $router['params'][$key] : $default;
}

/**
 * Translate the given message.
 *
 * @param  string  $id
 * @param  array   $replace
 * @param  string  $locale
 * @return string
 */
function trans($id, $replace = [], $locale = null)
{
    return App::translator()->trans($id, $replace, $locale);
}


/**
 * Translates the given message based on a count.
 *
 * @param  string  $id
 * @param  int|array|\Countable  $number
 * @param  array   $replace
 * @param  string  $locale
 * @return string
 */
function trans_choice($id, $number, array $replace = [], $locale = null)
{
    return App::translator()->transChoice($id, $number, $replace, $locale);
}

/**
 * Возвращает форматированный список запросов
 * @return array
 */
function getQueryLog()
{
    $queries = Capsule::getQueryLog();
    $formattedQueries = [];
    foreach ($queries as $query) {
        $prep = $query['query'];
        foreach ($query['bindings'] as $binding) {
            $binding = is_int($binding) ? $binding : "'{$binding}'";
            $prep = preg_replace("#\?#", $binding, $prep, 1);
        }
        $formattedQueries[] = ['query' => $prep, 'time' => $query['time']];
    }
    return $formattedQueries;
}
