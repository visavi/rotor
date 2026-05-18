<?php

use App\Classes\CloudFlare;
use App\Classes\Metrika;
use App\Models\AdminAdvert;
use App\Models\Advert;
use App\Models\Antimat;
use App\Models\Ban;
use App\Models\Banhist;
use App\Models\BlackList;
use App\Models\Chat;
use App\Models\Counter;
use App\Models\Error;
use App\Models\Guestbook;
use App\Models\Invite;
use App\Models\News;
use App\Models\Notice;
use App\Models\Online;
use App\Models\PaidAdvert;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Spam;
use App\Models\Sticker;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use cbschuld\Browser;
use Illuminate\Mail\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use ReCaptcha\ReCaptcha;

const ROTOR_VERSION = '13.1.0';
define('SITETIME', time());

/**
 * Форматирует время с учетом часовых поясов
 */
function dateFixed(
    DateTimeInterface|int|null $timestamp,
    string $format = 'd.m.Y / H:i',
    bool $original = false,
): string {
    $date = Date::parse($timestamp)->setTimezone(config('app.timezone'));
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
 * Преобразует специальные символы в HTML-сущности
 */
function check(array|string|null $string, bool $doubleEncode = true): array|string
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
 */
function int(array|int|string|null $num): int
{
    return abs((int) $num);
}

/**
 * Преобразует все элементы массива в int
 */
function intar(array|int|string|null $numbers): ?array
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
        $format = (int) ($time / $seconds);
        $time %= $seconds;

        if ($format >= 1) {
            $return[] = plural($format, $unit);
        }
    }

    return implode(' ', array_slice($return, 0, $crumbs));
}

/**
 * Очищает строку от мата по базе слов
 */
function antimat(?string $str): string
{
    return Antimat::replace((string) $str);
}

/**
 * Возвращает количество пользователей онлайн по типам
 */
function statsOnline(): array
{
    return Cache::remember('online', 60, static function () {
        $rows = Online::query()->select('user_id')->get();

        $users = $rows->whereNotNull('user_id')->unique('user_id');
        $usersCount = $users->count();
        $guestsCount = $rows->whereNull('user_id')->count();
        $total = $usersCount + $guestsCount;

        $metrika = new Metrika();
        $metrika->getCounter($total);

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
 * Возвращает статистику посещений
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
 */
function statsAdmins(): int
{
    return Cache::remember('statAdmins', 3600, static function () {
        return User::query()->whereIn('level', User::ADMIN_GROUPS)->count();
    });
}

/**
 * Возвращает количество жалоб
 */
function statsSpam(): int
{
    return Spam::query()->count();
}

/**
 * Возвращает количество забанненых пользователей
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
 */
function statsBanHist(): int
{
    return Banhist::query()->count();
}

/**
 * Возвращает количество ожидающих подтверждения регистрации
 */
function statsRegList(): int
{
    return User::query()->where('level', User::PENDED)->count();
}

/**
 * Возвращает количество забаненных по IP
 */
function statsIpBanned(): int
{
    return Ban::query()->count();
}

/**
 * Возвращает количество новостей
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
 */
function statsAntimat(): int
{
    return Antimat::query()->count();
}

/**
 * Возвращает количество стикеров
 */
function statsStickers(): int
{
    return Sticker::query()->count();
}

/**
 * Возвращает дату последнего сканирования сайта
 */
function statsChecker(): string
{
    if (Storage::disk('local')->exists('checker.php')) {
        return dateFixed(Storage::disk('local')->lastModified('checker.php'));
    }

    return '0';
}

/**
 * Возвращает количество приглашений на регистрацию
 */
function statsInvite(): string
{
    $invites = Invite::query()
        ->selectRaw('used, count(*) as cnt')
        ->groupBy('used')
        ->pluck('cnt', 'used');

    return ($invites[0] ?? 0) . '/' . ($invites[1] ?? 0);
}

/**
 * Возвращает количество тем и сообщений в форуме
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
 */
function statsGuestbook(): string
{
    return Cache::remember('statGuestbook', 600, static function () {
        $total = Guestbook::query()->count();

        $totalNew = Guestbook::query()
            ->active()
            ->where('created_at', '>', strtotime('-1 day', SITETIME))
            ->count();

        return formatShortNum($total) . ($totalNew ? '/+' . $totalNew : '');
    });
}

/**
 * Возвращает количество сообщений в админ-чате
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
 */
function statsNewChat(): int
{
    return Chat::query()->max('created_at') ?? 0;
}

/**
 * Частично скрывает email
 */
function hideMail(string $email): string
{
    return preg_replace('/(?<=.).(?=.*@)/u', '*', $email);
}

/**
 * Возвращает статистику текущих голосований из кеш-файла
 */
function statVotes(): string
{
    return Cache::remember('statVotes', 900, static function () {
        $votes = Vote::query()
            ->selectRaw('count(*) AS cnt, coalesce(sum(count), 0) AS sum')
            ->where('closed', 0)
            ->first();

        return ($votes->cnt ?? 0) . '/' . ($votes->sum ?? 0);
    });
}

/**
 * Возвращает иконку расширения
 */
function icons(string $ext): HtmlString
{
    $icons = [
        'php'  => 'fa-regular fa-file-code',
        'ppt'  => 'fa-regular fa-file-powerpoint',
        'doc'  => 'fa-regular fa-file-word',
        'docx' => 'fa-regular fa-file-word',
        'xls'  => 'fa-regular fa-file-excel',
        'xlsx' => 'fa-regular fa-file-excel',
        'txt'  => 'fa-regular fa-file-alt',
        'css'  => 'fa-regular fa-file-alt',
        'dat'  => 'fa-regular fa-file-alt',
        'html' => 'fa-regular fa-file-alt',
        'htm'  => 'fa-regular fa-file-alt',
        'wav'  => 'fa-regular fa-file-audio',
        'amr'  => 'fa-regular fa-file-audio',
        'mp3'  => 'fa-regular fa-file-audio',
        'mid'  => 'fa-regular fa-file-audio',
        'zip'  => 'fa-regular fa-file-archive',
        'rar'  => 'fa-regular fa-file-archive',
        '7z'   => 'fa-regular fa-file-archive',
        'gz'   => 'fa-regular fa-file-archive',
        '3gp'  => 'fa-regular fa-file-video',
        'mp4'  => 'fa-regular fa-file-video',
        'jpg'  => 'fa-regular fa-file-image',
        'jpeg' => 'fa-regular fa-file-image',
        'bmp'  => 'fa-regular fa-file-image',
        'wbmp' => 'fa-regular fa-file-image',
        'gif'  => 'fa-regular fa-file-image',
        'png'  => 'fa-regular fa-file-image',
        'webp' => 'fa-regular fa-file-image',
        'ttf'  => 'fa-solid fa-font',
        'pdf'  => 'fa-regular fa-file-pdf',
        'csv'  => 'fa-regular fa-file-csv',
    ];

    $ico = $icons[$ext] ?? 'fa-regular fa-file';

    return new HtmlString('<i class="' . $ico . '"></i>');
}

/**
 * Возвращает обрезанную до заданного количества слов строку
 */
function truncateHtml(?string $html, int $words = 20, string $end = '...'): HtmlString
{
    $text = strip_tags((string) $html);

    return new HtmlString(Str::words($text, $words, $end));
}

/**
 * Возвращает обрезанную строку с удалением перевода строки
 */
function truncateDescription(HtmlString|string $value, int $words = 20, string $end = ''): string
{
    $value = strip_tags(preg_replace('/[\s\n\r]+/', ' ', $value));

    return Str::words(trim($value), $words, $end);
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
    $result  = '';

    if ($adverts) {
        $total  = count($adverts);
        $show   = setting('rekusershow') > $total ? $total : setting('rekusershow');
        $links  = Arr::random($adverts, $show);
        $result = implode('<br>', $links);
    }

    if ($result || getUser()) {
        return new HtmlString(view('adverts/_links', compact('result')));
    }

    return null;
}

/**
 * Форматирует вывод числа
 */
function formatNum(int|float $num): HtmlString
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
function formatShortNum(int $num): int|string
{
    $thresholds = [
        1_000_000_000_000 => 'T',
        1_000_000_000     => 'B',
        1_000_000         => 'M',
        1_000             => 'K',
    ];

    foreach ($thresholds as $threshold => $suffix) {
        if ($num > $threshold) {
            return round($num / $threshold, 1) . $suffix;
        }
    }

    return $num;
}

/**
 * Удаляет директорию рекурсивно
 */
function deleteDir(string $dir): void
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
 */
function deleteFile(string $path): bool
{
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }

    return true;
}

/**
 * Отправляет уведомление об упоминании в приват
 */
function sendNotify(string $text, string $url, string $title, array $exclude = []): void
{
    if (! $login = getUser('login')) {
        return;
    }

    $excludeLogins = array_merge([$login], $exclude);

    preg_match_all('/<a[^>]+class="user"[^>]*href="\/users\/([\w\-]+)"/', $text, $matches);

    if (! empty($matches[1])) {
        $usersAnswer = array_unique(array_diff($matches[1], $excludeLogins));

        foreach ($usersAnswer as $user) {
            $user = getUserByLogin($user);

            if ($user?->notify_mention) {
                $notify = textNotice('notify', compact('login', 'url', 'title', 'text'));
                $user->sendMessage(null, $notify);
            }
        }
    }
}

/**
 * Возвращает приватное сообщение
 */
function textNotice(string $type, array $replace = []): string
{
    $message = Notice::query()->where('type', $type)->first();

    if (! $message) {
        return __('main.text_missing');
    }

    if (isset($replace['url'])) {
        $replace['page'] = '<a href="' . $replace['url'] . '">' . ($replace['title'] ?? $replace['url']) . '</a>';
        unset($replace['url'], $replace['title']);
    }

    foreach ($replace as $key => $val) {
        if ($key === 'login') {
            $val = '<a class="user" href="/users/' . $val . '">@' . $val . '</a>';
        }

        if ($key === 'text') {
            $message->text = str_replace('<p>%text%</p>', '%text%', $message->text);
        }

        $message->text = str_replace('%' . $key . '%', $val, $message->text);
    }

    return $message->text;
}

/**
 * Возвращает блок статистики производительности
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
 */
function clearCache(array|string|null $keys = null): bool
{
    if ($keys) {
        Cache::deleteMultiple((array) $keys);
    } else {
        Cache::flush();
    }

    return true;
}

/**
 * Возвращает текущую страницу
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
 */
function saveErrorLog(int $code, ?string $message = null): void
{
    $errorCodes = [400, 401, 403, 404, 405, 419, 429, 500, 503, 666];

    if (setting('errorlog') && in_array($code, $errorCodes, true)) {
        Error::query()->create([
            'code'       => $code,
            'request'    => Str::substr(request()->getRequestUri(), 0, 250),
            'referer'    => Str::substr(request()->header('referer'), 0, 250),
            'user_id'    => getUser('id'),
            'message'    => Str::substr($message, 0, 250),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }
}

/**
 * Возвращает ошибку
 */
function showError(string|array $errors): HtmlString
{
    $errors = (array) $errors;

    return new HtmlString(view('app/_error', compact('errors')));
}

/**
 * Get captcha
 */
function getCaptcha(): HtmlString
{
    return new HtmlString(view('app/_captcha'));
}

/**
 * Проверяет captcha
 */
function captchaVerify(): bool
{
    $request = request();

    if (setting('captcha_type') === 'recaptcha_v2') {
        $recaptcha = new ReCaptcha(setting('recaptcha_private'));

        $response = $recaptcha->setExpectedHostname($request->getHost())
            ->verify($request->input('g-recaptcha-response'), getIp());

        return $response->isSuccess();
    }

    if (setting('captcha_type') === 'recaptcha_v3') {
        $recaptcha = new ReCaptcha(setting('recaptcha_private'));

        $response = $recaptcha->setExpectedHostname($request->getHost())
            ->setExpectedAction('submit')
            ->setScoreThreshold(0.5)
            ->verify($request->input('protect'), getIp());

        return $response->isSuccess();
    }

    if (in_array(setting('captcha_type'), ['graphical', 'animated'], true)) {
        return strtolower($request->input('protect')) === strtolower($request->session()->get('protect'));
    }

    return true;
}

/**
 * Сохраняет flash уведомления
 *
 * @deprecated since 10.1 - Use redirect()->with('success', 'Message') or redirect()->withErrors($validator->getErrors())
 * $request->session()->flash('flash.{status}', $message);
 */
function setFlash(string $status, mixed $message): void
{
    session(['flash.' . $status => $message]);
}

/**
 * Сохраняет POST данные введенных пользователем
 *
 * @deprecated since 10.1 - Use $request->flash() or redirect()->withInput();
 */
function setInput(array $data): void
{
    app('session')->flash('_old_input', $data);
}

/**
 * Возвращает значение из POST данных
 *
 * @deprecated since 10.1 - Use old('field', 'default');
 */
function getInput(string $key, mixed $default = null): mixed
{
    if (app('session')->missing('_old_input')) {
        return $default;
    }

    $input = session('_old_input', []);

    return Arr::get($input, $key, $default);
}

/**
 * Подсвечивает блок с полем для ввода сообщения
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
 */
function getExtension(string $filename): string
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

/**
 * Возвращает имя файла без расширения
 */
function getBodyName(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);

    return preg_replace('/\xc2\xa0/', ' ', $name);
}

/**
 * Склоняет числа
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
 * RenderHtml
 */
function renderHtml(?string $text, string $group = 'gallery'): HtmlString
{
    $html = (string) $text;

    if (str_contains($html, 'hidden') && ! auth()->check()) {
        $html = preg_replace(
            '/<div class="hidden">.*?<\/div>/s',
            '<div class="hidden"><em>Содержимое скрыто. Войдите, чтобы увидеть.</em></div>',
            $html
        );
    }

    if (str_contains($html, 'image')) {
        $html = preg_replace(
            '/<img\s([^>]*)class="image"([^>]*)>/i',
            '<img $1class="image" data-fancybox="' . $group . '"$2>',
            $html
        );
    }

    if (str_contains($html, '<a ')) {
        $siteHost = parse_url(config('app.url'), PHP_URL_HOST);
        $html = preg_replace_callback(
            '/<a\b([^>]*)>/i',
            static fn ($m) => preg_match('/href="([^"]*)"/i', $m[1], $h)
            && ($host = parse_url($h[1], PHP_URL_HOST))
            && $host !== $siteHost
                ? '<a' . $m[1] . ' target="_blank" rel="noopener nofollow">'
                : $m[0],
            $html
        );
    }

    return new HtmlString($html);
}

/**
 * Render text
 */
function renderText(?string $text): HtmlString
{
    return new HtmlString(nl2br(e((string) $text)));
}

/**
 * Обрабатывает BB-код
 *
 * @deprecated - используется renderHtml
 */
function bbCode(?string $text): HtmlString
{
    return new HtmlString($text);
}

/**
 * Определяет IP пользователя
 */
function getIp(): string
{
    static $ip = null;

    return $ip ??= (new CloudFlare(request()))->ip();
}

/**
 * Определяет браузер
 */
function getBrowser(): string
{
    static $userAgent = null;

    if ($userAgent !== null) {
        return $userAgent;
    }

    $browser = new Browser();
    $name = $browser->getBrowser();
    $parts = explode('.', $browser->getVersion(), 3);
    $version = implode('.', array_slice($parts, 0, 2));

    $result = $version === Browser::VERSION_UNKNOWN ? $name : $name . ' ' . $version;

    return $userAgent = mb_substr($result, 0, 25, 'utf-8');
}

/**
 * Является ли пользователь администратором
 */
function isAdmin(?string $level = null): bool
{
    $user = auth()->user();
    if (! $user) {
        return false;
    }

    $levels = array_flip(User::ADMIN_GROUPS);
    $level = $level ?? User::EDITOR;

    return isset($levels[$user->level], $levels[$level])
        && $levels[$user->level] >= $levels[$level];
}

/**
 * Возвращает объект пользователя по логину
 */
function getUserByLogin(?string $login): ?User
{
    return User::query()->where('login', $login)->first();
}

/**
 * Возвращает объект пользователя по id
 */
function getUserById(?int $id): ?User
{
    return User::query()->find($id);
}

/**
 * Возвращает объект пользователя по токену
 */
function getUserByToken(string $token): ?User
{
    return User::query()->where('apikey', $token)->first();
}

/**
 * Возвращает объект пользователя по логину или email
 */
function getUserByLoginOrEmail(?string $login): ?User
{
    $field = strpos($login, '@') ? 'email' : 'login';

    return User::query()->where($field, $login)->first();
}

/**
 * Возвращает данные пользователя по ключу
 */
function getUser(?string $key = null): mixed
{
    static $user;

    if (! $user) {
        $user = auth()->user();
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
            } else {
                $query['bindings'][$key] = $binding ?? 'null';
            }
        }

        $sql = str_replace(['%', '?'], ['%%', '%s'], $query['query']);
        $sql = vsprintf($sql, $query['bindings']);

        $formattedQueries[] = ['query' => $sql, 'time' => $query['time']];
    }

    return $formattedQueries;
}

/**
 * Возвращает настройки сайта по ключу
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
 * Получает версию
 */
function parseVersion(string $version): string
{
    $ver = explode('.', strtok($version, '-'));

    return $ver[0] . '.' . $ver[1] . '.' . ($ver[2] ?? '0');
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
 * Возвращает список доступных тем оформления
 */
function getAvailableThemes(): array
{
    return array_map('basename', glob(resource_path('views/themes/*'), GLOB_ONLYDIR) ?: []);
}

/**
 * Возвращает список доступных языков
 */
function getAvailableLanguages(): array
{
    return array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR) ?: []);
}

/**
 * Заменяет относительные URL на абсолютные
 */
function absolutizeUrls(string $text): string
{
    $base = config('app.url');

    return preg_replace('/(href|src)="(\/[^"]*)"/', '$1="' . $base . '$2"', $text);
}
