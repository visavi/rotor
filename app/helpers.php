<?php

use App\Models\AdminAdvert;
use App\Models\PaidAdvert;
use App\Classes\{BBCode, Calendar, Metrika, CloudFlare, Mix};
use App\Models\Antimat;
use App\Models\Ban;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Article;
use App\Models\Item;
use App\Models\Load;
use App\Models\Chat;
use App\Models\Counter;
use App\Models\Down;
use App\Models\Guestbook;
use App\Models\Invite;
use App\Models\Error;
use App\Models\News;
use App\Models\Notice;
use App\Models\Offer;
use App\Models\Online;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Advert;
use App\Models\Setting;
use App\Models\Sticker;
use App\Models\Spam;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManagerStatic as Image;
use josegonzalez\Dotenv\Loader;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Форматирует вывод времени из секунд
 *
 * @param int $time секунды
 *
 * @return string форматированный вывод
 */
function makeTime(int $time): string
{
    $format = $time < 3600 ? 'i:s' : 'H:i:s';

    return gmdate($format, $time);
}

/**
 * Форматирует время с учетом часовых поясов
 *
 * @param int|null $timestamp секунды
 * @param string   $format    формат времени
 * @param bool     $original  формат без изменения
 *
 * @return string форматированный вывод
 */
function dateFixed(?int $timestamp, string $format = 'd.m.Y / H:i', bool $original = false): string
{
    if (! is_numeric($timestamp)) {
        $timestamp = SITETIME;
    }

    $shift     = getUser('timezone') * 3600;
    $dateStamp = date($format, $timestamp + $shift);

    if ($original) {
        return $dateStamp;
    }

    $today     = date('d.m.Y', SITETIME + $shift);
    $yesterday = date('d.m.Y', strtotime('-1 day', SITETIME + $shift));

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
 * @return string конвертированная строка
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
 * @return string преобразованная строка
 */
function utfLower(string $str): string
{
    return mb_strtolower($str, 'utf-8');
}

/**
 * Обрезает строку
 *
 * @param mixed    $str    строка
 * @param int      $start  начало позиции
 * @param int|null $length конец позиции
 *
 * @return string обрезанная строка
 */
function utfSubstr($str, int $start, $length = null): string
{
    if (! $length) {
        $length = utfStrlen($str);
    }

    return mb_substr($str, $start, $length, 'utf-8');
}

/**
 * Возвращает длину строки
 *
 * @param mixed $str строка
 *
 * @return int длина строка
 */
function utfStrlen($str): int
{
    return mb_strlen($str, 'utf-8');
}

/**
 * Определяет является ли кодировка utf-8
 *
 * @param string $str строка
 *
 * @return bool
 */
function isUtf(string $str): bool
{
    return mb_check_encoding($str, 'utf-8');
}

/**
 * Преобразует специальные символы в HTML-сущности
 *
 * @param mixed $string       строка или массив строк
 * @param bool  $doubleEncode преобразовывать существующие html-сущности
 *
 * @return array|string обработанные данные
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
 * @return int обработанные данные
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
 * @return array|null обработанные данные
 */
function intar($numbers): ?array
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
 * @param int $bytes     размер в байтах
 * @param int $precision кол. символов после запятой
 *
 * @return string форматированный вывод размера
 */
function formatSize(int $bytes, int $precision = 2): string
{
    $units = ['B', 'Kb', 'Mb', 'Gb', 'Tb'];
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1000));
    $pow   = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . $units[$pow];
}

/**
 * Возвращает размер файла человекочитаемом формате
 *
 * @param string $file путь к файлу
 *
 * @return int|string размер в читаемом формате
 */
function formatFileSize(string $file)
{
    if (file_exists($file) && is_file($file)) {
        return formatSize(filesize($file));
    }

    return formatSize(0);
}

/**
 * Возвращает время в человекочитаемом формате
 *
 * @param int $time   кол. секунд timestamp
 * @param int $crumbs кол. элементов
 *
 * @return string время в читаемом формате
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
        $time  %= $seconds;

        if ($format >= 1) {
            $return[] = plural($format, $unit);
        }
    }

    return implode(' ', array_slice($return, 0, $crumbs));
}

/**
 * Очищает строку от мата по базе слов
 *
 * @param string $str строка
 *
 * @return string обработанная строка
 */
function antimat(string $str): string
{
    return Antimat::replace($str);
}

/**
 * Возвращает рейтинг в виде звезд
 *
 * @param int|float $rating рейтинг
 *
 * @return HtmlString преобразованный рейтинг
 */
function ratingVote($rating): HtmlString
{
    $rating = round($rating / 0.5) * 0.5;

    $full_stars = floor($rating);
    $half_stars = ceil($rating - $full_stars);
    $empty_stars = 5 - $full_stars - $half_stars;

    $output = '<div class="star-rating fa-lg text-danger">';
    $output .= str_repeat('<i class="fas fa-star"></i>', $full_stars);
    $output .= str_repeat('<i class="fas fa-star-half-alt"></i>', $half_stars);
    $output .= str_repeat('<i class="far fa-star"></i>', $empty_stars);
    $output .= '( ' . $rating .' )</div>';

    return new HtmlString($output);
}

/**
 * Возвращает календарь
 *
 * @param int $time
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
 * @return array массив данных
 */
function statsOnline(): array
{
    return Cache::remember('online', 60, static function () {
        $users  = Online::query()->distinct('user_id')->whereNotNull('user_id')->count();
        $guests = Online::query()->whereNull('user_id')->count();

        $metrika = new Metrika();
        $metrika->getCounter($users + $guests);

        return [$users, $guests];
    });
}

/**
 * Возвращает количество пользователей онлайн
 *
 * @return HtmlString|null
 */
function showOnline(): ?HtmlString
{
    $online = statsOnline();

    if (setting('onlines')) {
        return new HtmlString(view('app/_online', compact('online')));
    }

    return null;
}

/**
 * Возвращает статистику посещений
 *
 * @return array статистика посещений
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
 *
 * @return HtmlString|null
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
 * @return string количество пользователей
 */
function statsUsers(): string
{
    return Cache::remember('statUsers', 1800, static function () {
        $startDay = mktime(0, 0, 0, dateFixed(SITETIME, 'n', true));

        $stat = User::query()->count();
        $new  = User::query()->where('created_at', '>', $startDay)->count();

        if ($new) {
            $stat .= '/+' . $new;
        }

        return $stat;
    });
}

/**
 * Возвращает количество администраторов
 *
 * @return int количество администраторов
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
 * @return int количество жалоб
 */
function statsSpam(): int
{
    return Spam::query()->count();
}

/**
 * Возвращает количество забанненых пользователей
 *
 * @return int количество забаненных
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
 * @return int количество записей
 */
function statsBanHist(): int
{
    return Banhist::query()->count();
}

/**
 * Возвращает количество ожидающих подтверждения регистрации
 *
 * @return int количество ожидающих
 */
function statsRegList(): int
{
    return User::query()->where('level', User::PENDED)->count();
}

/**
 * Возвращает количество забаненных по IP
 *
 * @return int количество забаненных
 */
function statsIpBanned(): int
{
    return Ban::query()->count();
}

/**
 * Возвращает количество фотографий в галерее
 *
 * @return string количество фотографий
 */
function statsPhotos(): string
{
    return Cache::remember('statPhotos', 900, static function () {
        $stat     = Photo::query()->count();
        $totalNew = Photo::query()->where('created_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество новостей
 *
 * @return string количество новостей
 */
function statsNews(): string
{
    return Cache::remember('statNews', 300, static function () {
        $total = News::query()->count();

        $totalNew = News::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество записей в черном списке
 *
 * @return string количество записей
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
 * @return int количество записей
 */
function statsAntimat(): int
{
    return Antimat::query()->count();
}

/**
 * Возвращает количество стикеров
 *
 * @return int количество стикеров
 */
function statsStickers(): int
{
    return Sticker::query()->count();
}

/**
 * Возвращает дату последнего сканирования сайта
 *
 * @return int|string дата последнего сканирования
 */
function statsChecker()
{
    if (file_exists(STORAGE . '/caches/checker.php')) {
        return dateFixed(filemtime(STORAGE . '/caches/checker.php'), 'd.m.Y');
    }

    return 0;
}

/**
 * Возвращает количество приглашений на регистрацию
 *
 * @return string количество приглашений
 */
function statsInvite(): string
{
    $invited     = Invite::query()->where('used', 0)->count();
    $usedInvited = Invite::query()->where('used', 1)->count();

    return $invited . '/' . $usedInvited;
}

/**
 * Возвращает следующею и предыдущую фотографию в галерее
 *
 * @param int $id Id фотографий
 *
 * @return array|null массив данных
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
 * @return string количество статей
 */
function statsBlog(): string
{
    return Cache::remember('statArticles', 900, static function () {
        $stat     = Article::query()->count();
        $totalNew = Article::query()->where('created_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество тем и сообщений в форуме
 *
 * @return string количество тем и сообщений
 */
function statsForum(): string
{
    return Cache::remember('statForums', 600, static function () {
        $topics = Topic::query()->count();
        $posts  = Post::query()->count();

        $totalNew = Post::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($topics) . '/' . formatShortNum($posts) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество сообщений в гостевой книге
 *
 * @return string количество сообщений
 */
function statsGuestbook(): string
{
    return Cache::remember('statGuestbook', 600, static function () {
        $total = Guestbook::query()->count();

        $totalNew = Guestbook::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество сообщений в админ-чате
 *
 * @return string количество сообщений
 */
function statsChat(): string
{
    return Cache::remember('statChat', 3600, static function () {
        $total = Chat::query()->count();

        $totalNew = Chat::query()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает время последнего сообщения в админ-чате
 *
 * @return int время сообщения
 */
function statsNewChat(): int
{
    return Chat::query()->max('created_at') ?? 0;
}

/**
 * Возвращает количество файлов в загруз-центре
 *
 * @return string количество файлов
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
 * @return int количество файлов
 */
function statsNewLoad(): int
{
    return Down::query()->where('active', 0)->count();
}

/**
 * Возвращает количество объявлений
 *
 * @return string количество статей
 */
function statsBoard(): string
{
    return Cache::remember('statBoards', 900, static function () {
        $stat      = formatShortNum(Item::query()->where('expires_at', '>', SITETIME)->count());
        $totalNew  = Item::query()->where('updated_at', '>', strtotime('-1 day', SITETIME))->count();

        return formatShortNum($stat) . ($totalNew  ? '/+' . $totalNew : '');
    });
}

/**
 * Обфусцирует email
 *
 * @param string $email email
 *
 * @return string обфусцированный email
 */
function cryptMail(string $email): string
{
    $output  = '';
    $symbols = str_split($email);

    foreach ($symbols as $symbol) {
        $output  .= '&#' . ord($symbol) . ';';
    }

    return $output;
}

/**
 * Частично скрывает email
 *
 * @param string $email
 *
 * @return string
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
 * @return HtmlString новость
 */
function lastNews(): HtmlString
{
    $news = null;

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
 * @param string $ext расширение файла
 *
 * @return HtmlString иконка
 */
function icons(string $ext): HtmlString
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
        default:
            $ico = 'file';
    }
    return new HtmlString('<i class="far fa-' . $ico . '"></i>');
}

/**
 * Перемешивает элементы ассоциативного массива, сохраняя ключи
 *
 * @param array &$array Исходный массив, переданный по ссылке
 *
 * @return bool Флаг успешного выполнения операции
 */
function shuffleAssoc(array &$array)
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
 * Закрывает bb-теги
 *
 * @param string $html
 *
 * @return string
 */
function closeTags(string $html): string
{
    preg_match_all('#\[([a-z]+)(?:=.*)?(?<![/])\]#iU', $html, $result);
    $openTags = $result[1];

    preg_match_all('#\[/([a-z]+)\]#iU', $html, $result);
    $closedTags = $result[1];

    if ($openTags === $closedTags) {
        return $html;
    }

    $diff = array_diff_assoc($openTags, $closedTags);
    $tags = array_reverse($diff);

    foreach ($tags as $key => $value) {
        $html .= '[/'. $value .']';
    }

    return $html;
}

/**
 * Возвращает обрезанный текст с закрытием тегов
 *
 * @param string $value
 * @param int    $words
 * @param string $end
 *
 * @return HtmlString
 */
function bbCodeTruncate(string $value, int $words = 20, string $end = '...'): HtmlString
{
    $value  = Str::words($value, $words, $end);
    $bbText = bbCode(closeTags($value));

    return new HtmlString(preg_replace('/\[(.*?)\]/', '', $bbText));
}

/**
 * Возвращает обрезанную до заданного количества букв строке
 *
 * @param HtmlString|string $value Исходная строка
 * @param int               $limit Максимальное количество символов в результате
 * @param string            $end
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
 * @param string            $end
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
 * @param int               $words
 * @param string            $end
 *
 * @return string
 */
function truncateDescription($value, int $words = 20, string $end = ''): string
{
    $value = strip_tags(preg_replace('/\s+/', ' ', $value));

    return Str::words(trim($value), $words, $end);
}

/**
 * Возвращает код платной рекламы
 *
 * @param string $place
 *
 * @return HtmlString|null
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
 *
 * @return HtmlString|null
 */
function getAdvertAdmin(): ?HtmlString
{
    $adverts = AdminAdvert::statAdverts();

    if ($adverts) {
        $result  = Arr::random($adverts);

        return new HtmlString(view('adverts/_admin_links', compact('result')));
    }

    return null;
}

/**
 * Возвращает код пользовательской рекламы
 *
 * @return HtmlString|null
 */
function getAdvertUser(): ?HtmlString
{
    $adverts = Advert::statAdverts();

    if ($adverts) {
        $total = count($adverts);
        $show  = setting('rekusershow') > $total ? $total : setting('rekusershow');

        $links  = Arr::random($adverts, $show);
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
 * @return string количество предложений и проблем
 */
function statsOffers(): string
{
    return Cache::remember('offers', 600, static function () {
        $offers   = Offer::query()->where('type', 'offer')->count();
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
            DB::connection()->update('update topics set count_posts = (select count(*) from posts where topics.id = posts.topic_id)');
            DB::connection()->update('update forums set count_topics = (select count(*) from topics where forums.id = topics.forum_id)');
            DB::connection()->update('update forums set count_posts = (select coalesce(sum(count_posts), 0) from topics where forums.id = topics.forum_id)');
            break;

        case 'blogs':
            DB::connection()->update('update blogs set count_articles = (select count(*) from articles where blogs.id = articles.category_id)');
            DB::connection()->update('update articles set count_comments = (select count(*) from comments where relate_type = "' . Article::$morphName . '" and articles.id = comments.relate_id)');
            break;

        case 'loads':
            DB::connection()->update('update loads set count_downs = (select count(*) from downs where loads.id = downs.category_id and active = ?)', [1]);
            DB::connection()->update('update downs set count_comments = (select count(*) from comments where relate_type = "' . Down::$morphName . '" and downs.id = comments.relate_id)');
            break;

        case 'news':
            DB::connection()->update('update news set count_comments = (select count(*) from comments where relate_type = "' . News::$morphName . '" and news.id = comments.relate_id)');
            break;

        case 'photos':
            DB::connection()->update('update photos set count_comments = (select count(*) from comments where relate_type = "' . Photo::$morphName . '" and photos.id = comments.relate_id)');
            break;

        case 'offers':
            DB::connection()->update('update offers set count_comments = (select count(*) from comments where relate_type = "' . Offer::$morphName . '" and offers.id = comments.relate_id)');
            break;

        case 'boards':
            DB::connection()->update('update boards set count_items = (select count(*) from items where boards.id = items.board_id and items.expires_at > ' . SITETIME . ');');
            break;

        case 'votes':
            DB::connection()->update('update votes set count = (select coalesce(sum(result), 0) from voteanswer where votes.id = voteanswer.vote_id)');
            break;
    }
}

/**
 * Возвращает количество строк в файле
 *
 * @param string $file путь к файлу
 *
 * @return int количество строк
 */
function counterString(string $file)
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
 * @return HtmlString форматированное число
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
 *
 * @param int $num
 *
 * @return string
 */
function formatShortNum(int $num)
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
 * @param string|null $path   путь к изображению
 * @param array  $params параметры изображения
 *
 * @return array обработанные параметры
 */
function resizeProcess(?string $path, array $params = []): array
{
    if (empty($params['alt'])) {
        $params['alt'] = basename($path);
    }

    if (empty($params['class'])) {
        $params['class'] = 'img-fluid';
    }

    if (! file_exists(HOME . $path) || ! is_file(HOME . $path)) {
        return [
            'path'   => '/assets/img/images/photo.png',
            'source' => false,
            'params' => $params,
        ];
    }

    [$width, $height] = getimagesize(HOME . $path);

    if ($width <= setting('previewsize') && $height <= setting('previewsize')) {
        return [
            'path'   => $path,
            'source' => $path,
            'params' => $params,
        ];
    }

    $thumb = ltrim(str_replace('/', '_', $path), '_');

    if (! file_exists(UPLOADS . '/thumbnails/' . $thumb)) {
        $img = Image::make(HOME . $path);
        $img->resize(setting('previewsize'), setting('previewsize'), static function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $img->save(UPLOADS . '/thumbnails/' . $thumb);
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
 * @param string|null $path   путь к изображению
 * @param array       $params параметры изображения
 *
 * @return HtmlString уменьшенное изображение
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
 * @param string $path путь к файлу
 *
 * @return bool
 */
function deleteFile(string $path): bool
{
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }

    if (in_array(getExtension($path), ['jpg', 'jpeg', 'gif', 'png'], true)) {
        $thumb = ltrim(str_replace([HOME, '/'], ['', '_'], $path), '_');
        $thumb = UPLOADS . '/thumbnails/' . $thumb;

        if (file_exists($thumb) && is_file($thumb)) {
            unlink($thumb);
        }
    }

    return true;
}

/**
 * Отправляет уведомление об упоминании в приват
 *
 * @param string $text  текст сообщения
 * @param string $url   путь к странице
 * @param string $title название страницу
 *
 * @return void
 */
function sendNotify(string $text, string $url, string $title)
{
    /*$parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $text);*/
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
 * @param string $type    тип сообщения
 * @param array  $replace массив заменяемых параметров
 *
 * @return string сформированный текст
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
 * @return HtmlString|null статистика производительности
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
 * @param string|array|null $keys
 *
 * @return bool результат выполнения
 */
function clearCache($keys = null): bool
{
    if ($keys) {
        if (!is_array($keys)) {
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
 * @param string|null $url
 *
 * @return string|null текущая страница
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
 * Возвращает подключенный шаблон
 *
 * @param string $view   имя шаблона
 * @param array  $params массив параметров
 * @param array  $mergeData
 *
 * @return string сформированный код
 */
function view(string $view, array $params = [], array $mergeData = []): string
{
    return View::make($view, $params, $mergeData)->render();
}

/**
 * Translate the given message.
 *
 * @param string      $key
 * @param array       $replace
 * @param string|null $locale
 *
 * @return string
 */
function __(string $key, array $replace = [], $locale = null): string
{
    return Lang::get($key, $replace, $locale);
}

/**
 * Translates the given message based on a count.
 *
 * @param string              $key
 * @param int|array|Countable $number
 * @param array               $replace
 * @param string|null         $locale
 *
 * @return string
 */
function choice(string $key, $number, array $replace = [], $locale = null): string
{
    return Lang::choice($key, $number, $replace, $locale);
}

/**
 * Сохраняет страницы с ошибками
 *
 * @param int|string  $code    код ошибки
 * @param string|null $message текст ошибки
 *
 * @return string сформированная страница с ошибкой
 */
function abort($code, $message = null): string
{
    $protocol = server('SERVER_PROTOCOL');
    $referer  = server('HTTP_REFERER');

    switch ($code) {
        case 403:
            header($protocol . ' 403 Forbidden');
            break;
        case 404:
            header($protocol . ' 404 Not Found');
            break;
        case 405:
            header($protocol . ' 405 Method Not Allowed');
            break;
        default:
            header($protocol . ' 400 Bad Request');
    }

    saveErrorLog($code);

    if (request()->ajax()) {
        header($protocol . ' 200 OK');

        exit(json_encode([
            'status' => 'error',
            'message' => $message,
        ]));
    }

    exit(view('errors/' . $code, compact('message', 'referer')));
}

/**
 * Saves error logs
 *
 * @param mixed $code
 *
 * @return void
 */
function saveErrorLog($code)
{
    if (setting('errorlog') && in_array($code, [403, 404, 405, 666], true)) {
        Error::query()->create([
            'code'       => $code,
            'request'    => utfSubstr(request()->getRequestUri(), 0, 200),
            'referer'    => utfSubstr(server('HTTP_REFERER'), 0, 200),
            'user_id'    => getUser('id'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }
}

/**
 * Переадресовывает пользователя
 *
 * @param string $url       адрес переадресации
 * @param bool   $permanent постоянное перенаправление
 *
 * @return void
 */
function redirect(string $url, bool $permanent = false)
{
    if (isset($_SESSION['captcha'])) {
        $_SESSION['captcha'] = null;
    }

    if ($permanent) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
    }

    header('Location: ' . $url);
    exit();
}

/**
 * Сохраняет flash уведомления
 *
 * @param string $status статус уведомления
 * @param mixed  $message массив или текст с уведомлениями
 *
 * @return void
 */
function setFlash(string $status, $message)
{
    $_SESSION['flash'][$status] = $message;
}

/**
 * Возвращает ошибку
 *
 * @param string|array $errors ошибки
 *
 * @return HtmlString сформированный блок с ошибкой
 */
function showError($errors): HtmlString
{
    $errors = (array) $errors;

    return new HtmlString(view('app/_error', compact('errors')));
}

/**
 * @return HtmlString
 */
function getCaptcha(): HtmlString
{
    return new HtmlString(view('app/_captcha'));
}

/**
 * Сохраняет POST данные введенных пользователем
 *
 * @param array $data массив полей
 */
function setInput(array $data)
{
    $_SESSION['input'] = json_encode($data);
}

/**
 * Возвращает значение из POST данных
 *
 * @param string $name имя поля
 * @param mixed  $default
 *
 * @return mixed сохраненное значение
 */
function getInput(string $name, $default = null)
{
    if (empty($_SESSION['input'])) {
        return $default;
    }

    $session = json_decode($_SESSION['input'], true);
    $input   = Arr::get($session, $name);

    if ($input !== null) {
        Arr::forget($session, $name);

        $_SESSION['input'] = json_encode($session);
    }

    return $input ?? $default;
}

/**
 * Подсвечивает блок с полем для ввода сообщения
 *
 * @param string $field имя поля
 *
 * @return string CSS класс ошибки
 */
function hasError(string $field): string
{
    $isValid = isset($_SESSION['flash']['danger']) ? ' is-valid' : '';

    return isset($_SESSION['flash']['danger'][$field]) ? ' is-invalid' : $isValid;
}

/**
 * Возвращает блок с текстом ошибки
 *
 * @param string $field имя поля
 *
 * @return string|null блоки ошибки
 */
function textError(string $field): ?string
{
    return $_SESSION['flash']['danger'][$field] ?? null;
}

/**
 * Отправляет уведомления на email
 *
 * @param string $to      Получатель
 * @param string $subject Тема письма
 * @param string $body    Текст сообщения
 * @param array  $params  Дополнительные параметры
 *
 * @return bool Результат отправки
 */
function sendMail(string $to, string $subject, string $body, array $params = []): bool
{
    if (empty($params['from'])) {
        $params['from'] = [config('SITE_EMAIL') => config('SITE_ADMIN')];
    }

    $message = (new Swift_Message())
        ->setTo($to)
        ->setSubject($subject)
        ->setBody($body, 'text/html')
        ->setFrom($params['from'])
        ->setReturnPath(config('SITE_EMAIL'));

    if (config('MAIL_DRIVER') === 'smtp') {
        $transport = (new Swift_SmtpTransport())
            ->setHost(config('MAIL_HOST'))
            ->setPort(config('MAIL_PORT'))
            ->setEncryption(config('MAIL_ENCRYPTION'))
            ->setUsername(config('MAIL_USERNAME'))
            ->setPassword(config('MAIL_PASSWORD'));
    } else {
        $transport = new Swift_SendmailTransport();

        if (config('MAIL_PATH')) {
            $transport->setCommand(config('MAIL_PATH'));
        }
    }

    $mailer = new Swift_Mailer($transport);

    try {
        return $mailer->send($message);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Возвращает расширение файла
 *
 * @param string $filename имя файла
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
 * @param string $filename имя файла
 *
 * @return string имя без расширения
 */
function getBodyName(string $filename): string
{
    return pathinfo($filename, PATHINFO_FILENAME);
}

/**
 * Склоняет числа
 *
 * @param int   $num   число
 * @param mixed $forms массив склоняемых слов (один, два, много)
 *
 * @return string форматированная строка
 */
function plural(int $num, $forms): string
{
    if (! is_array($forms)) {
        $forms = explode(',', $forms);
    }

    if (count($forms) === 1) {
        return $num . ' ' . $forms[0];
    }

    if ($num % 100 > 10 &&  $num % 100 < 15) {
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
 * @param string $text  Необработанный текст
 * @param bool   $parse Обрабатывать или вырезать код
 *
 * @return HtmlString Обработанный текст
 */
function bbCode(string $text, bool $parse = true): HtmlString
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
    $cf = new CloudFlare(request());

    return $cf->ip();
}

/**
 * Определяет браузер
 *
 * @param string|null $userAgent
 *
 * @return string браузер и версия браузера
 */
function getBrowser($userAgent = null): string
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
 * Возращает объект Request
 *
 * @return Request
 */
function request(): Request
{
    static $request;

    if (! $request) {
        $request = Request::capture();
    }

    return $request;
}

/**
 * Возвращает серверные переменные
 *
 * @param string|null $key     ключ массива
 * @param string|null $default значение по умолчанию
 *
 * @return mixed данные
 */
function server($key = null, $default = null)
{
    return request()->server($key, $default);
}

/**
 * Возвращает является ли пользователь авторизованным
 *
 * @return mixed
 */
function checkAuth()
{
    if (isset($_SESSION['id'], $_SESSION['password'])) {
        $user = getUserById($_SESSION['id']);

        if ($user && $_SESSION['password'] === md5(config('APP_KEY') . $user->password)) {
            return $user;
        }
    }

    return false;
}

/**
 * Возвращает является ли пользователь администратором
 *
 * @param string $level уровень доступа
 *
 * @return bool является ли пользователь администратором
 */
function isAdmin($level = User::EDITOR): bool
{
    return access($level);
}

/**
 * Возвращает имеет ли пользователь доступ по уровню
 *
 * @param string $level уровень доступа
 *
 * @return bool разрешен ли доступ
 */
function access(string $level): bool
{
    $access = array_flip(User::ALL_GROUPS);

    return getUser()
        && isset($access[$level], $access[getUser('level')])
        && $access[getUser('level')] <= $access[$level];
}

/**
 * Возвращает объект пользователя по логину
 *
 * @param string|null $login логин пользователя
 *
 * @return Builder|Model|null
 */
function getUserByLogin(?string $login): ?User
{
    return User::query()->where('login', $login)->first();
}

/**
 * Возвращает объект пользователя по id
 *
 * @param int|null $id ID пользователя
 *
 * @return Builder|Model|null
 */
function getUserById(?int $id): ?User
{
    return User::query()->find($id);
}

/**
 * Возвращает объект пользователя по логину или email
 *
 * @param string|null $login логин или email пользователя
 *
 * @return Builder|Model|null
 */
function getUserByLoginOrEmail(?string $login): ?User
{
    $field = strpos($login, '@') ? 'email' : 'login';

    return User::query()->where($field, $login)->first();
}

/**
 * Возвращает данные пользователя по ключу
 *
 * @param string|null $key ключ массива
 *
 * @return User|mixed
 */
function getUser(?string $key = null)
{
    static $user;

    if (! $user) {
        $user = checkAuth();
    }

    return $key ? ($user[$key] ?? null) : $user;
}

/**
 * Разбивает массив по страницам
 *
 * @param array $items
 * @param int   $perPage
 * @param array $appends
 *
 * @return LengthAwarePaginator
 */
function paginate(array $items, int $perPage, array $appends = []): LengthAwarePaginator
{
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $slice       = array_slice($items, $perPage * ($currentPage - 1), $perPage, true);

    $collection = new LengthAwarePaginator($slice, count($items), $perPage);
    $collection->setPath(request()->url());
    $collection->appends($appends);

    return $collection;
}

/**
 * Возвращает сформированный код base64 картинки
 *
 * @param string $path   путь к картинке
 * @param array  $params параметры
 *
 * @return HtmlString сформированный код
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
 *
 * @param int                   $percent
 * @param int|float|string|null $title
 *
 * @return HtmlString
 */
function progressBar(int $percent, $title = null): HtmlString
{
    if (! $title) {
        $title = $percent . '%';
    }

    return new HtmlString(view('app/_progressbar', compact('percent', 'title')));
}

/**
 * Возвращает форматированный список запросов
 *
 * @return array
 */
function getQueryLog(): array
{
    $queries = DB::connection()->getQueryLog();
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
 * @param bool $clear нужно ли сбросить кеш
 *
 * @return array массив IP
 */
function ipBan($clear = false): array
{
    if ($clear) {
        clearCache('ipBan');
    }

    return Cache::rememberForever('ipBan', static function () {
        return Ban::query()->get()->pluck('id', 'ip')->all();
    });
}

/**
 * Возвращает пользовательские настройки сайта по ключу
 *
 * @param string|null $key     ключ массива
 * @param string|null $default значение по умолчанию
 *
 * @return array|string|null данные
 */
function setting($key = null, $default = null)
{
    static $settings;

    if (! $settings) {
        $settings = array_replace(Setting::getSettings(), Setting::getUserSettings());
    }

    return $key ? ($settings[$key] ?? $default) : $settings;
}

/**
 * Возвращает дефолтные настройки сайта по ключу
 *
 * @param string|null $key     ключ массива
 * @param string|null $default значение по умолчанию
 *
 * @return array|string|null данные
 */
function defaultSetting($key = null, $default = null)
{
    static $settings;

    if (! $settings) {
        $settings = Setting::getSettings();
    }

    return $key ? ($settings[$key] ?? $default) : $settings;
}

/**
 * Возвращает путь к сайту
 *
 * @param bool $parse выводить протокол
 *
 * @return string адрес сайта
 */
function siteUrl(bool $parse = false): string
{
    $url = config('SITE_URL');

    if ($parse) {
        $url = Str::startsWith($url, '//') ? 'http:' . $url : $url;
    }

    return $url;
}

/**
 * Возвращает имя сайта из ссылки
 *
 * @param string $url ссылка на сайт
 *
 * @return string имя сайта
 */
function siteDomain(string $url): string
{
    $url = strtolower($url);
    $url = str_replace(['http://www.', 'http://', 'https://', '//'], '', $url);
    $url = strtok($url, '/?');

    return $url;
}

/**
 * Получает версию
 *
 * @param string $version
 *
 * @return string
 */
function parseVersion(string $version): string
{
    $ver = explode('.', strtok($version, '-'));

    return $ver[0] . '.' . $ver[1] . '.' . ($ver[2] ?? 0);
}

/**
 * Проверяет captcha
 *
 * @return bool
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

    if (setting('captcha_type') === 'graphical') {
        return strtolower($request->input('protect')) === $_SESSION['protect'];
    }

    return false;
}

/**
 * Возвращает уникальное имя
 *
 * @param string|null $extension
 *
 * @return string
 */
function uniqueName(string $extension = null): string
{
    if ($extension) {
        $extension = '.' . $extension;
    }

    return str_replace('.', '', uniqid('', true)) . $extension;
}

/**
 * Возвращает курсы валют
 *
 * @return HtmlString
 */
function getCourses(): ?HtmlString
{
    $courses = Cache::remember('courses', 3600, static function () {
        try {
            $client = new Client(['timeout' => 3.0]);
            $response = $client->get('//www.cbr-xml-daily.ru/daily_json.js');

            $content = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            $content = null;
        }

        return $content;
    });

    return new HtmlString(view('app/_courses', compact('courses')));
}

/**
 * Runs the console command
 *
 * @param Command $command
 * @param array   $arguments
 *
 * @return void
 */
function runCommand(Command $command, array $arguments = [])
{
    $input  = new ArrayInput($arguments);
    $output = new NullOutput();

    try {
        $command->run($input, $output);
    } catch (Exception $e) {
        return;
    }
}

/**
 * Returns csrf token field
 *
 * @return string
 */
function csrf_field(): string
{
    return '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
}

/**
 * Return config
 *
 * @param string $key
 * @param mixed $default
 *
 * @return mixed
 */
function config(string $key, $default = null)
{
    static $config;

    if (! $config) {
        $configPath = STORAGE . '/caches/config.php';

        if (file_exists($configPath)) {
            $config = require $configPath;
        } else {
            $loader = new Loader(BASEDIR . '/.env');
            $params = $loader->parse()->toArray();
            $getenv = array_intersect_key(getenv(), $params);
            $config = array_replace($params, $getenv);

            if (config('APP_ENV') === 'production') {
                file_put_contents(
                    $configPath,
                    '<?php return ' . var_export($config, true) . ';'
                );
            }
        }
    }

    return $config[$key] ?? $default;
}

/**
 * Get the path to a versioned Mix file.
 *
 * @param  string  $path
 * @param  string  $manifestDirectory
 *
 * @return string
 * @throws Exception
 */
function mix(string $path, string $manifestDirectory = ''): string
{
    return (new Mix())(...func_get_args());
}
