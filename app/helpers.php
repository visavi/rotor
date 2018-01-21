<?php

use App\Classes\BBCode;
use App\Classes\Registry;
use App\Classes\Request;
use App\Models\Antimat;
use App\Models\Ban;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Blog;
use App\Models\Bookmark;
use App\Models\Load;
use App\Models\Chat;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Counter;
use App\Models\Down;
use App\Models\Guest;
use App\Models\Ignore;
use App\Models\Inbox;
use App\Models\Invite;
use App\Models\Log;
use App\Models\Login;
use App\Models\News;
use App\Models\Note;
use App\Models\Notebook;
use App\Models\Notice;
use App\Models\Offer;
use App\Models\Online;
use App\Models\Outbox;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Rating;
use App\Models\RekUser;
use App\Models\Setting;
use App\Models\Smile;
use App\Models\Spam;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManagerStatic as Image;
use Jenssegers\Blade\Blade;

/**
 * Форматирует вывод времени из секунд
 *
 * @param  string $time секунды
 * @return string       форматированный вывод
 */
function makeTime($time)
{
    if ($time < 3600) {
        $time = sprintf('%02d:%02d', ($time / 60) % 60, $time % 60);
    } else {
        $time = sprintf('%02d:%02d:%02d', ($time / 3600) % 24, ($time / 60) % 60, $time % 60);
    }

    return $time;
}

/**
 * Форматирует время с учетом часовых поясов
 *
 * @param  int     $timestamp секунды
 * @param  string  $format    формат времени
 * @return string             форматированный вывод
 */
function dateFixed($timestamp, $format = 'd.m.y / H:i')
{
    if (! is_numeric($timestamp)) {
        $timestamp = SITETIME;
    }

    $shift     = getUser('timezone') * 3600;
    $dateStamp = date($format, $timestamp + $shift);

    $today     = date('d.m.y', SITETIME + $shift);
    $yesterday = date('d.m.y', strtotime('-1 day', SITETIME + $shift));

    $dateStamp = str_replace([$today, $yesterday], ['Сегодня', 'Вчера'], $dateStamp);

    $search = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $replace = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];

    return str_replace($search, $replace, $dateStamp);
}

/**
 * Удаляет записи пользователя из всех таблиц
 *
 * @param  User $user объект пользователя
 * @return bool          результат удаления
 * @throws Exception
 */
function deleteUser(User $user)
{
    deleteImage('uploads/photos/', $user->picture);
    deleteImage('uploads/avatars/', $user->avatar);

    Inbox::query()->where('user_id', $user->id)->delete();
    Outbox::query()->where('user_id', $user->id)->delete();
    Contact::query()->where('user_id', $user->id)->delete();
    Ignore::query()->where('user_id', $user->id)->delete();
    Rating::query()->where('user_id', $user->id)->delete();
    Wall::query()->where('user_id', $user->id)->delete();
    Note::query()->where('user_id', $user->id)->delete();
    Notebook::query()->where('user_id', $user->id)->delete();
    Banhist::query()->where('user_id', $user->id)->delete();
    Bookmark::query()->where('user_id', $user->id)->delete();
    Login::query()->where('user_id', $user->id)->delete();
    Invite::query()->where('user_id', $user->id)->orWhere('invite_user_id', $user->id)->delete();

    return $user->delete();
}

/**
 * Удаляет альбом пользователя
 *
 * @param  User $user объект пользователя
 * @return void
 */
function deleteAlbum(User $user)
{
    $photos = Photo::query()->where('user_id', $user->id)->get();

    if ($photos->isNotEmpty()) {
        foreach ($photos as $photo) {

            Comment::query()
                ->where('relate_type', Photo::class)
                ->where('relate_id', $photo->id)
                ->delete();

            Photo::query()->where('id', $photo->id)->delete();

            deleteImage('uploads/pictures/', $photo->link);
        }
    }
}

/**
 * Удаляет изображение и превью
 *
 * @param string $dir   директория с изображение
 * @param string $image имя изображения
 * @return bool
 */
function deleteImage($dir, $image)
{
    if (! $image) {
        return true;
    }

    $path = str_replace('/', '_', $dir.$image);

    if (file_exists(HOME.'/'.$dir.$image)) {
        unlink(HOME.'/'.$dir.$image);
    }

    if (file_exists(UPLOADS.'/thumbnail/'.$path)) {
        unlink(UPLOADS.'/thumbnail/'.$path);
    }

    return true;
}

/**
 * Конвертирует строку в кодировку utf-8
 *
 * @param  string $str строка
 * @return string      конвертированная строка
 */
function winToUtf($str)
{
    return mb_convert_encoding($str, 'utf-8', 'windows-1251');
}

/**
 * Преобразует строку в нижний регистр
 *
 * @param  string $str строка
 * @return string      преобразованная строка
 */
function utfLower($str)
{
    return mb_strtolower($str, 'utf-8');
}

/**
 * Обрезает строку
 *
 * @param string  $str    строка
 * @param int     $start  начало позиции
 * @param int     $length конец позиции
 * @return string         обрезанная строка
 */
function utfSubstr($str, $start, $length = null)
{
    if (! $length) {
        $length = utfStrlen($str);
    }

    return mb_substr($str, $start, $length, 'utf-8');
}

/**
 * Возвращает длину строки
 *
 * @param string $str строка
 * @return int        длина строка
 */
function utfStrlen($str)
{
    return mb_strlen($str, 'utf-8');
}

/**
 * Определяет является ли кодировка utf-8
 *
 * @param  string $str строка
 * @return bool
 */
function isUtf($str)
{
    return mb_check_encoding($str, 'utf-8');
}

/**
 * Преобразует специальные символы в HTML-сущности
 *
 * @param  mixed $string строка или массив строк
 * @return mixed         обработанные данные
 */
function check($string)
{
    if (is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = check($val);
        }
    } else {
        $string =  htmlspecialchars($string, ENT_QUOTES);
        $search = [chr(0), "\x00", "\x1A", chr(226) . chr(128) . chr(174)];

        $string = trim(str_replace($search, [], $string));
    }

    return $string;
}

/**
 * Преобразует в положительное число
 *
 * @param  string $num число
 * @return int         обработанные данные
 */
function int($num)
{
    return (int) abs($num);
}

/**
 * Преобразует все элементы массива в int
 *
 * @param  mixed $numbers массив или число
 * @return array          обработанные данные
 */
function intar($numbers)
{
    if ($numbers) {
        if (is_array($numbers)) {
            $numbers = array_map('intval', $numbers);
        } else {
            $numbers = [(int) $numbers];
        }
    }

    return $numbers;
}

/**
 * Возвращает размер в человекочитаемом формате
 *
 * @param  string  $bytes     размер в байтах
 * @param  integer $precision кол. символов после запятой
 * @return string             форматированный вывод размера
 */
function formatSize($bytes, $precision = 2)
{
    $units = ['byte','Kb','Mb','Gb','Tb'];
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1000));
    $pow   = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . $units[$pow];
}

/**
 * Возвращает размер файла человекочитаемом формате
 *
 * @param  string     $file путь к файлу
 * @return int|string       размер в читаемом формате
 */
function formatFileSize($file)
{
    if (file_exists($file) && is_file($file)) {
        return formatSize(filesize($file));
    }

    return 0;
}

/**
 * Возвращает время в человекочитаемом формате
 *
 * @param  string $time  кол. секунд timestamp
 * @return string        время в читаемом формате
 */
function formatTime($time)
{
    $units = [
        'год,года,лет'           => 365 * 24 * 60 * 60,
        'месяц,месяца,месяцев'   => 30 * 24 * 60 * 60,
        'неделя,недели,недель'   => 7 * 24 * 60 * 60,
        'день,дня,дней'          => 24 * 60 * 60,
        'час,часа,часов'         => 60 * 60,
        'минута,минуты,минут'    => 60,
        'секунда,секунды,секунд' => 1,
    ];

    foreach ($units as $unit => $seconds) {
        $format = $time / $seconds;

        if ($format >= 1) {
            return plural(round($format), $unit);
        }
    }

    return 0;
}

/**
 * Очищает строку от мата по базе слов
 *
 * @param  string $str строка
 * @return string      обработанная строка
 */
function antimat($str)
{
    $words = Antimat::query()
        ->orderBy(DB::raw('CHAR_LENGTH(string)'), 'desc')
        ->pluck('string')
        ->all();

    if ($words) {
        foreach($words as $word) {
            $str = preg_replace('|'.preg_quote($word).'|iu', '***', $str);
        }
    }

    return $str;
}

/**
 * Возвращает имя уровня пользователя
 *
 * @param  string $level уровень пользователя
 * @return mixed         имя уровня
 */
function userLevel($level)
{
    $name = explode(',', setting('statusname'));

    switch ($level) {
        case User::BOSS:
            $status = $name[0];
            break;
        case User::ADMIN:
            $status = $name[1];
            break;
        case User::MODER:
            $status = $name[2];
            break;
        case User::EDITOR:
            $status = $name[3];
            break;
        case User::USER:
            $status = $name[4];
            break;
        case User::PENDED:
            $status = $name[5];
            break;
        case User::BANNED:
            $status = $name[6];
            break;
        default: $status = setting('statusdef');
    }

    return $status;
}

/**
 * Кеширует статусы пользователей
 *
 * @param int $time время кеширования
 */
function saveStatus($time = 0)
{
    if (empty($time) || @filemtime(STORAGE.'/temp/status.dat') < time() - $time) {

    $users = User::query()
        ->select('users.id', 'users.status', 'status.name', 'status.color')
        ->leftJoin('status', function($join) {
            $join->whereRaw('users.point between status.topoint and status.point');
        })
        ->where('users.point', '>', 0)
        ->get();

        $statuses = [];
        foreach ($users as $user) {

            if ($user->status) {
                $statuses[$user->id] = '<span style="color:#ff0000">'.$user->status.'</span>';
                continue;
            }

            if ($user->color) {
                $statuses[$user->id] = '<span style="color:'.$user->color.'">'.$user->name.'</span>';
                continue;
            }

            $statuses[$user->id] = $user->name;
        }

        file_put_contents(STORAGE.'/temp/status.dat', json_encode($statuses, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}

/**
 * Возвращает статус пользователя
 *
 * @param  User   $user объект пользователя
 * @return string       статус пользователя
 */
function userStatus(User $user)
{
    static $status;

    if (! $user) {
        return setting('statusdef');
    }

    if (! $status) {
        saveStatus(3600);
        $status = json_decode(file_get_contents(STORAGE.'/temp/status.dat'));
    }

    return $status->{$user->id} ?? setting('statusdef');
}

/**
 * Возвращает рейтинг в виде звезд
 *
 * @param  float  $rating рейтинг
 * @return string         преобразованный рейтинг
 */
function ratingVote($rating)
{
    $rating = round($rating / 0.5) * 0.5;

    $full_stars = floor($rating);
    $half_stars = ceil($rating - $full_stars);
    $empty_stars = 5 - $full_stars - $half_stars;

    $output = '<div class="star-rating fa-lg text-danger">';
    $output .= str_repeat('<i class="fa fa-star"></i>', $full_stars);
    $output .= str_repeat('<i class="fa fa-star-half"></i>', $half_stars);
    $output .= str_repeat('<i class="far fa-star"></i>', $empty_stars);
    $output .= '</div>';

    return $output;
}

/**
 * Формирует календарь
 *
 * @param  int   $month месяц
 * @param  int   $year  год
 * @return array        сформированный массив
 */
function makeCalendar($month, $year)
{
    $wday = date('w', mktime(0, 0, 0, $month, 1, $year));

    if ($wday === 0) {
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
                $row[] = '';
            }
        }
        if (! $notEmpty) break;
        $cal[] = $row;
    }
    return $cal;
}

/**
 * Возвращает количество писем пользователя
 *
 * @param  User $user объект пользователя
 * @return int        количество писем
 */
function userMail(User $user)
{
    return Inbox::query()->where('user_id', $user->id)->count();
}

/**
 * Возвращает аватар для пользователя по умолчанию
 *
 * @param  User   $user логин пользователя
 * @return string       код аватара
 */
function defaultAvatar(User $user)
{
    $name   = empty($user->name) ? $user->login : $user->name;
    $color  = '#'.substr(dechex(crc32($user->login)), 0, 6);
    $letter = mb_strtoupper(utfSubstr($name, 0, 1), 'utf-8');

    return '<div class="avatar" style="background:'.$color.'"><a href="/user/'.$user->login.'">'.$letter.'</a></div>';
}

/**
 * Возвращает аватар пользователя
 *
 * @param  User   $user объект пользователя
 * @return string       аватар пользователя
 */
function userAvatar(User $user)
{
    if (! $user->id) {
        return '<img src="/assets/img/images/avatar_guest.png" alt=""> ';
    }

    if ($user->avatar && file_exists(UPLOADS.'/avatars/'.$user->avatar)) {
        return '<a href="/user/'.$user->login.'"><img src="/uploads/avatars/'.$user->avatar.'" alt=""></a> ';
    }

    return defaultAvatar($user);
    //return '<a href="/user/'.$user->login.'"><img src="/assets/img/images/avatar_default.png" alt=""></a> ';
}

/**
 * Возвращает размер контакт-листа
 *
 * @param  User $user объект пользователя
 * @return int        количество контактов
 */
function userContact(User $user)
{
    return Contact::query()->where('user_id', $user->id)->count();
}

/**
 * Возвращает размер игнор-листа
 *
 * @param  User $user объект пользователя
 * @return int        количество игнорируемых
 */
function userIgnore(User $user)
{
    return Ignore::query()->where('user_id', $user->id)->count();
}

/**
 * Возвращает количество записей на стене сообщений
 *
 * @param  User $user объект пользователя
 * @return int        количество записей
 */
function userWall(User $user)
{
    return Wall::query()->where('user_id', $user->id)->count();
}

/**
 * Возвращает количество пользователей онлайн по типам
 *
 * @param  int   $cache время кеширования данных
 * @return array        массив данных
 */
function statsOnline($cache = 30)
{
    if (@filemtime(STORAGE.'/temp/online.dat') < time() - $cache) {

        $online[] = Online::query()->whereNotNull('user_id')->count();
        $online[] = Online::query()->count();

        include_once APP.'/Includes/count.php';

        file_put_contents(STORAGE.'/temp/online.dat', json_encode($online), LOCK_EX);
    }

    return json_decode(file_get_contents(STORAGE.'/temp/online.dat'));
}

/**
 * Возвращает количество пользователей онлайн
 *
 * @return string
 */
function showOnline()
{
    if (setting('onlines')) {
        return view('app/_online', ['online' => statsOnline()]);
    }

    return null;
}

/**
 * Возвращает статистику посещений
 *
 * @return array статистика посещений
 */
function statsCounter()
{
    if (@filemtime(STORAGE.'/temp/counter.dat') < time() - 10) {
        $counts = Counter::query()->first();
        file_put_contents(STORAGE.'/temp/counter.dat', json_encode($counts), LOCK_EX);
    }

    return json_decode(file_get_contents(STORAGE.'/temp/counter.dat'));
}

/**
 * Выводит счетчик посещений
 *
 * @return string
 */
function showCounter()
{
    include_once APP.'/Includes/counters.php';

    if (setting('incount') > 0) {
        return view('app/_counter', ['count' => statsCounter()]);
    }

    return null;
}

/**
 * Возвращает количество пользователей
 *
 * @return int количество пользователей
 */
function statsUsers()
{
    if (@filemtime(STORAGE.'/temp/statusers.dat') < time() - 3600) {

        $stat = User::query()->count();
        $new  = User::query()->where('joined', '>', date('Y-m-01'))->count();

        if ($new) {
            $stat = $stat.'/+'.$new;
        }

        file_put_contents(STORAGE.'/temp/statusers.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statusers.dat');
}

/**
 * Возвращает количество администраторов
 *
 * @return int количество администраторов
 */
function statsAdmins()
{
    if (@filemtime(STORAGE.'/temp/statadmins.dat') < time() - 3600) {

        $total = User::query()->whereIn('level', User::ADMIN_GROUPS)->count();

        file_put_contents(STORAGE.'/temp/statadmins.dat', $total, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statadmins.dat');
}

/**
 * Возвращает количество спама
 *
 * @return int количество спама
 */
function statsSpam()
{
    return Spam::query()->count();
}

/**
 * Возвращает количество забанненых пользователей
 *
 * @return int количество забаненных
 */
function statsBanned()
{
    return User::query()
        ->where('level', User::BANNED)
        ->where('timeban', '>', SITETIME)
        ->count();
}

/**
 * Возвращает количество записей в истории банов
 *
 * @return int количество записей
 */
function statsBanHist()
{
    return Banhist::query()->count();
}

/**
 * Возвращает количество ожидающих подтверждения регистрации
 *
 * @return int количество ожидающих
 */
function statsRegList()
{
    return User::query()->where('level', User::PENDED)->count();
}

/**
 * Возвращает количество забаненных по IP
 *
 * @return int количество забаненных
 */
function statsIpBanned()
{
    return Ban::query()->count();
}

/**
 * Возвращает количество фотографий в галерее
 *
 * @return int количество фотографий
 */
function statsGallery()
{
    if (@filemtime(STORAGE.'/temp/statgallery.dat') < time() - 900) {
        $stat     = Photo::query()->count();
        $totalNew = Photo::query()->where('created_at', '>', SITETIME - 86400 * 3)->count();

        if ($totalNew) {
            $stat = $stat.'/+'.$totalNew;
        }

        file_put_contents(STORAGE.'/temp/statgallery.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statgallery.dat');
}

/**
 * Возвращает количество новостей
 *
 * @return int количество новостей
 */
function statsNews()
{
    return News::query()->count();
}

/**
 * Возвращает количество записей в черном списке
 *
 * @return string количество записей
 */
function statsBlacklist()
{
    $blacklist = BlackList::query()
        ->select('type', DB::raw('count(*) as total'))
        ->groupBy('type')
        ->pluck('total', 'type')
        ->all();

    $list = $blacklist + ['login' => 0, 'email' => 0, 'domain' => 0];

    return $list['login'].'/'.$list['email'].'/'.$list['domain'];
}

/**
 * Возвращает количество записей в антимате
 *
 * @return int количество записей
 */
function statsAntimat()
{
    return Antimat::query()->count();
}

/**
 * Возвращает количество смайлов
 *
 * @return int количество смайлов
 */
function statsSmiles()
{
    return Smile::query()->count();
}

/**
 * Возвращает дату последнего сканирования сайта
 *
 * @return int|string дата последнего сканирования
 */
function statsChecker()
{
    if (file_exists(STORAGE.'/temp/checker.dat')) {
        return dateFixed(filemtime(STORAGE.'/temp/checker.dat'), 'j.m.y');
    }

    return 0;
}

/**
 * Возвращает количество приглашений на регистрацию
 *
 * @return int количество приглашений
 */
function statsInvite()
{
    $invited     = Invite::query()->where('used', 0)->count();
    $usedInvited = Invite::query()->where('used', 1)->count();

    return $invited.'/'.$usedInvited;
}

/**
 * Возвращает онлайн-статус пользователя
 *
 * @param  User   $user объект пользователя
 * @return string       онлайн-статус
 */
function userOnline(User $user)
{
    static $visits;

    $online = '<i class="fa fa-asterisk text-danger"></i>';

    if (! $visits) {
        if (@filemtime(STORAGE.'/temp/visit.dat') < time() - 10) {

            $onlines = Online::query()
                ->whereNotNull('user_id')
                ->pluck('user_id', 'user_id')
                ->all();

            file_put_contents(STORAGE.'/temp/visit.dat', json_encode($onlines), LOCK_EX);
        }

        $visits = json_decode(file_get_contents(STORAGE.'/temp/visit.dat'));
    }

    if (isset($visits->{$user->id})) {
        $online = '<i class="fa fa-asterisk fa-spin text-success"></i>';
    }

    return $online;
}

/**
 * Возвращает следующею и предыдущую фотографию в галерее
 *
 * @param  int   $id Id фотографи
 * @return mixed     массив данных
 */
function photoNavigation($id)
{
    if (! $id) {
        return false;
    }

    $next = Photo::query()
        ->where('id', '>', $id)
        ->orderBy('id')
        ->pluck('id')
        ->first();

    $prev = Photo::query()
        ->where('id', '<', $id)
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();

    return compact('next', 'prev');
}

/**
 * Возвращает количество статей в блогах
 *
 * @return string количество статей
 */
function statsBlog()
{
    if (@filemtime(STORAGE.'/temp/statblogblog.dat') < time() - 900) {

        $stat      = Blog::query()->count();
        $totalnew  = Blog::query()->where('created_at', '>', SITETIME - 86400 * 3)->count();

        if ($totalnew) {
            $stat = $stat.'/+'.$totalnew;
        }

        file_put_contents(STORAGE.'/temp/statblog.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statblog.dat');
}

/**
 * Возвращает количество тем и сообщений в форуме
 *
 * @return string количество тем и сообщений
 */
function statsForum()
{
    if (@filemtime(STORAGE.'/temp/statforum.dat') < time() - 600) {

        $topics = Topic::query()->count();
        $posts  = Post::query()->count();

        file_put_contents(STORAGE.'/temp/statforum.dat', $topics.'/'.$posts, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statforum.dat');
}

/**
 * Возвращает количество сообщений в гостевой книге
 *
 * @return int количество сообщений
 */
function statsGuest()
{
    if (@filemtime(STORAGE.'/temp/statguest.dat') < time() - 600) {

        $total = Guest::query()->count();

        file_put_contents(STORAGE.'/temp/statguest.dat', $total, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statguest.dat');
}

/**
 * Возвращает количество сообщений в админ-чате
 *
 * @return int количество сообщений
 */
function statsChat()
{
    return Chat::query()->count();
}

/**
 * Возвращает время последнего сообщения в админ-чате
 *
 * @return string время сообщения
 */
function statsNewChat()
{
    return Chat::query()->max('created_at');
}

/**
 * Возвращает количество файлов в загруз-центре
 *
 * @return string количество файлов
 */
function statsLoad()
{
    if (@filemtime(STORAGE.'/temp/statload.dat') < time() - 900) {

        $totalLoads = Load::query()->sum('count');

        $totalNew = Down::query()->where('active', 1)
            ->where('created_at', '>', SITETIME - 86400 * 5)
            ->count();

        $stat = $totalNew ? $totalLoads.'/+'.$totalNew : $totalLoads;

        file_put_contents(STORAGE.'/temp/statload.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statload.dat');
}

/**
 * Возвращает количество новых файлов
 *
 * @return string количество файлов
 */
function statsNewLoad()
{
    return Down::query()->where('active', 0)->count();
}

/**
 * Обфусцирует email
 *
 * @param  string $mail email
 * @return string       обфусцированный email
 */
function cryptMail($mail)
{
    $output  = '';
    $symbols = str_split($mail);

    foreach ($symbols as $symbol) {
        $output .= '&#'.ord($symbol).';';
    }

    return $output;
}

/**
 * Возвращает статистику текущих голосований из кэш-файла,
 * предварительно сформировав этот файл, если он устарел
 *
 * @return string Статистика текущий голосований
 */
function statVotes()
{
    if (@filemtime(STORAGE.'/temp/statvote.dat') < time() - 900) {

        $votes = Vote::query()
            ->selectRaw('count(*) AS cnt, ifnull(sum(count), 0) AS sum')
            ->where('closed', 0)
            ->first();

        file_put_contents(STORAGE.'/temp/statvote.dat', $votes->cnt.'/'.$votes->sum, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statvote.dat');
}

/**
 * Возвращает дату последней новости из кэш-файла,
 * предварительно сформировав этот файл, если он устарел
 *
 * @return string Дата последней новости или тег span с текстом "Сегодня "
 */
function statsNewsDate()
{
    if (@filemtime(STORAGE.'/temp/statnews.dat') < time() - 900) {
        $stat = 0;

        $news = News::query()->orderBy('created_at', 'desc')->first();

        if ($news) {
            $stat = dateFixed($news->created_at, 'd.m.y');
        }

        file_put_contents(STORAGE.'/temp/statnews.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/statnews.dat');
}

/**
 * Возвращает последние новости
 *
 * @return void новость
 */
function lastNews()
{
    if (setting('lastnews') > 0) {

        $news = News::query()
            ->where('top', 1)
            ->orderBy('created_at', 'desc')
            ->limit(setting('lastnews'))
            ->get();

        $total = count($news);

        if ($total > 0) {
            foreach ($news as $data) {
                $data['text'] = str_replace('[cut]', '', $data->text);
                echo '<i class="far fa-circle fa-lg text-muted"></i> <a href="/news/'.$data->id.'">'.$data->title.'</a> ('.$data->comments.') <i class="fa fa-caret-down news-title"></i><br>';

                echo '<div class="news-text" style="display: none;">'.bbCode($data->text).'<br>';
                echo '<a href="/news/comments/'.$data->id.'">Комментарии</a> ';
                echo '<a href="/news/end/'.$data->id.'">&raquo;</a></div>';
            }
        }
    }
}

/**
 * Возвращает является ли пользователь авторизованным
 *
 * @return mixed
 */
function checkAuth()
{
    if (isset($_SESSION['id'], $_SESSION['password'])) {

        $user = User::query()->find($_SESSION['id']);

        if ($user && $_SESSION['password'] === md5(env('APP_KEY').$user->password)) {
            return $user;
        }
    }

    return false;
}

/**
 * Возвращает является ли пользователь администратором
 *
 * @param string $level уровень доступа
 * @return bool         является ли пользователь администратором
 */
function isAdmin($level = User::EDITOR)
{
    return access($level);
}

/**
 * Возвращает имеет ли пользователь доступ по уровню
 *
 * @param  string $level уровень доступа
 * @return bool          разрешен ли доступ
 */
function access($level)
{
    $access = array_flip(User::ALL_GROUPS);

    return getUser() && isset($access[$level]) && isset($access[getUser('level')]) && $access[getUser('level')] <= $access[$level];
}

/**
 * Возвращает иконку расширения
 *
 * @param  string $ext расширение файла
 * @return string      иконка
 */
function icons($ext)
{
    switch ($ext) {
        case 'php':
            $ico = 'file-code';
            break;
        case 'ppt':
            $ico = 'file-powerpoint';
            break;
        case 'doc':
        case 'docx':
            $ico = 'file-word';
            break;
        case 'xls':
        case 'xlsx':
            $ico = 'file-excel';
            break;
        case 'txt':
        case 'css':
        case 'dat':
        case 'html':
        case 'htm':
            $ico = 'file-alt';
            break;
        case 'wav':
        case 'amr':
        case 'mp3':
        case 'mid':
            $ico = 'file-audio';
            break;
        case 'zip':
        case 'rar':
        case '7z':
        case 'gz':
            $ico = 'file-archive';
            break;
        case '3gp':
        case 'mp4':
            $ico = 'file-video';
            break;
        case 'jpg':
        case 'jpeg':
        case 'bmp':
        case 'wbmp':
        case 'gif':
        case 'png':
            $ico = 'file-image';
            break;
        case 'ttf':
            $ico = 'font';
            break;
        case 'pdf':
            $ico = 'file-pdf';
            break;
        default: $ico = 'file';
    }
    return '<i class="far fa-'.$ico.'"></i>';
}

/**
 * Перемешивает элементы ассоциативного массива, сохраняя ключи
 *
 * @param  array &$array Исходный массив, переданный по ссылке
 * @return bool          Флаг успешного выполнения операции
 */
function shuffleAssoc(&$array)
{
    $keys = array_keys($array);

    shuffle($keys);
    $new = [];

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;
    return true;
}

/**
 * Возвращает обрезанную до заданного количества слов строку
 *
 * @param  string  $str   Исходная строка
 * @param  int     $words Максимальное количество слов в результате
 * @return string         Обрезанная строка
 */
function stripString($str, $words = 20) {
    return implode(' ', array_slice(explode(' ', strip_tags($str)), 0, $words));
}

/**
 * Возвращает HTML пользовательской рекламы
 *
 * @return string Сгенерированный HTML пользовательской рекламы
 */
function getAdvertUser()
{
    if (setting('rekusershow')) {
        if (@filemtime(STORAGE.'/temp/rekuser.dat') < time() - 1800) {
            saveAdvertUser();
        }

        $datafile = json_decode(file_get_contents(STORAGE.'/temp/rekuser.dat'));

        if ($datafile) {

            $total = count($datafile);
            $show  = setting('rekusershow') > $total ? $total : setting('rekusershow');

            $links  = array_random($datafile, $show);
            $result = implode('<br>', $links);

            return view('advert/_user', compact('result'));
        }
    }
}

/**
 * Кэширует ссылки пользовательской рекламы
 *
 * @return void Список ссылок
 */
function saveAdvertUser()
{
    $data = RekUser::query()->where('deleted_at', '>', SITETIME)->get();

    $links = [];

    if ($data->isNotEmpty()) {
        foreach ($data as $val) {
            if ($val['color']) {
                $val['name'] = '<span style="color:'.$val->color.'">'.$val->name.'</span>';
            }

            $link = '<a href="'.$val->site.'" target="_blank" rel="nofollow">'.$val->name.'</a>';

            if ($val->bold) {
                $link = '<b>'.$link.'</b>';
            }

            $links[] = $link;
        }
    }

    file_put_contents(STORAGE.'/temp/rekuser.dat', json_encode($links, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Выводит последние фотографии
 *
 * @param  int  $show Количество последних фотографий
 * @return void       Список фотографий
 */
function recentPhotos($show = 5)
{
    if (@filemtime(STORAGE.'/temp/recentphotos.dat') < time() - 1800) {

        $recent = Photo::query()->orderBy('created_at', 'desc')->limit($show)->get();

        file_put_contents(STORAGE.'/temp/recentphotos.dat', json_encode($recent, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $photos = json_decode(file_get_contents(STORAGE.'/temp/recentphotos.dat'));

    if ($photos) {
        foreach ($photos as $data) {
            echo '<a href="/gallery/'.$data->id.'">'.resizeImage('uploads/pictures/', $data->link, ['alt' => $data->title, 'class' => 'rounded', 'style' => 'width: 100px; height: 100px;']).'</a>';
        }

        echo '<br>';
    }
}

/**
 * Выводит последние темы форума
 *
 * @param  int  $show Количество последних тем форума
 * @return void       Список тем
 */
function recentTopics($show = 5)
{
    if (@filemtime(STORAGE.'/temp/recenttopics.dat') < time() - 180) {
        $lastTopics = Topic::query()->orderBy('updated_at', 'desc')->limit($show)->get();
        file_put_contents(STORAGE.'/temp/recenttopics.dat', json_encode($lastTopics, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $topics = json_decode(file_get_contents(STORAGE.'/temp/recenttopics.dat'));

    if ($topics) {
        foreach ($topics as $topic) {
            echo '<i class="far fa-circle fa-lg text-muted"></i>  <a href="/topic/'.$topic->id.'">'.$topic->title.'</a> ('.$topic->posts.')';
            echo '<a href="/topic/end/' . $topic->id . '">&raquo;</a><br>';
        }
    }
}

/**
 * Выводит последние файлы в загрузках
 *
 * @param  int  $show Количество последних файлов в загрузках
 * @return void       Список файлов
 */
function recentFiles($show = 5)
{
    if (@filemtime(STORAGE.'/temp/recentfiles.dat') < time() - 600) {

        $lastFiles = Down::query()
            ->where('active', 1)
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->with('category')
            ->get();

        file_put_contents(STORAGE.'/temp/recentfiles.dat', json_encode($lastFiles, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $files = json_decode(file_get_contents(STORAGE.'/temp/recentfiles.dat'));

    if ($files) {
        foreach ($files as $file){
            $folder = $file->category->folder ? $file->category->folder.'/' : null;
            $filesize = $file->link ? formatFileSize(UPLOADS.'/files/'.$folder.$file->link) : 0;
            echo '<i class="far fa-circle fa-lg text-muted"></i>  <a href="/down/'.$file->id.'">'.$file->title.'</a> ('.$filesize.')<br>';
        }
    }
}

/**
 * Выводит последние статьи в блогах
 *
 * @param  int  $show Количество последних статей в блогах
 * @return void       Список статей
 */
function recentBlogs($show = 5)
{
    if (@filemtime(STORAGE.'/temp/recentblog.dat') < time() - 600) {
        $lastBlogs = Blog::query()
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->get();

        file_put_contents(STORAGE.'/temp/recentblog.dat', json_encode($lastBlogs, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $blogs = json_decode(file_get_contents(STORAGE.'/temp/recentblog.dat'));

    if ($blogs) {
        foreach ($blogs as $blog) {
            echo '<i class="far fa-circle fa-lg text-muted"></i> <a href="/article/'.$blog->id.'">'.$blog->title.'</a> ('.$blog->comments.')<br>';
        }
    }
}

/**
 *  Возвращает количество предложений и проблем
 *
 * @return string количество предложений и проблем
 */
function statsOffers()
{
    if (@filemtime(STORAGE.'/temp/offers.dat') < time() - 10800) {

        $offers   = Offer::query()->where('type', 'offer')->count();
        $problems = Offer::query()->where('type', 'issue')->count();

        file_put_contents(STORAGE.'/temp/offers.dat', $offers.'/'.$problems, LOCK_EX);
    }

    return file_get_contents(STORAGE.'/temp/offers.dat');
}

/**
 * Пересчитывает счетчики
 *
 * @param  string $mode сервис счетчиков
 * @return void
 */
function restatement($mode)
{
    switch ($mode) {
        case 'forum':
            DB::update('update topics set posts = (select count(*) from posts where topics.id = posts.topic_id)');
            DB::update('update forums set topics = (select count(*) from topics where forums.id = topics.forum_id)');
            DB::update('update forums set posts = (select SUM(posts) from topics where forums.id = topics.forum_id)');
            break;

        case 'blog':
            DB::update('update categories set count = (select count(*) from blogs where categories.id = blogs.category_id)');
            DB::update('update blogs set comments = (select count(*) from comments where relate_type = "'.addslashes(Blog::class).'" and blogs.id = comments.relate_id)');
            break;

        case 'load':
            DB::update('update loads set count = (select count(*) from downs where loads.id = downs.category_id and active = ?)', [1]);
            DB::update('update downs set comments = (select count(*) from comments where relate_type = "'.addslashes(Down::class).'" and downs.id = comments.relate_id)');
            break;

        case 'news':
            DB::update('update news set comments = (select count(*) from comments where relate_type = "'.addslashes(News::class).'" and news.id = comments.relate_id)');
            break;

        case 'photo':
            DB::update('update photo set comments = (select count(*) from comments where relate_type=  "'.addslashes(Photo::class).'" and photo.id = comments.relate_id)');
            break;

        case 'offer':
            DB::update('update offers set comments = (select count(*) from comments where relate_type=  "'.addslashes(Offer::class).'" and offer.id = comments.relate_id)');
            break;
    }
}

// ------------------- Функция подсчета строк в файле--------------------//
function counterString($files)
{
    $count_lines = 0;
    if (file_exists($files)) {
        $lines = file($files);
        $count_lines = count($lines);
    }
    return $count_lines;
}

// ------------- Функция вывода ссылки на анкету -------------//
/**
 * Возвращает ссылку на профиль пользователя
 *
 * @param  User   $user  объект пользователя
 * @param  string $color цвет логина
 * @return string        путь к профилю
 */
function profile(User $user, $color = null)
{
    if ($user->id){
        $name = empty($user->name) ? $user->login : $user->name;

        if ($color){
            return '<a href="/user/'.$user->login.'"><span style="color:'.$color.'">'.$name.'</span></a>';
        }

        return '<a href="/user/'.$user->login.'">'.$name.'</a>';
    }

    return setting('guestsuser');
}

/**
 * Форматирует вывод числа
 *
 * @param  int    $num число
 * @return string      форматированное число
 */
function formatNum($num)
{
    if ($num > 0) {
        return '<span style="color:#00aa00">+'.$num.'</span>';
    }

    if ($num < 0) {
        return '<span style="color:#ff0000">'.$num.'</span>';
    }

    return '<span>0</span>';
}

/**
 * Загружает изображение
 *
 * @param  UploadedFile $file путь изображения
 * @param  int          $path путь сохранения изображения
 * @return string             имя загруженного файла
 */
function uploadImage(UploadedFile $file, $path)
{
    $picture = uniqid() . '.' . $file->getClientOriginalExtension();

    $img = Image::make($file);
    $img->resize(setting('screensize'), setting('screensize'), function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });

    if (setting('copyfoto')) {
        $img->insert(HOME . '/assets/img/images/watermark.png', 'bottom-right', 10, 10);
    }

    $img->save($path . $picture);

    return $img->basename;
}

/**
 * Выполняет уменьшение и кеширование изображений
 *
 * @param  string $dir    путь изображения
 * @param  string $name   имя уменьшенного изображения
 * @param  array  $params параметры изображения
 * @return string         уменьшенное изображение
 */
function resizeImage($dir, $name, array $params = [])
{
    if (! empty($name) && file_exists(HOME.'/'.$dir.$name)) {

        $prepareName = str_replace('/', '_', $dir.$name);
        list($width, $height) = getimagesize(HOME.'/'.$dir.$name);

        if (empty($params['alt'])) {
            $params['alt'] = $name;
        }

        if (empty($params['class'])) {
            $params['class'] = 'img-fluid';
        }

        if (empty($params['size'])) {
            $params['size'] = setting('previewsize');
        }

        $strParams = [];
        foreach ($params as $key => $param) {
            $strParams[] = $key.'="'.$param.'"';
        }

        $strParams = implode(' ', $strParams);

        if ($width <= $params['size'] && $height <= $params['size']) {
            return '<img src="/'.$dir.$name.'"'.$strParams.'>';
        }

        if (! file_exists(UPLOADS.'/thumbnail/'.$prepareName)) {

            $img = Image::make(HOME.'/'.$dir.$name);

            $img->fit($params['size'], $params['size'], function ($constraint) {
                $constraint->upsize();
            });

            $img->save(UPLOADS . '/thumbnail/' . $prepareName);
        }

        return '<img src="/uploads/thumbnail/'.$prepareName.'"'.$strParams.'>';
    }

    return '<img src="/assets/img/images/photo.jpg" alt="nophoto">';
}


/**
 * Возвращает находится ли пользователь в контакатх
 *
 * @param  User $user        пользователя
 * @param  User $contactUser объект пользователя
 * @return bool              находится ли в контактах
 */
function isContact(User $user, User $contactUser)
{
    $isContact = Contact::query()
        ->where('user_id', $user->id)
        ->where('contact_id', $contactUser->id)
        ->first();

    if ($isContact) {
        return true;
    }

    return false;
}

/**
 * Возвращает находится ли пользователь в игноре
 *
 * @param  User $user       объект пользователя
 * @param  User $ignoreUser объект пользователя
 * @return bool             находится ли в игноре
 */
function isIgnore(User $user, User $ignoreUser)
{

    $isIgnore = Ignore::query()
        ->where('user_id', $user->id)
        ->where('ignore_id', $ignoreUser->id)
        ->first();

    if ($isIgnore) {
        return true;
    }

    return false;
}

/**
 * Удаляет директорию рекурсивно
 *
 * @param string $dir
 */
function removeDir($dir)
{
    if (file_exists($dir)){
        if ($files = glob($dir . '/*')) {
            foreach($files as $file) {
                is_dir($file) ? removeDir($file) : unlink($file);
            }
        }
        rmdir($dir);
    }
}

/**
 * Отправляет приватное сообщение
 *
 * @param  User $user   Получатель
 * @param  User $author Отправитель
 * @param  int  $text   текст сообщения
 * @return bool         результат отправки
 */
function sendPrivate(User $user, User $author = null, $text)
{
    Inbox::query()->create([
        'user_id'    => $user->id,
        'author_id'  => $author->id ?? null,
        'text'       => $text,
        'created_at' => SITETIME,
    ]);

    $user->increment('newprivat');

    return true;
}

/**
 * Возвращает приватное сообщение
 *
 * @param  string $type    тип сообщения
 * @param  array  $replace массив заменяемых параметров
 * @return string          сформированный текст
 */
function textNotice($type, array $replace = [])
{
    $message = Notice::query()->where('type', $type)->first();

    if (! $message) {
        return 'Отсутствует текст сообщения!';
    }

    foreach ($replace as $key => $val) {
        $message->text = str_replace($key, $val, $message->text);
    }

    return $message->text;
}

/**
 * Выводит блок статистики производительности
 *
 * @return string статистика производительности
 */
function performance()
{
    if (isAdmin() && setting('performance')){

        $queries = env('APP_DEBUG') ? getQueryLog() : [];
        return view('app/_performance', compact('queries'));
    }

    return null;
}

/**
 * Очистка кеш-файлов
 *
 * @return bool результат выполнения
 */
function clearCache()
{
    $files = glob(STORAGE.'/temp/*.dat');
    $files = array_diff($files, [
        STORAGE.'/temp/checker.dat',
        STORAGE.'/temp/counter7.dat'
    ]);

    if ($files){
        foreach ($files as $file) {
            unlink ($file);
        }
    }

    // Авто-кэширование данных
    ipBan(true);

    return true;
}

/**
 * Возвращает текущую страницу
 *
 * @param null $url
 * @return string текущая страница
 */
function returnUrl($url = null)
{
    if (Request::is('/', 'login', 'register', 'recovery', 'restore', 'ban', 'closed')) {
        return false;
    }
    $query = Request::has('return') ? Request::input('return') : Request::path();
    return '?return='.urlencode(! $url ? $query : $url);
}

/**
 * Возвращает подключенный шаблон
 *
 * @param  string $view   имя шаблона
 * @param  array  $params массив параметров
 * @return string         сформированный код
 */
function view($view, array $params = [])
{
    $blade = new Blade([
        HOME.'/themes/'.setting('themes').'/views',
        RESOURCES.'/views',
        HOME.'/themes',
    ], STORAGE.'/cache');

    return $blade->render($view, $params);
}

/**
 * Сохраняет страницы с ошибками
 *
 * @param  integer $code    код ошибки
 * @param  string  $message текст ошибки
 * @return string  сформированная страница с ошибкой
 */
function abort($code, $message = null)
{
    if ($code === 403) {
        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
    }

    if ($code === 404) {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    }

    if (setting('errorlog') && in_array($code, [403, 404])) {

        Log::query()->create([
            'code'       => $code,
            'request'    => utfSubstr(server('REQUEST_URI'), 0, 200),
            'referer'    => utfSubstr(server('HTTP_REFERER'), 0, 200),
            'user_id'    => getUser('id'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }

    if (Request::ajax()) {
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');

        exit(json_encode([
            'status' => 'error',
            'message' => $message,
        ]));
    }

    $referer = Request::header('referer') ?? null;
    exit(view('errors/'.$code, compact('message', 'referer')));
}

/**
 * Переадресовывает пользователя
 *
 * @param  string  $url       адрес переадресации
 * @param  bool    $permanent постоянное перенаправление
 * @return void
 */
function redirect($url, $permanent = false)
{
    if (isset($_SESSION['captcha'])) {
        $_SESSION['captcha'] = null;
    }

    if ($permanent){
        header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
    }

    header('Location: '.$url);
    exit();
}

/**
 * Сохраняет flash уведомления
 *
 * @param string $status статус уведомления
 * @param mixed $message массив или текст с уведомлениями
 * @return void
 */
function setFlash($status, $message)
{
    $_SESSION['flash'][$status] = $message;
}

/**
 * Возвращает ошибку
 *
 * @param  mixed $errors ошибки
 * @return string сформированный блок с ошибкой
 */
function showError($errors)
{
    if (is_array($errors)) {
        $errors = implode('<br><i class="fa fa-exclamation-circle fa-lg text-danger"></i> ', $errors);
    }

    return view('app/_error', compact('errors'));
}

/**
 * Сохраняет POST данные введенных пользователем
 *
 * @param array $data массив полей
 */
function setInput(array $data)
{
    $prepareData = [];
    foreach($data as $key => $value) {

        if (is_object($value)) {
            continue;
        }

        $prepareData[$key] = $value;
    }

    $_SESSION['input'] = $prepareData;
}

/**
 * Возвращает значение из POST данных
 *
 * @param string $name имя поля
 * @param string $default
 * @return string сохраненный текст
 */
function getInput($name, $default = null)
{
    if (isset($_SESSION['input'][$name])) {
        $input = $_SESSION['input'][$name];
        unset($_SESSION['input'][$name]);
    }

    return $input ?? $default;
}

/**
 * Подсвечивает блок с полем для ввода сообщения
 *
 * @param string $field имя поля
 * @return string CSS класс ошибки
 */
function hasError($field)
{
    return isset($_SESSION['flash']['danger'][$field]) ? ' has-error' : '';
}

/**
 * Возвращает блок с текстом ошибки
 *
 * @param  string $field имя поля
 * @return string        блоки ошибки
 */
function textError($field)
{
    $text = null;

    if (isset($_SESSION['flash']['danger'][$field])) {
        $error = $_SESSION['flash']['danger'][$field];
        $text = '<div class="text-danger">'.$error.'</div>';
    }

    return $text;
}

/**
 * Отправка уведомления на email
 *
 * @param  mixed   $to      Получатель
 * @param  string  $subject Тема письма
 * @param  string  $body    Текст сообщения
 * @param  array   $params  Дополнительные параметры
 * @return bool             Результат отправки
 */
function sendMail($to, $subject, $body, array $params = [])
{
    if (empty($params['from'])) {
        $params['from'] = [env('SITE_EMAIL') => env('SITE_ADMIN')];
    }

    $message = (new Swift_Message())
        ->setTo($to)
        ->setSubject($subject)
        ->setBody($body, 'text/html')
        ->setFrom($params['from'])
        ->setReturnPath(env('SITE_EMAIL'))
        ->setBody($body, 'text/html');

    if (env('MAIL_DRIVER') === 'smtp') {
        $transport = (new Swift_SmtpTransport(env('MAIL_HOST'), env('MAIL_PORT'), env('MAIL_ENCRYPTION')))
            ->setUsername(env('MAIL_USERNAME'))
            ->setPassword(env('MAIL_PASSWORD'));
    } else {
        $transport = new Swift_SendmailTransport(env('MAIL_PATH'));
    }

    $mailer = new Swift_Mailer($transport);
    return $mailer->send($message);
}

/**
 * Возвращает расширение файла
 *
 * @param  string $filename имя файла
 * @return string расширение
 */
function getExtension($filename)
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

/**
 * Склоняет числа
 *
 * @param  integer $num  число
 * @param  mixed   $forms массив склоняемых слов (один, два, много)
 * @return string  форматированная строка
 */
function plural($num, $forms)
{
    if (! is_array($forms)) {
        $forms = explode(',', $forms);
    }

    if (count($forms) === 1) {
        return $num.' '.$forms[0];
    }

    if ($num % 100 > 10 &&  $num % 100 < 15) return $num.' '.$forms[2];
    if ($num % 10 === 1) return $num.' '.$forms[0];
    if ($num % 10 > 1 && $num %10 < 5) return $num.' '.$forms[1];
    return $num.' '.$forms[2];
}

/**
 * Валидирует даты
 *
 * @param  string $date   дата
 * @param  string $format формат даты
 * @return bool           результат валидации
 */
function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Обрабатывает BB-код
 *
 * @param  string  $text  Необработанный текст
 * @param  bool    $parse Обрабатывать или вырезать код
 * @return string         Обработанный текст
 */
function bbCode($text, $parse = true)
{
    $bbCode = new BBCode();

    if (! $parse) {
        return $bbCode->clear($text);
    }

    $text = $bbCode->parse($text);
    $text = $bbCode->parseSmiles($text);

    return $text;
}

/**
 * Определяет IP пользователя
 *
 * @return string IP пользователя
 */
function getIp()
{
    $ip = Request::ip();
    return $ip === '::1' ? '127.0.0.1' : $ip;
}

/**
 * Определяет браузер
 *
 * @param string|null $userAgent
 * @return string браузер и версия браузера
 */
function getBrowser($userAgent = null)
{
    $browser = new Browser();
    if ($userAgent) {
        $browser->setUserAgent($userAgent);
    }

    $brow = $browser->getBrowser();
    $version = implode('.', array_slice(explode('.', $browser->getVersion()), 0, 2));
    return mb_substr($version === 'unknown' ? $brow : $brow.' '.$version, 0, 25, 'utf-8');
}

/**
 * Возвращает серверные переменные
 *
 * @param string|null $key     ключ массива
 * @param string|null $default значение по умолчанию
 * @return mixed               данные
 */
function server($key = null, $default = null)
{
    $server = Request::server($key, $default);
    if ($key === 'REQUEST_URI') $server = urldecode($server);
    if ($key === 'PHP_SELF') $server = current(explode('?', server('REQUEST_URI')));

    return check($server);
}

/**
 * Возвращает объект пользователя по логину
 *
 * @param  string    $login логин пользователя
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
 */
function getUserByLogin($login)
{
    return User::query()->where('login', $login)->first();
}

/**
 * Возвращает объект пользователя по id
 *
 * @param  int       $id ID пользователя
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
 */
function getUserById($id)
{
    return User::query()->find($id);
}

/**
 * Возвращает настройки пользователя по ключу
 *
 * @param  string $key ключ массива
 * @return string|\Illuminate\Database\Query\Builder|User
 */
function getUser($key = null)
{
    if (Registry::has('user')) {
        if ($key) {
            return Registry::get('user')[$key] ?? null;
        }

        return Registry::get('user');
    }

    return null;
}

/**
 * Генерирует постраничную навигация
 *
 * @param  array $page массив данных
 * @return string        сформированный блок
 */
function pagination($page)
{
    if (empty($page['total'])) {
        return null;
    }

    if (empty($page['crumbs'])) {
        $page['crumbs'] = 3;
    }

    $url     = array_except($_GET, 'page');
    $request = $url ? '&'.http_build_query($url) : null;

    $pages   = [];
    $pg_cnt  = ceil($page['total'] / $page['limit']);
    $idx_fst = max($page['current'] - $page['crumbs'], 1);
    $idx_lst = min($page['current'] + $page['crumbs'], $pg_cnt);

    if ($page['current'] != 1) {
        $pages[] = [
            'page'  => $page['current'] - 1,
            'title' => 'Предыдущая',
            'name'  => '«',
        ];
    }

    if ($page['current'] > $page['crumbs'] + 1) {
        $pages[] = [
            'page'  => 1,
            'title' => '1 страница',
            'name'  => 1,
        ];
        if ($page['current'] != $page['crumbs'] + 2) {
            $pages[] = [
                'separator' => true,
                'name'      => ' ... ',
            ];
        }
    }

    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        if ($i == $page['current']) {
            $pages[] = [
                'current' => true,
                'name'    => $i,
            ];
        } else {
            $pages[] = [
                'page'  => $i,
                'title' => $i.' страница',
                'name'  => $i,
            ];
        }
    }

    if ($page['current'] < $pg_cnt - $page['crumbs']) {
        if ($page['current'] != $pg_cnt - $page['crumbs'] - 1) {
            $pages[] = [
                'separator' => true,
                'name'      => ' ... ',
            ];
        }
        $pages[] = [
            'page'  => $pg_cnt,
            'title' => $pg_cnt . ' страница',
            'name'  => $pg_cnt,
        ];
    }

    if ($page['current'] != $pg_cnt) {
        $pages[] = [
            'page'  => $page['current'] + 1,
            'title' => 'Следующая',
            'name'  => '»',
        ];
    }

    return view('app/_pagination', compact('pages', 'request'));
}

/**
 * Обрабатывает постраничную навигацию
 *
 * @param  integer $limit элементов на страницу
 * @param  integer $total всего элементов
 * @return array          массив подготовленных данных
 */
function paginate($limit, $total)
{
    $current = (int) Request::input('page');

    if ($current < 1) {
        $current = 1;
    }

    if ($total && $current * $limit >= $total) {
        $current = (int) ceil($total / $limit);
    }

    $offset = (int) ($current * $limit) - $limit;

    return compact('current', 'offset', 'limit', 'total');
}

/**
 * Возвращает сформированный код base64 картинки
 *
 * @param string  $path   путь к картинке
 * @param array   $params параметры
 * @return string         сформированный код
 */
function imageBase64($path, array $params = [])
{
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);

    if (! isset($params['class'])) {
        $params['class'] = 'img-fluid';
    }

    $strParams = [];
    foreach ($params as $key => $param) {
        $strParams[] = $key.'="'.$param.'"';
    }

    $strParams = implode(' ', $strParams);

    return '<img src="data:image/'.$type.';base64,'.base64_encode($data).'"'.$strParams.'>';
}

/**
 * Выводит прогресс-бар
 *
 * @param  int    $percent
 * @param  bool   $title
 * @return string
 */
function progressBar($percent, $title = false)
{
    if (! $title){
        $title = $percent.'%';
    }

    return view('app/_progressbar', compact('percent', 'title'));
}

/**
 * Инициализирует языковую локализацию
 *
 * @param  string $fallback
 * @return \Illuminate\Translation\Translator
 */
function translator($fallback = 'en')
{
    $translator = new \Illuminate\Translation\Translator(
        new \Illuminate\Translation\FileLoader(
            new \Illuminate\Filesystem\Filesystem(),
            RESOURCES.'/lang'
        ),
        setting('lang')
    );

    $translator->setFallback($fallback);

    return $translator;
}

/**
 * Translate the given message.
 *
 * @param  string  $id
 * @param  array   $replace
 * @param  string  $locale
 * @return string
 */
function trans($id, array $replace = [], $locale = null)
{
    return translator()->trans($id, $replace, $locale);
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
    return translator()->transChoice($id, $number, $replace, $locale);
}

/**
 * Возвращает форматированный список запросов
 * @return array
 */
function getQueryLog()
{
    $queries = DB::getQueryLog();
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

/**
 * Выводит список забаненных ip
 *
 * @param  bool $save нужно ли сбросить кеш
 * @return array      массив IP
 */
function ipBan($save = false)
{
    if (! $save && file_exists(STORAGE.'/temp/ipban.dat')) {
        $ipBan = json_decode(file_get_contents(STORAGE.'/temp/ipban.dat'));
    } else {
        $ipBan = Ban::query()->pluck('ip')->all();
        file_put_contents(STORAGE.'/temp/ipban.dat', json_encode($ipBan), LOCK_EX);
    }

    return $ipBan;
}

/**
 * Возвращает настройки сайта по ключу
 *
 * @param  string $key ключ массива
 * @return string      данные
 */
function setting($key = null)
{
    if (! Registry::has('setting')) {

        if (! file_exists(STORAGE.'/temp/setting.dat')) {
            saveSetting();
        }

        $setting = json_decode(file_get_contents(STORAGE.'/temp/setting.dat'), true);

        Registry::set('setting', $setting);
    }

    if (! $key) {
        return Registry::get('setting');
    }

    return Registry::get('setting')[$key] ?? null;
}

/**
 * Устанавливает настройки сайта
 *
 * @param array $setting массив настроек
 */
function setSetting($setting)
{
    $setting = array_merge(Registry::get('setting'), $setting);
    Registry::set('setting', $setting);
}

/**
 * Кеширует настройки сайта
 */
function saveSetting() {
    $setting = Setting::query()->pluck('value', 'name')->all();
    file_put_contents(STORAGE.'/temp/setting.dat', json_encode($setting, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Возвращает путь к сайту
 *
 * @param  bool   $parse выводить протокол
 * @return string        адрес сайта
 */
function siteUrl($parse = false)
{
    $url = env('SITE_URL');

    if ($parse) {
        $url = starts_with($url, '//') ? 'http:' . $url : $url;
    }

    return $url;
}

/**
 * Возвращает имя сайта из ссылки
 *
 * @param  string $url ссылка на сайт
 * @return string      имя сайта
 */
function siteDomain($url)
{
    $url = strtolower($url);
    $url = str_replace(['http://www.', 'http://', 'https://', '//'], '', $url);
    $url = strtok($url, '/?');

    return $url;
}

/**
 * Получает версию
 *
 * @param  string $version
 * @return string
 */
function parseVersion($version)
{
    $version = explode('.', strtok($version, '-'));

    return $version[0] . '.' . $version[1] . '.' . $version[2] ?? 0;
}
