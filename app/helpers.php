<?php

use App\Classes\BBCode;
use App\Classes\Calendar;
use App\Classes\CloudFlare;
use App\Classes\Metrika;
use App\Models\AdminAdvert;
use App\Models\Advert;
use App\Models\Antimat;
use App\Models\Article;
use App\Models\Ban;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Chat;
use App\Models\Counter;
use App\Models\Down;
use App\Models\Error;
use App\Models\Guestbook;
use App\Models\Invite;
use App\Models\Item;
use App\Models\Load;
use App\Models\News;
use App\Models\Notice;
use App\Models\Offer;
use App\Models\Online;
use App\Models\PaidAdvert;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Spam;
use App\Models\Sticker;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Intervention\Image\ImageManager;
use ReCaptcha\ReCaptcha;

const ROTOR_VERSION = '12.2';
define('SITETIME', time());

/**
 * Форматирует вывод времени из секунд
 *
 * @param int $time секунды
 *
 * @return string Форматированный вывод
 */
function makeTime(int $time): string
{
    $format = $time < 3600 ? 'i:s' : 'H:i:s';

    return gmdate($format, $time);
}

/**
 * Форматирует время с учетом часовых поясов
 */
function dateFixed(
    DateTimeInterface|int|null $timestamp,
    string $format = 'd.m.Y / H:i',
    bool $original = false,
): string {
    if ($timestamp === null) {
        $timestamp = SITETIME;
    }
    if (is_numeric($timestamp)) {
        $date = Date::createFromTimestamp($timestamp);
    } else {
        $date = Date::parse($timestamp);
    }

    $shift = (int) getUser('timezone');
    $dateStamp = $date->addHours($shift)->format($format);

    if ($original) {
        return $dateStamp;
    }

    $today = Date::now()->addHours($shift)->format('d.m.Y');
    $yesterday = Date::now()->addHours($shift)->subDay()->format('d.m.Y');

    $replaces = [
        $today      => __('main.today'),
        $yesterday  => __('main.yesterday'),
        'January'   => __('main.january'),
        'February'  => __('main.february'),
        'March'     => __('main.march'),
        'April'     => __('main.april'),
        'May'       => __('main.may'),
        'June'      => __('main.june'),
        'July'      => __('main.july'),
        'August'    => __('main.august'),
        'September' => __('main.september'),
        'October'   => __('main.october'),
        'November'  => __('main.november'),
        'December'  => __('main.december'),
    ];

    return strtr($dateStamp, $replaces);
}

/**
 * Конвертирует строку в кодировку utf-8
 *
 * @param string $str строка
 *
 * @return string Конвертированная строка
 */
function winToUtf(string $str): string
{
    return mb_convert_encoding($str, 'utf-8', 'windows-1251');
}

/**
 * Преобразует строку в нижний регистр
 *
 * @param string $str строка
 *
 * @return string Преобразованная строка
 */
function utfLower(string $str): string
{
    return mb_strtolower($str, 'utf-8');
}

/**
 * Обрезает строку
 *
 * @param mixed    $str    Строка
 * @param int      $start  Начало позиции
 * @param int|null $length Конец позиции
 *
 * @return string Обрезанная строка
 */
function utfSubstr(mixed $str, int $start, ?int $length = null): string
{
    if (! $length) {
        $length = utfStrlen($str);
    }

    return mb_substr((string) $str, $start, $length, 'utf-8');
}

/**
 * Возвращает длину строки
 *
 * @param mixed $str строка
 *
 * @return int Длина строка
 */
function utfStrlen($str): int
{
    return mb_strlen($str, 'utf-8');
}

/**
 * Является ли кодировка utf-8
 *
 * @param string $str строка
 */
function isUtf(string $str): bool
{
    return mb_check_encoding($str, 'utf-8');
}

/**
 * Преобразует специальные символы в HTML-сущности
 *
 * @param mixed $string       Строка или массив строк
 * @param bool  $doubleEncode Преобразовывать существующие html-сущности
 *
 * @return array|string Обработанные данные
 */
function check($string, bool $doubleEncode = true)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = check($val, $doubleEncode);
        }
    } else {
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8', $doubleEncode);
        $search = [chr(0), "\x00", "\x1A", chr(226) . chr(128) . chr(174)];
        $string = str_replace($search, [], $string);
    }

    return $string;
}

/**
 * Преобразует в положительное число
 *
 * @param int|string $num число
 *
 * @return int Обработанные данные
 */
function int($num): int
{
    return abs((int) $num);
}

/**
 * Преобразует все элементы массива в int
 *
 * @param array|int|string $numbers массив или число
 *
 * @return array|null Обработанные данные
 */
function intar($numbers): ?array
{
    if (! $numbers) {
        return null;
    }

    if (is_array($numbers)) {
        $numbers = array_map('intval', $numbers);
    } else {
        $numbers = [(int) $numbers];
    }

    return $numbers;
}

/**
 * Возвращает размер в человеко читаемом формате
 *
 * @param int $bytes     размер в байтах
 * @param int $precision кол. символов после запятой
 *
 * @return string Форматированный вывод размера
 */
function formatSize(int $bytes, int $precision = 2): string
{
    $units = ['B', 'Kb', 'Mb', 'Gb', 'Tb'];
    $pow = floor(($bytes ? log($bytes) : 0) / log(1000));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . $units[$pow];
}

/**
 * Возвращает размер файла человеко-читаемом формате
 *
 * @param string $file Путь к файлу
 *
 * @return string Размер в читаемом формате
 */
function formatFileSize(string $file): string
{
    if (file_exists($file) && is_file($file)) {
        return formatSize(filesize($file));
    }

    return formatSize(0);
}

/**
 * Возвращает время в человеко-читаемом формате
 *
 * @param int $time   Кол. секунд timestamp
 * @param int $crumbs Кол. элементов
 *
 * @return string Время в читаемом формате
 */
function formatTime(int $time, int $crumbs = 2): string
{
    if ($time < 1) {
        return '0';
    }

    $units = [
        __('main.plural_years')   => 31536000,
        __('main.plural_months')  => 2592000,
        __('main.plural_days')    => 86400,
        __('main.plural_hours')   => 3600,
        __('main.plural_minutes') => 60,
        __('main.plural_seconds') => 1,
    ];

    $return = [];

    foreach ($units as $unit => $seconds) {
        $format = floor($time / $seconds);
        $time %= $seconds;

        if ($format >= 1) {
            $return[] = plural($format, $unit);
        }
    }

    return implode(' ', array_slice($return, 0, $crumbs));
}

/**
 * Очищает строку от мата по базе слов
 *
 * @param string|null $str строка
 *
 * @return string Обработанная строка
 */
function antimat(?string $str): string
{
    return Antimat::replace((string) $str);
}

/**
 * Возвращает календарь
 *
 *
 * @return HtmlString календарь
 */
function getCalendar(int $time = SITETIME): HtmlString
{
    $calendar = new Calendar();

    return new HtmlString($calendar->getCalendar($time));
}

/**
 * Возвращает количество пользователей онлайн по типам
 *
 * @return array Массив данных
 */
function statsOnline(): array
{
    return Cache::remember('online', 60, static function () {
        $users = Online::query()
            ->select('user_id')
            ->distinct('user_id')
            ->whereNotNull('user_id')
            ->get();

        $usersCount = $users->count();
        $guestsCount = Online::query()->whereNull('user_id')->count();
        $total = $usersCount + $guestsCount;

        $metrika = new Metrika();
        $metrika->getCounter($usersCount + $guestsCount);

        return [$usersCount, $guestsCount, $total, $users];
    });
}

/**
 * Возвращает количество пользователей онлайн
 */
function showOnline(): ?HtmlString
{
    if (setting('onlines')) {
        $online = statsOnline();

        return new HtmlString(view('app/_online', compact('online')));
    }

    return null;
}

/**
 * Get online widget
 *
 * @return mixed
 */
function onlineWidget(): HtmlString
{
    $online = statsOnline();

    return new HtmlString(view('widgets/_online', compact('online')));
}

/**
 * Возвращает статистику посещений
 *
 * @return array Статистика посещений
 */
function statsCounter(): array
{
    return Cache::remember('counter', 30, static function () {
        $counter = Counter::query()->first();

        return $counter ? $counter->toArray() : [];
    });
}

/**
 * Выводит счетчик посещений
 */
function showCounter(): ?HtmlString
{
    $metrika = new Metrika();
    $metrika->saveStatistic();

    $counter = statsCounter();

    if (setting('incount') > 0) {
        return new HtmlString(view('app/_counter', compact('counter')));
    }

    return null;
}

/**
 * Возвращает количество пользователей
 *
 * @return string Количество пользователей
 */
function statsUsers(): string
{
    return Cache::remember('statUsers', 1800, static function () {
        $stat = User::query()->count();
        $new = User::query()->where('created_at', '>', strtotime('-1 day', SITETIME))->count();

        if ($new) {
            $stat .= '/+' . $new;
        }

        return $stat;
    });
}

/**
 * Возвращает количество администраторов
 *
 * @return int Количество администраторов
 */
function statsAdmins(): int
{
    return Cache::remember('statAdmins', 3600, static function () {
        return User::query()->whereIn('level', User::ADMIN_GROUPS)->count();
    });
}

/**
 * Возвращает количество жалоб
 *
 * @return int Количество жалоб
 */
function statsSpam(): int
{
    return Spam::query()->count();
}

/**
 * Возвращает количество забанненых пользователей
 *
 * @return int Количество забаненных
 */
function statsBanned(): int
{
    return User::query()
        ->where('level', User::BANNED)
        ->where('timeban', '>', SITETIME)
        ->count();
}

/**
 * Возвращает количество записей в истории банов
 *
 * @return int Количество записей
 */
function statsBanHist(): int
{
    return Banhist::query()->count();
}

/**
 * Возвращает количество ожидающих подтверждения регистрации
 *
 * @return int Количество ожидающих
 */
function statsRegList(): int
{
    return User::query()->where('level', User::PENDED)->count();
}

/**
 * Возвращает количество забаненных по IP
 *
 * @return int Количество забаненных
 */
function statsIpBanned(): int
{
    return Ban::query()->count();
}

/**
 * Возвращает количество фотографий в галерее
 *
 * @return string Количество фотографий
 */
function statsPhotos(): string
{
    return Cache::remember('statPhotos', 900, static function () {
        $stat = Photo::query()->count();
        $totalNew = Photo::query()->where('created_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество новостей
 *
 * @return string Количество новостей
 */
function statsNews(): string
{
    return Cache::remember('statNews', 300, static function () {
        $total = News::query()->count();

        $totalNew = News::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество записей в черном списке
 *
 * @return string Количество записей
 */
function statsBlacklist(): string
{
    $blacklist = BlackList::query()
        ->selectRaw('type, count(*) as total')
        ->groupBy('type')
        ->pluck('total', 'type')
        ->all();

    $list = $blacklist + ['login' => 0, 'email' => 0, 'domain' => 0];

    return $list['login'] . '/' . $list['email'] . '/' . $list['domain'];
}

/**
 * Возвращает количество записей в антимате
 *
 * @return int Количество записей
 */
function statsAntimat(): int
{
    return Antimat::query()->count();
}

/**
 * Возвращает количество стикеров
 *
 * @return int Количество стикеров
 */
function statsStickers(): int
{
    return Sticker::query()->count();
}

/**
 * Возвращает дату последнего сканирования сайта
 *
 * @return int|string Дата последнего сканирования
 */
function statsChecker()
{
    if (file_exists(storage_path('framework/cache/checker.php'))) {
        return dateFixed(filemtime(storage_path('framework/cache/checker.php')), 'd.m.Y');
    }

    return 0;
}

/**
 * Возвращает количество приглашений на регистрацию
 *
 * @return string Количество приглашений
 */
function statsInvite(): string
{
    $invited = Invite::query()->where('used', 0)->count();
    $usedInvited = Invite::query()->where('used', 1)->count();

    return $invited . '/' . $usedInvited;
}

/**
 * Возвращает следующею и предыдущую фотографию в галерее
 *
 * @param int $id Id фотографий
 *
 * @return array|null Массив данных
 */
function photoNavigation(int $id): ?array
{
    if (! $id) {
        return null;
    }

    $next = Photo::query()
        ->where('id', '>', $id)
        ->orderBy('id')
        ->pluck('id')
        ->first();

    $prev = Photo::query()
        ->where('id', '<', $id)
        ->orderByDesc('id')
        ->pluck('id')
        ->first();

    return compact('next', 'prev');
}

/**
 * Возвращает количество статей в блогах
 *
 * @return string Количество статей
 */
function statsBlog(): string
{
    return Cache::remember('statArticles', 900, static function () {
        $stat = Article::query()->count();
        $totalNew = Article::query()->where('created_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество тем и сообщений в форуме
 *
 * @return string Количество тем и сообщений
 */
function statsForum(): string
{
    return Cache::remember('statForums', 600, static function () {
        $topics = Topic::query()->count();
        $posts = Post::query()->count();

        $totalNew = Post::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($topics) . '/' . formatShortNum($posts) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество сообщений в гостевой книге
 *
 * @return string Количество сообщений
 */
function statsGuestbook(): string
{
    return Cache::remember('statGuestbook', 600, static function () {
        $total = Guestbook::query()->count();

        $totalNew = Guestbook::query()
            ->where('active', true)
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество сообщений в админ-чате
 *
 * @return string Количество сообщений
 */
function statsChat(): string
{
    return Cache::remember('statChat', 3600, static function () {
        $total = Chat::query()->count();

        $totalNew = Chat::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает время последнего сообщения в админ-чате
 *
 * @return int Время сообщения
 */
function statsNewChat(): int
{
    return Chat::query()->max('created_at') ?? 0;
}

/**
 * Возвращает количество файлов в загруз-центре
 *
 * @return string Количество файлов
 */
function statsLoad(): string
{
    return Cache::remember('statLoads', 900, static function () {
        $totalLoads = Load::query()->sum('count_downs');

        $totalNew = Down::query()->where('active', 1)
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($totalLoads) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество новых файлов
 *
 * @return int Количество файлов
 */
function statsNewLoad(): int
{
    return Down::query()->where('active', 0)->count();
}

/**
 * Возвращает количество объявлений
 *
 * @return string Количество статей
 */
function statsBoard(): string
{
    return Cache::remember('statBoards', 900, static function () {
        $stat = formatShortNum(Item::query()->where('expires_at', '>', SITETIME)->count());
        $totalNew = Item::query()->where('updated_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Обфусцирует email
 *
 * @param string $email email
 *
 * @return string Обфусцированный email
 */
function cryptMail(string $email): string
{
    $output = '';
    $symbols = mb_str_split($email);

    foreach ($symbols as $symbol) {
        $output .= '&#' . ord($symbol) . ';';
    }

    return $output;
}

/**
 * Частично скрывает email
 */
function hideMail(string $email): string
{
    return preg_replace('/(?<=.).(?=.*@)/u', '*', $email);
}

/**
 * Возвращает статистику текущих голосований из кэш-файла
 *
 * @return string Статистика текущий голосований
 */
function statVotes(): string
{
    return Cache::remember('statVotes', 900, static function () {
        $votes = Vote::query()
            ->selectRaw('count(*) AS cnt, coalesce(sum(count), 0) AS sum')
            ->where('closed', 0)
            ->first();

        if (! $votes) {
            $votes->cnt = $votes->sum = 0;
        }

        return $votes->cnt . '/' . $votes->sum;
    });
}

/**
 * Возвращает дату последней новости из кэш-файла
 *
 * @return string Дата последней новости
 */
function statsNewsDate()
{
    $newsDate = Cache::remember('statNewsDate', 900, static function () {
        /** @var News $news */
        $news = News::query()->orderByDesc('created_at')->first();

        return $news->created_at ?? 0;
    });

    return $newsDate ? dateFixed($newsDate, 'd.m.Y') : 0;
}

/**
 * Возвращает последние новости
 *
 * @return HtmlString Новость
 */
function lastNews(): HtmlString
{
    $news = collect();

    if (setting('lastnews') > 0) {
        $news = Cache::remember('lastNews', 1800, static function () {
            return News::query()
                ->where('top', 1)
                ->orderByDesc('created_at')
                ->limit(setting('lastnews'))
                ->get();
        });
    }

    return new HtmlString(view('widgets/_news', compact('news')));
}

/**
 * Возвращает иконку расширения
 *
 * @param string $ext Расширение файла
 *
 * @return HtmlString Иконка
 */
function icons(string $ext): HtmlString
{
    switch ($ext) {
        case 'php':
            $ico = 'fa-regular fa-file-code';
            break;
        case 'ppt':
            $ico = 'fa-regular fa-file-powerpoint';
            break;
        case 'doc':
        case 'docx':
            $ico = 'fa-regular fa-file-word';
            break;
        case 'xls':
        case 'xlsx':
            $ico = 'fa-regular fa-file-excel';
            break;
        case 'txt':
        case 'css':
        case 'dat':
        case 'html':
        case 'htm':
            $ico = 'fa-regular fa-file-alt';
            break;
        case 'wav':
        case 'amr':
        case 'mp3':
        case 'mid':
            $ico = 'fa-regular fa-file-audio';
            break;
        case 'zip':
        case 'rar':
        case '7z':
        case 'gz':
            $ico = 'fa-regular fa-file-archive';
            break;
        case '3gp':
        case 'mp4':
            $ico = 'fa-regular fa-file-video';
            break;
        case 'jpg':
        case 'jpeg':
        case 'bmp':
        case 'wbmp':
        case 'gif':
        case 'png':
        case 'webp':
            $ico = 'fa-regular fa-file-image';
            break;
        case 'ttf':
            $ico = 'fa-solid fa-font';
            break;
        case 'pdf':
            $ico = 'fa-regular fa-file-pdf';
            break;
        case 'csv':
            $ico = 'fa-regular fa-file-csv';
            break;
        default:
            $ico = 'fa-regular fa-file';
    }

    return new HtmlString('<i class="' . $ico . '"></i>');
}

/**
 * Перемешивает элементы ассоциативного массива, сохраняя ключи
 *
 * @param array &$array Исходный массив, переданный по ссылке
 *
 * @return bool Флаг успешного выполнения операции
 */
function shuffleAssoc(array &$array): bool
{
    $keys = array_keys($array);

    shuffle($keys);
    $new = [];

    foreach ($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}

/**
 * Возвращает обрезанный текст с закрытием тегов
 */
function bbCodeTruncate(?string $text, int $words = 20, string $end = '...'): HtmlString
{
    $bbCode = new BBCode();

    $text = Str::words($text, $words, $end);
    $bbText = bbCode($bbCode->closeTags($text));

    return new HtmlString(preg_replace('/\[(.*?)]/', '', $bbText));
}

/**
 * Возвращает обрезанную до заданного количества букв строке
 *
 * @param HtmlString|string $value Исходная строка
 * @param int               $limit Максимальное количество символов в результате
 *
 * @return string Обрезанная строка
 */
function truncateString($value, int $limit = 100, string $end = '...'): string
{
    $value = strip_tags($value);

    if (mb_strlen($value, 'utf-8') <= $limit) {
        return $value;
    }

    $string = mb_substr($value, 0, $limit + 1);
    if ($lastSpace = mb_strrpos($string, ' ', 0, 'utf-8')) {
        $string = mb_substr($string, 0, $lastSpace, 'utf-8');
    } else {
        $string = mb_substr($string, 0, $limit, 'utf-8');
    }

    return trim($string) . $end;
}

/**
 * Возвращает обрезанную до заданного количества слов строке
 *
 * @param HtmlString|string $value Исходная строка
 * @param int               $words Максимальное количество слов в результате
 *
 * @return string Обрезанная строка
 */
function truncateWord($value, int $words = 20, string $end = '...'): string
{
    $value = strip_tags($value);

    return Str::words(trim($value), $words, $end);
}

/**
 * Возвращает обрезанную строку с удалением перевода строки
 *
 * @param HtmlString|string $value
 */
function truncateDescription($value, int $words = 20, string $end = ''): string
{
    $value = strip_tags(preg_replace('/\s+/', ' ', $value));

    return Str::words(trim($value), $words, $end);
}

/**
 * Get the number of words a string contains.
 *
 * @param string $string
 */
function wordCount($string): int
{
    return count(preg_split('/[^\s*+]+/u', $string));
}

/**
 * Возвращает код платной рекламы
 */
function getAdvertPaid(string $place): ?HtmlString
{
    $adverts = PaidAdvert::statAdverts();

    if (isset($adverts[$place])) {
        $links = [];
        foreach ($adverts[$place] as $advert) {
            $links[] = Arr::random($advert);
        }

        return new HtmlString(implode('<br>', $links));
    }

    return null;
}

/**
 * Возвращает код админской рекламы
 */
function getAdvertAdmin(): ?HtmlString
{
    $adverts = AdminAdvert::statAdverts();

    if ($adverts) {
        $result = Arr::random($adverts);

        return new HtmlString(view('adverts/_admin_links', compact('result')));
    }

    return null;
}

/**
 * Возвращает код пользовательской рекламы
 */
function getAdvertUser(): ?HtmlString
{
    $adverts = Advert::statAdverts();

    if ($adverts) {
        $total = count($adverts);
        $show = setting('rekusershow') > $total ? $total : setting('rekusershow');

        $links = Arr::random($adverts, $show);
        $result = implode('<br>', $links);

        return new HtmlString(view('adverts/_links', compact('result')));
    }

    return null;
}

/**
 * Выводит последние фотографии
 *
 * @param int $show Количество последних фотографий
 *
 * @return HtmlString Список фотографий
 */
function recentPhotos(int $show = 5): HtmlString
{
    $photos = Cache::remember('recentPhotos', 1800, static function () use ($show) {
        return Photo::query()
            ->orderByDesc('created_at')
            ->limit($show)
            ->with('files')
            ->get();
    });

    return new HtmlString(view('widgets/_photos', compact('photos')));
}

/**
 * Выводит последние темы форума
 *
 * @param int $show Количество последних тем форума
 *
 * @return HtmlString Список тем
 */
function recentTopics(int $show = 5): HtmlString
{
    $topics = Cache::remember('recentTopics', 300, static function () use ($show) {
        return Topic::query()
            ->orderByDesc('updated_at')
            ->limit($show)
            ->get();
    });

    return new HtmlString(view('widgets/_topics', compact('topics')));
}

/**
 * Выводит последние файлы в загрузках
 *
 * @param int $show Количество последних файлов в загрузках
 *
 * @return HtmlString Список файлов
 */
function recentDowns(int $show = 5): HtmlString
{
    $downs = Cache::remember('recentDowns', 600, static function () use ($show) {
        return Down::query()
            ->where('active', 1)
            ->orderByDesc('created_at')
            ->limit($show)
            ->with('category')
            ->get();
    });

    return new HtmlString(view('widgets/_downs', compact('downs')));
}

/**
 * Выводит последние статьи в блогах
 *
 * @param int $show Количество последних статей в блогах
 *
 * @return HtmlString Список статей
 */
function recentArticles(int $show = 5): HtmlString
{
    $articles = Cache::remember('recentArticles', 600, static function () use ($show) {
        return Article::query()
            ->orderByDesc('created_at')
            ->limit($show)
            ->get();
    });

    return new HtmlString(view('widgets/_articles', compact('articles')));
}

/**
 * Выводит последние объявления
 *
 * @param int $show Количество последних объявлений
 *
 * @return HtmlString Список объявлений
 */
function recentBoards(int $show = 5): HtmlString
{
    $items = Cache::remember('recentBoards', 600, static function () use ($show) {
        return Item::query()
            ->where('expires_at', '>', SITETIME)
            ->orderByDesc('created_at')
            ->limit($show)
            ->get();
    });

    return new HtmlString(view('widgets/_boards', compact('items')));
}

/**
 * Возвращает количество предложений и проблем
 *
 * @return string Количество предложений и проблем
 */
function statsOffers(): string
{
    return Cache::remember('offers', 600, static function () {
        $offers = Offer::query()->where('type', 'offer')->count();
        $problems = Offer::query()->where('type', 'issue')->count();

        return $offers . '/' . $problems;
    });
}

/**
 * Пересчитывает счетчики
 *
 * @param string $mode сервис счетчиков
 *
 * @return void
 */
function restatement(string $mode)
{
    switch ($mode) {
        case 'forums':
            DB::update('update topics set count_posts = (select count(*) from posts where topics.id = posts.topic_id)');
            DB::update('update forums set count_topics = (select count(*) from topics where forums.id = topics.forum_id)');
            DB::update('update forums set count_posts = (select coalesce(sum(count_posts), 0) from topics where forums.id = topics.forum_id)');
            break;

        case 'blogs':
            DB::update('update blogs set count_articles = (select count(*) from articles where blogs.id = articles.category_id)');
            DB::update('update articles set count_comments = (select count(*) from comments where relate_type = "' . Article::$morphName . '" and articles.id = comments.relate_id)');
            break;

        case 'loads':
            DB::update('update loads set count_downs = (select count(*) from downs where loads.id = downs.category_id and active = ?)', [1]);
            DB::update('update downs set count_comments = (select count(*) from comments where relate_type = "' . Down::$morphName . '" and downs.id = comments.relate_id)');
            break;

        case 'news':
            DB::update('update news set count_comments = (select count(*) from comments where relate_type = "' . News::$morphName . '" and news.id = comments.relate_id)');
            break;

        case 'photos':
            DB::update('update photos set count_comments = (select count(*) from comments where relate_type = "' . Photo::$morphName . '" and photos.id = comments.relate_id)');
            break;

        case 'offers':
            DB::update('update offers set count_comments = (select count(*) from comments where relate_type = "' . Offer::$morphName . '" and offers.id = comments.relate_id)');
            break;

        case 'boards':
            DB::update('update boards set count_items = (select count(*) from items where boards.id = items.board_id and items.expires_at > ' . SITETIME . ');');
            break;

        case 'votes':
            DB::update('update votes set count = (select coalesce(sum(result), 0) from voteanswer where votes.id = voteanswer.vote_id)');
            break;
    }
}

/**
 * Возвращает количество строк в файле
 *
 * @param string $file Путь к файлу
 *
 * @return int Количество строк
 */
function counterString(string $file): int
{
    $countLines = 0;
    if (file_exists($file)) {
        $countLines = count(file($file));
    }

    return $countLines;
}

/**
 * Форматирует вывод числа
 *
 * @param int|float $num число
 *
 * @return HtmlString Форматированное число
 */
function formatNum($num): HtmlString
{
    if ($num > 0) {
        $data = '<span style="color:#00aa00">+' . $num . '</span>';
    } elseif ($num < 0) {
        $data = '<span style="color:#ff0000">' . $num . '</span>';
    } else {
        $data = '<span>0</span>';
    }

    return new HtmlString($data);
}

/**
 * Форматирует вывод числа
 */
function formatShortNum(int $num): float|int|string
{
    if (! is_numeric($num)) {
        return '0b';
    }

    if ($num > 1000000000000) {
        return round($num / 1000000000000, 1) . 'T';
    }

    if ($num > 1000000000) {
        return round($num / 1000000000, 1) . 'B';
    }

    if ($num > 1000000) {
        return round($num / 1000000, 1) . 'M';
    }

    if ($num > 1000) {
        return round($num / 1000, 1) . 'K';
    }

    return $num;
}

/**
 * Обрабатывает и уменьшает изображение
 *
 * @param string|null $path   Путь к изображению
 * @param array       $params Параметры изображения
 *
 * @return array Обработанные параметры
 */
function resizeProcess(?string $path, array $params = []): array
{
    if (empty($params['alt'])) {
        $params['alt'] = basename($path);
    }

    if (empty($params['class'])) {
        $params['class'] = 'img-fluid';
    }

    if (! file_exists(public_path($path)) || ! is_file(public_path($path))) {
        return [
            'path'   => '/assets/img/images/photo.png',
            'source' => false,
            'params' => $params,
        ];
    }

    [$width, $height] = getimagesize(public_path($path));

    if ($width <= setting('previewsize') && $height <= setting('previewsize')) {
        return [
            'path'   => $path,
            'source' => $path,
            'params' => $params,
        ];
    }

    $thumb = ltrim(str_replace('/', '_', $path), '_');

    if (! file_exists(public_path('uploads/thumbnails/' . $thumb))) {
        $imageManager = app(ImageManager::class);
        $image = $imageManager->read(public_path($path));
        $image->scaleDown(setting('previewsize'), setting('previewsize'));
        $image->save(public_path('uploads/thumbnails/' . $thumb));
    }

    return [
        'path'   => '/uploads/thumbnails/' . $thumb,
        'source' => $path,
        'params' => $params,
    ];
}

/**
 * Возвращает уменьшенное изображение
 *
 * @param string|null $path   Путь к изображению
 * @param array       $params Параметры изображения
 *
 * @return HtmlString Уменьшенное изображение
 */
function resizeImage(?string $path, array $params = []): HtmlString
{
    $image = resizeProcess($path, $params);

    $strParams = [];
    foreach ($image['params'] as $key => $param) {
        $strParams[] = $key . '="' . check($param) . '"';
    }

    $strParams = implode(' ', $strParams);

    return new HtmlString('<img src="' . $image['path'] . '" data-source="' . $image['source'] . '" ' . $strParams . '>');
}

/**
 * Удаляет директорию рекурсивно
 *
 * @param string $dir путь к директории
 *
 * @return void
 */
function deleteDir(string $dir)
{
    if (file_exists($dir)) {
        if ($files = glob($dir . '/*')) {
            foreach ($files as $file) {
                is_dir($file) ? deleteDir($file) : unlink($file);
            }
        }
        rmdir($dir);
    }
}

/**
 * Удаляет файл
 *
 * @param string $path Путь к файлу
 */
function deleteFile(string $path): bool
{
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }

    if (in_array(getExtension($path), explode(',', setting('image_extensions')), true)) {
        $thumb = ltrim(str_replace([public_path(), '/'], ['', '_'], $path), '_');
        $thumb = public_path('uploads/thumbnails/' . $thumb);

        if (file_exists($thumb) && is_file($thumb)) {
            unlink($thumb);
        }
    }

    return true;
}

/**
 * Отправляет уведомление об упоминании в приват
 *
 * @param string $text  Текст сообщения
 * @param string $url   Путь к странице
 * @param string $title Название страницу
 *
 * @return void
 */
function sendNotify(string $text, string $url, string $title)
{
    /* $parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $text); */
    preg_match_all('/(?<=^|\s|=)@([\w\-]+)/', $text, $matches);

    if (! empty($matches[1])) {
        $login = getUser('login') ?? setting('guestsuser');
        $usersAnswer = array_unique(array_diff($matches[1], [$login]));

        foreach ($usersAnswer as $user) {
            $user = getUserByLogin($user);

            if ($user && $user->notify) {
                $notify = textNotice('notify', compact('login', 'url', 'title', 'text'));
                $user->sendMessage(null, $notify);
            }
        }
    }
}

/**
 * Возвращает приватное сообщение
 *
 * @param string $type    Тип сообщения
 * @param array  $replace Массив заменяемых параметров
 *
 * @return string Сформированный текст
 */
function textNotice(string $type, array $replace = []): string
{
    /** @var Notice $message */
    $message = Notice::query()->where('type', $type)->first();

    if (! $message) {
        return __('main.text_missing');
    }

    foreach ($replace as $key => $val) {
        $message->text = str_replace('%' . $key . '%', $val, $message->text);
    }

    return $message->text;
}

/**
 * Возвращает блок статистики производительности
 *
 * @return HtmlString|null Статистика производительности
 */
function performance(): ?HtmlString
{
    if (isAdmin() && setting('performance')) {
        $queries = getQueryLog();
        $timeQueries = array_sum(array_column($queries, 'time'));

        return new HtmlString(view('app/_performance', compact('queries', 'timeQueries')));
    }

    return null;
}

/**
 * Очистка кеш-файлов
 *
 *
 * @return bool Результат выполнения
 */
function clearCache(array|string|null $keys = null): bool
{
    if ($keys) {
        if (! is_array($keys)) {
            $keys = [$keys];
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return true;
    }

    Cache::flush();

    return true;
}

/**
 * Возвращает текущую страницу
 *
 *
 * @return string|null Текущая страница
 */
function returnUrl(?string $url = null): ?string
{
    $request = request();

    if ($request->is('/', 'login', 'register', 'recovery', 'restore', 'ban', 'closed')) {
        return null;
    }

    $query = $request->has('return') ? $request->input('return') : $request->path();

    return '?return=' . urlencode($url ?? '/' . $query);
}

/**
 * Saves error logs
 *
 *
 * @return void
 */
function saveErrorLog(int $code, ?string $message = null)
{
    $errorCodes = [401, 403, 404, 405, 419, 429, 500, 503, 666];

    if (setting('errorlog') && in_array($code, $errorCodes, true)) {
        Error::query()->create([
            'code'       => $code,
            'request'    => utfSubstr(request()->getRequestUri(), 0, 250),
            'referer'    => utfSubstr(request()->header('referer'), 0, 250),
            'user_id'    => getUser('id'),
            'message'    => utfSubstr($message, 0, 250),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }
}

/**
 * Возвращает ошибку
 *
 * @param string|array $errors ошибки
 *
 * @return HtmlString Сформированный блок с ошибкой
 */
function showError(string|array $errors): HtmlString
{
    $errors = (array) $errors;

    return new HtmlString(view('app/_error', compact('errors')));
}

function getCaptcha(): HtmlString
{
    return new HtmlString(view('app/_captcha'));
}

/**
 * Сохраняет flash уведомления
 *
 * @param string $status  Статус уведомления
 * @param mixed  $message Массив или текст с уведомлениями
 *
 * @return void
 *
 * @deprecated since 10.1 - Use redirect->with('success', 'Message') or session()->flash()
 */
function setFlash(string $status, $message)
{
    session(['flash.' . $status => $message]);
}

/**
 * Сохраняет POST данные введенных пользователем
 *
 * @param array $data Массив полей
 *
 * @deprecated since 10.1
 */
function setInput(array $data)
{
    session()->flash('input', json_encode($data));
}

/**
 * Возвращает значение из POST данных
 *
 * @param string $key Имя поля
 *
 * @return mixed Сохраненное значение
 *
 * @deprecated since 10.1 - Use old('field', 'default');
 */
function getInput(string $key, $default = null): mixed
{
    if (session()->missing('input')) {
        return $default;
    }

    $input = json_decode(session('input', []), true);

    return Arr::get($input, $key, $default);
}

/**
 * Подсвечивает блок с полем для ввода сообщения
 *
 * @param string $field Имя поля
 *
 * @return string CSS класс ошибки
 */
function hasError(string $field): string
{
    // Новая валидация
    if (session('errors')) {
        /** @var ViewErrorBag $errors */
        $errors = session('errors');

        return $errors->has($field) ? ' is-invalid' : ' is-valid';
    }

    $isValid = session('flash.danger') ? ' is-valid' : '';

    return session('flash.danger.' . $field) ? ' is-invalid' : $isValid;
}

/**
 * Возвращает блок с текстом ошибки
 *
 * @param string $field Имя поля
 *
 * @return string|null Блоки ошибки
 */
function textError(string $field): ?string
{
    // Новая валидация
    if (session('errors')) {
        /** @var ViewErrorBag $errors */
        $errors = session('errors');

        return $errors->first($field);
    }

    return session('flash.danger.' . $field);
}

/**
 * Отправляет уведомления на email
 *
 *
 * @return bool Результат отправки
 */
function sendMail(string $view, array $data): bool
{
    try {
        Mail::send($view, $data, static function (Message $message) use ($data) {
            $message->subject($data['subject'])
                ->to($data['to'])
                ->from(config('mail.from.address'), config('mail.from.name'));

            if (isset($data['from'])) {
                [$fromEmail, $fromName] = $data['from'];
                $message->replyTo($fromEmail, $fromName);
            }

            if (isset($data['unsubscribe'])) {
                $headers = $message->getHeaders();
                $headers->addTextHeader(
                    'List-Unsubscribe',
                    '<' . config('app.url') . '/unsubscribe?key=' . $data['unsubscribe'] . '>'
                );
            }
        });
    } catch (Exception) {
        return false;
    }

    return true;
}

/**
 * Возвращает расширение файла
 *
 * @param string $filename Имя файла
 *
 * @return string расширение
 */
function getExtension(string $filename): string
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

/**
 * Возвращает имя файла без расширения
 *
 * @param string $filename Имя файла
 *
 * @return string Имя без расширения
 */
function getBodyName(string $filename): string
{
    return pathinfo($filename, PATHINFO_FILENAME);
}

/**
 * Склоняет числа
 *
 * @param int   $num   Число
 * @param mixed $forms Склоняемые слова (один, два, много)
 *
 * @return string Форматированная строка
 */
function plural(int $num, mixed $forms): string
{
    if (! is_array($forms)) {
        $forms = explode(',', $forms);
    }

    if (count($forms) === 1) {
        return $num . ' ' . $forms[0];
    }

    if ($num % 100 > 10 && $num % 100 < 15) {
        return $num . ' ' . $forms[2];
    }

    if ($num % 10 === 1) {
        return $num . ' ' . $forms[0];
    }

    if ($num % 10 > 1 && $num % 10 < 5) {
        return $num . ' ' . $forms[1];
    }

    return $num . ' ' . $forms[2];
}

/**
 * Обрабатывает BB-код
 *
 * @param string|null $text  Необработанный текст
 * @param bool        $parse Обрабатывать или вырезать код
 *
 * @return HtmlString Обработанный текст
 */
function bbCode(?string $text, bool $parse = true): HtmlString
{
    $bbCode = new BBCode();
    $checkText = check($text);

    if (! $parse) {
        return new HtmlString($bbCode->clear($checkText));
    }

    $parseText = $bbCode->parse($checkText);
    $parseText = $bbCode->parseStickers($parseText);

    return new HtmlString($parseText);
}

/**
 * Определяет IP пользователя
 *
 * @return string IP пользователя
 */
function getIp(): string
{
    return (new CloudFlare(request()))->ip();
}

/**
 * Определяет браузер
 *
 *
 * @return string Браузер и версия браузера
 */
function getBrowser(?string $userAgent = null): string
{
    $browser = new Browser();
    if ($userAgent) {
        $browser->setUserAgent($userAgent);
    }

    $brow = $browser->getBrowser();
    $version = implode('.', array_slice(explode('.', $browser->getVersion()), 0, 2));

    $browser = $version === Browser::VERSION_UNKNOWN ? $brow : $brow . ' ' . $version;

    return mb_substr($browser, 0, 25, 'utf-8');
}

/**
 * Является ли пользователь администратором
 *
 * @param string|null $level Уровень доступа
 *
 * @return bool Является ли пользователь администратором
 */
function isAdmin(?string $level = null): bool
{
    return access($level ?? User::EDITOR);
}

/**
 * Имеет ли пользователь доступ по уровню
 *
 * @param string $level Уровень доступа
 *
 * @return bool Разрешен ли доступ
 */
function access(string $level): bool
{
    $access = array_flip(User::ALL_GROUPS);

    return getUser()
        && isset($access[$level], $access[getUser('level')])
        && $access[getUser('level')] <= $access[$level];
}

/**
 * Возвращает текущего пользователя
 */
function getUserFromSession(): ?User
{
    if (session()->has(['id', 'password'])) {
        $user = getUserById(session('id'));

        if ($user && session('password') === $user->password) {
            return $user;
        }
    }

    return null;
}

/**
 * Возвращает объект пользователя по логину
 *
 * @param string|null $login Логин пользователя
 */
function getUserByLogin(?string $login): Builder|User|null
{
    return User::query()->where('login', $login)->first();
}

/**
 * Возвращает объект пользователя по id
 *
 * @param int|null $id ID пользователя
 */
function getUserById(?int $id): Builder|User|null
{
    return User::query()->find($id);
}

/**
 * Возвращает объект пользователя по токену
 *
 * @param string $token Логин пользователя
 */
function getUserByToken(string $token): Builder|User|null
{
    return User::query()->where('apikey', $token)->first();
}

/**
 * Возвращает объект пользователя по логину или email
 *
 * @param string|null $login Логин или email пользователя
 */
function getUserByLoginOrEmail(?string $login): Builder|User|null
{
    $field = strpos($login, '@') ? 'email' : 'login';

    return User::query()->where($field, $login)->first();
}

/**
 * Возвращает данные пользователя по ключу
 *
 * @param string|null $key Ключ массива
 *
 * @return User|null|mixed
 */
function getUser(?string $key = null): mixed
{
    static $user;

    if (! $user) {
        $user = getUserFromSession();
    }

    return $key ? ($user->$key ?? null) : $user;
}

/**
 * Разбивает данные по страницам
 */
function paginate(array|Collection $items, int $perPage, array $appends = []): LengthAwarePaginator
{
    $data = $items instanceof Collection ? $items : Collection::make($items);

    $currentPage = LengthAwarePaginator::resolveCurrentPage();

    $collection = new LengthAwarePaginator(
        $data->forPage($currentPage, $perPage),
        $data->count(),
        $perPage,
        $currentPage
    );

    $collection->setPath(request()->url());
    $collection->appends($appends);

    return $collection;
}

/**
 * Разбивает данные по страницам
 */
function simplePaginate(array|Collection $items, int $perPage, array $appends = []): Paginator
{
    $data = $items instanceof Collection ? $items : Collection::make($items);

    $currentPage = Paginator::resolveCurrentPage();

    $collection = new Paginator(
        $data->slice(max(0, ($currentPage - 1) * $perPage)),
        $perPage
    );

    $collection->setPath(request()->url());
    $collection->appends($appends);

    return $collection;
}

/**
 * Возвращает сформированный код base64 картинки
 *
 * @param string $path   Путь к картинке
 * @param array  $params Параметры
 *
 * @return HtmlString Сформированный код
 */
function imageBase64(string $path, array $params = []): HtmlString
{
    $type = getExtension($path);
    $data = file_get_contents($path);

    if (! isset($params['class'])) {
        $params['class'] = 'img-fluid';
    }

    if (empty($params['alt'])) {
        $params['alt'] = basename($path);
    }

    $strParams = [];
    foreach ($params as $key => $param) {
        $strParams[] = $key . '="' . $param . '"';
    }

    $strParams = implode(' ', $strParams);

    return new HtmlString('<img src="data:image/' . $type . ';base64,' . base64_encode($data) . '"' . $strParams . '>');
}

/**
 * Выводит прогресс-бар
 */
function progressBar(int $percent, float|int|string|null $title = null): HtmlString
{
    if (! $title) {
        $title = $percent . '%';
    }

    return new HtmlString(view('app/_progressbar', compact('percent', 'title')));
}

/**
 * Возвращает форматированный список запросов
 */
function getQueryLog(): array
{
    $queries = DB::getQueryLog();

    $formattedQueries = [];

    foreach ($queries as $query) {
        foreach ($query['bindings'] as $key => $binding) {
            if (is_string($binding)) {
                $query['bindings'][$key] = ctype_print($binding) ? "'$binding'" : '[binary]';
            }

            $query['bindings'][$key] = $binding ?? 'null';
        }

        $sql = str_replace(['%', '?'], ['%%', '%s'], $query['query']);
        $sql = vsprintf($sql, $query['bindings']);

        $formattedQueries[] = ['query' => $sql, 'time' => $query['time']];
    }

    return $formattedQueries;
}

/**
 * Выводит список забаненных ip
 *
 * @param bool $clear Нужно ли сбросить кеш
 *
 * @return array Массив IP
 */
function ipBan(bool $clear = false): array
{
    if ($clear) {
        clearCache('ipBan');
    }

    return Cache::rememberForever('ipBan', static function () {
        return Ban::query()->get()->pluck('id', 'ip')->all();
    });
}

/**
 * Возвращает настройки сайта по ключу
 *
 * @param string|null $key     Ключ массива
 * @param mixed|null  $default Значение по умолчанию
 *
 * @return mixed Данные
 */
function setting(?string $key = null, mixed $default = null): mixed
{
    static $settings;

    if (! $settings) {
        $settings = Setting::getSettings();
    }

    return $key ? ($settings[$key] ?? $default) : $settings;
}

/**
 * Возвращает имя сайта из ссылки
 *
 * @param string $url Ссылка на сайт
 *
 * @return string Имя сайта
 */
function siteDomain(string $url): string
{
    return parse_url(strtolower($url), PHP_URL_HOST);
}

/**
 * Получает версию
 */
function parseVersion(string $version): string
{
    $ver = explode('.', strtok($version, '-'));

    return $ver[0] . '.' . $ver[1] . '.' . ($ver[2] ?? 0);
}

/**
 * Проверяет captcha
 */
function captchaVerify(): bool
{
    $request = request();

    if (setting('captcha_type') === 'recaptcha_v2') {
        $recaptcha = new ReCaptcha(setting('recaptcha_private'));

        $response = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
            ->verify($request->input('g-recaptcha-response'), getIp());

        return $response->isSuccess();
    }

    if (setting('captcha_type') === 'recaptcha_v3') {
        $recaptcha = new ReCaptcha(setting('recaptcha_private'));

        $response = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
            ->setScoreThreshold(0.5)
            ->verify($request->input('protect'), getIp());

        return $response->isSuccess();
    }

    if (in_array(setting('captcha_type'), ['graphical', 'animated'], true)) {
        return strtolower($request->input('protect')) === $request->session()->get('protect');
    }

    return false;
}

/**
 * Возвращает уникальное имя
 */
function uniqueName(?string $extension = null): string
{
    if ($extension) {
        $extension = '.' . $extension;
    }

    return str_replace('.', '', uniqid('', true)) . $extension;
}

/**
 * Возвращает курсы валют
 */
function getCourses(): ?HtmlString
{
    $courses = Cache::remember('courses', 3600, static function () {
        try {
            $client = new Client(['timeout' => 3.0]);
            $response = $client->get('//www.cbr-xml-daily.ru/daily_json.js');

            $content = json_decode($response->getBody()->getContents(), true);
        } catch (Exception) {
            $content = null;
        }

        return $content;
    });

    return new HtmlString(view('app/_courses', compact('courses')));
}
