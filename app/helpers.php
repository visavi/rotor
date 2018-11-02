<?php

use App\Classes\{BBCode, Metrika, Registry, CloudFlare};
use App\Models\{
    Antimat,
    Ban,
    Banhist,
    BlackList,
    Blog,
    Item,
    Load,
    Chat,
    Counter,
    Down,
    Guestbook,
    Invite,
    Error,
    News,
    Notice,
    Offer,
    Online,
    Photo,
    Post,
    RekUser,
    Setting,
    Smile,
    Spam,
    Topic,
    User,
    Vote
};
use Curl\Curl;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Jenssegers\Blade\Blade;
use ReCaptcha\ReCaptcha;

/**
 * Форматирует вывод времени из секунд
 *
 * @param  string $time секунды
 * @return string       форматированный вывод
 */
function makeTime($time)
{
    $format = $time < 3600 ? 'i:s' : 'H:i:s';
    return gmdate($format, $time);
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
            $numbers = array_map('\intval', $numbers);
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
 * @param  string $time кол. секунд timestamp
 * @return string       время в читаемом формате
 */
function formatTime($time)
{
    $units = [
        'год,года,лет'           => 31536000,
        'месяц,месяца,месяцев'   => 2592000,
        'неделя,недели,недель'   => 604800,
        'день,дня,дней'          => 86400,
        'час,часа,часов'         => 3600,
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
    return Antimat::replace($str);
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
    $output .= str_repeat('<i class="fas fa-star"></i>', $full_stars);
    $output .= str_repeat('<i class="fas fa-star-half-alt"></i>', $half_stars);
    $output .= str_repeat('<i class="far fa-star"></i>', $empty_stars);
    $output .= '( ' . $rating .' )</div>';

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

        if (! $notEmpty) {
            break;
        }

        $cal[] = $row;
    }
    return $cal;
}

/**
 * Возвращает календарь
 *
 * @return string календарь
 */
function getCalendar()
{
    [$date['day'], $date['mon'], $date['year']] = explode('.', dateFixed(SITETIME, 'j.n.Y'));

    $startMonth = mktime(0, 0, 0, $date['mon'], 1);

    $newsDays = [];
    $newsIds  = [];

    $news = News::query()->where('created_at', '>', $startMonth)->get();

    if ($news->isNotEmpty()) {
        foreach ($news as $data) {
            $curDay           = dateFixed($data->created_at, 'j');
            $newsDays[]       = $curDay;
            $newsIds[$curDay] = $data->id;
        }
    }

    $calendar = makeCalendar($date['mon'], $date['year']);

    return view('app/_calendar', compact('calendar', 'date', 'newsDays', 'newsIds'));
}

/**
 * Возвращает количество пользователей онлайн по типам
 *
 * @return array массив данных
 */
function statsOnline()
{
    if (@filemtime(STORAGE . '/temp/online.dat') < time() - 60) {

        $metrika = new Metrika();
        $metrika->getCounter();

        $online[] = Online::query()->whereNotNull('user_id')->count();
        $online[] = Online::query()->count();

        file_put_contents(STORAGE . '/temp/online.dat', json_encode($online), LOCK_EX);
    }

    return json_decode(file_get_contents(STORAGE . '/temp/online.dat'));
}

/**
 * Возвращает количество пользователей онлайн
 *
 * @return string
 */
function showOnline()
{
    $online = statsOnline();

    if (setting('onlines')) {
        return view('app/_online', compact('online'));
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
    if (@filemtime(STORAGE . '/temp/counter.dat') < time() - 30) {
        $counts = Counter::query()->first();
        file_put_contents(STORAGE . '/temp/counter.dat', json_encode($counts), LOCK_EX);
    }

    return json_decode(file_get_contents(STORAGE . '/temp/counter.dat'));
}

/**
 * Выводит счетчик посещений
 *
 * @return string
 */
function showCounter()
{
    $metrika = new Metrika();
    $metrika->saveStatistic();

    $count = statsCounter();

    if (setting('incount') > 0) {
        return view('app/_counter', compact('count'));
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
    if (@filemtime(STORAGE . '/temp/statusers.dat') < time() - 3600) {

        $startDay = mktime(0, 0, 0, dateFixed(SITETIME, 'n'));

        $stat = User::query()->count();
        $new  = User::query()->where('created_at', '>', $startDay)->count();

        if ($new) {
            $stat = $stat . '/+' . $new;
        }

        file_put_contents(STORAGE . '/temp/statusers.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statusers.dat');
}

/**
 * Возвращает количество администраторов
 *
 * @return int количество администраторов
 */
function statsAdmins()
{
    if (@filemtime(STORAGE . '/temp/statadmins.dat') < time() - 3600) {

        $total = User::query()->whereIn('level', User::ADMIN_GROUPS)->count();

        file_put_contents(STORAGE . '/temp/statadmins.dat', $total, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statadmins.dat');
}

/**
 * Возвращает количество жалоб
 *
 * @return int количество жалоб
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
function statsPhotos()
{
    if (@filemtime(STORAGE . '/temp/statphotos.dat') < time() - 900) {
        $stat     = Photo::query()->count();
        $totalNew = Photo::query()->where('created_at', '>', strtotime('-3 day', SITETIME))->count();

        if ($totalNew) {
            $stat = $stat . '/+' . $totalNew;
        }

        file_put_contents(STORAGE . '/temp/statphotos.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statphotos.dat');
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
    if (file_exists(STORAGE . '/temp/checker.dat')) {
        return dateFixed(filemtime(STORAGE . '/temp/checker.dat'), 'j.m.y');
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

    return $invited . '/' . $usedInvited;
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
    if (@filemtime(STORAGE . '/temp/statblog.dat') < time() - 900) {

        $stat      = Blog::query()->count();
        $totalnew  = Blog::query()->where('created_at', '>', strtotime('-3 day', SITETIME))->count();

        if ($totalnew) {
            $stat = $stat . '/+' . $totalnew;
        }

        file_put_contents(STORAGE . '/temp/statblog.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statblog.dat');
}

/**
 * Возвращает количество тем и сообщений в форуме
 *
 * @return string количество тем и сообщений
 */
function statsForum()
{
    if (@filemtime(STORAGE . '/temp/statforum.dat') < time() - 600) {

        $topics = Topic::query()->count();
        $posts  = Post::query()->count();

        file_put_contents(STORAGE . '/temp/statforum.dat', $topics . '/' . $posts, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statforum.dat');
}

/**
 * Возвращает количество сообщений в гостевой книге
 *
 * @return int количество сообщений
 */
function statsGuestbook()
{
    if (@filemtime(STORAGE . '/temp/statguestbook.dat') < time() - 600) {

        $total = Guestbook::query()->count();

        file_put_contents(STORAGE . '/temp/statguestbook.dat', $total, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statguestbook.dat');
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
    if (@filemtime(STORAGE . '/temp/statload.dat') < time() - 900) {

        $totalLoads = Load::query()->sum('count_downs');

        $totalNew = Down::query()->where('active', 1)
            ->where('created_at', '>', strtotime('-3 day', SITETIME))
            ->count();

        $stat = $totalNew ? $totalLoads . '/+' . $totalNew : $totalLoads;

        file_put_contents(STORAGE . '/temp/statload.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statload.dat');
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
 * Возвращает количество объявлений
 *
 * @return string количество статей
 */
function statsBoard()
{
    if (@filemtime(STORAGE . '/temp/statboard.dat') < time() - 900) {

        $stat      = Item::query()->where('expires_at', '>', SITETIME)->count();
        $totalnew  = Item::query()->where('updated_at', '>', strtotime('-3 day', SITETIME))->count();

        if ($totalnew) {
            $stat = $stat . '/+' . $totalnew;
        }

        file_put_contents(STORAGE . '/temp/statboard.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statboard.dat');
}

/**
 * Обфусцирует email
 *
 * @param  string $email email
 * @return string       обфусцированный email
 */
function cryptMail($email)
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
 * @param  string $email
 * @return string
 */
function hideMail($email)
{
    return preg_replace('/(?<=.).(?=.*@)/u', '*', $email);
}

/**
 * Возвращает статистику текущих голосований из кэш-файла,
 * предварительно сформировав этот файл, если он устарел
 *
 * @return string Статистика текущий голосований
 */
function statVotes()
{
    if (@filemtime(STORAGE . '/temp/statvote.dat') < time() - 900) {

        $votes = Vote::query()
            ->selectRaw('count(*) AS cnt, ifnull(sum(count), 0) AS sum')
            ->where('closed', 0)
            ->first();

        if (! $votes) {
            $votes->cnt = $votes->sum = 0;
        }

        file_put_contents(STORAGE . '/temp/statvote.dat', $votes->cnt . '/' . $votes->sum, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statvote.dat');
}

/**
 * Возвращает дату последней новости из кэш-файла,
 * предварительно сформировав этот файл, если он устарел
 *
 * @return string Дата последней новости или тег span с текстом "Сегодня "
 */
function statsNewsDate()
{
    if (@filemtime(STORAGE . '/temp/statnews.dat') < time() - 900) {
        $stat = 0;

        $news = News::query()->orderBy('created_at', 'desc')->first();

        if ($news) {
            $stat = dateFixed($news->created_at, 'd.m.y');
        }

        file_put_contents(STORAGE . '/temp/statnews.dat', $stat, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/statnews.dat');
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
                echo '<i class="far fa-circle fa-lg text-muted"></i> <a href="/news/' . $data->id . '">' . $data->title . '</a> (' . $data->count_comments . ') <i class="fa fa-caret-down news-title"></i><br>';

                echo '<div class="news-text" style="display: none;">' . bbCode($data->text) . '<br>';
                echo '<a href="/news/comments/' . $data->id . '">Комментарии</a> ';
                echo '<a href="/news/end/' . $data->id . '">&raquo;</a></div>';
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

        /** @var User $user */
        $user = User::query()->find($_SESSION['id']);

        if ($user && $_SESSION['password'] === md5(env('APP_KEY') . $user->password)) {
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

    return getUser() && isset($access[$level], $access[getUser('level')]) && $access[getUser('level')] <= $access[$level];
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
    return '<i class="far fa-' . $ico . '"></i>';
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
        if (@filemtime(STORAGE . '/temp/rekuser.dat') < time() - 1800) {
            saveAdvertUser();
        }

        $datafile = json_decode(file_get_contents(STORAGE . '/temp/rekuser.dat'));

        if ($datafile) {

            $total = count($datafile);
            $show  = setting('rekusershow') > $total ? $total : setting('rekusershow');

            $links  = array_random($datafile, $show);
            $result = implode('<br>', $links);

            return view('advert/_user', compact('result'));
        }
    }

    return false;
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
                $val['name'] = '<span style="color:' . $val->color . '">' . $val->name . '</span>';
            }

            $link = '<a href="' . $val->site . '" target="_blank" rel="nofollow">' . $val->name . '</a>';

            if ($val->bold) {
                $link = '<b>' . $link . '</b>';
            }

            $links[] = $link;
        }
    }

    file_put_contents(STORAGE . '/temp/rekuser.dat', json_encode($links, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

/**
 * Выводит последние фотографии
 *
 * @param  int  $show Количество последних фотографий
 * @return void       Список фотографий
 */
function recentPhotos($show = 5)
{
    if (@filemtime(STORAGE . '/temp/recentphotos.dat') < time() - 1800) {

        $recent = Photo::query()
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->with('files')
            ->get();

        file_put_contents(STORAGE . '/temp/recentphotos.dat', json_encode($recent, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $photos = json_decode(file_get_contents(STORAGE . '/temp/recentphotos.dat'));

    if ($photos) {
        foreach ($photos as $photo) {
            $file = current($photo->files);

            if ($file) {
                echo '<a href="/photos/' . $photo->id . '">' . resizeImage($file->hash, ['alt' => $photo->title, 'class' => 'rounded', 'style' => 'width: 100px;']) . '</a>';
            }
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
    if (@filemtime(STORAGE . '/temp/recenttopics.dat') < time() - 180) {
        $lastTopics = Topic::query()->orderBy('updated_at', 'desc')->limit($show)->get();
        file_put_contents(STORAGE . '/temp/recenttopics.dat', json_encode($lastTopics, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $topics = json_decode(file_get_contents(STORAGE . '/temp/recenttopics.dat'));

    if ($topics) {
        foreach ($topics as $topic) {
            echo '<i class="far fa-circle fa-lg text-muted"></i>  <a href="/topics/' . $topic->id . '">' . $topic->title . '</a> (' . $topic->count_posts . ')';
            echo '<a href="/topics/end/' . $topic->id . '">&raquo;</a><br>';
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
    if (@filemtime(STORAGE . '/temp/recentfiles.dat') < time() - 600) {

        $lastFiles = Down::query()
            ->where('active', 1)
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->with('category')
            ->get();

        file_put_contents(STORAGE . '/temp/recentfiles.dat', json_encode($lastFiles, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $files = json_decode(file_get_contents(STORAGE . '/temp/recentfiles.dat'));

    if ($files) {
        foreach ($files as $file) {
            echo '<i class="far fa-circle fa-lg text-muted"></i>  <a href="/downs/' . $file->id . '">' . $file->title . '</a> (' . $file->count_comments . ')<br>';
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
    if (@filemtime(STORAGE . '/temp/recentblog.dat') < time() - 600) {
        $lastBlogs = Blog::query()
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->get();

        file_put_contents(STORAGE . '/temp/recentblog.dat', json_encode($lastBlogs, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $blogs = json_decode(file_get_contents(STORAGE . '/temp/recentblog.dat'));

    if ($blogs) {
        foreach ($blogs as $blog) {
            echo '<i class="far fa-circle fa-lg text-muted"></i> <a href="/articles/' . $blog->id . '">' . $blog->title . '</a> (' . $blog->count_comments . ')<br>';
        }
    }
}

/**
 * Выводит последние объявления
 *
 * @param  int  $show Количество последних объявлений
 * @return void       Список объявлений
 */
function recentBoards($show = 5)
{
    if (@filemtime(STORAGE . '/temp/recentboard.dat') < time() - 600) {
        $lastItems = Item::query()
            ->where('expires_at', '>', SITETIME)
            ->orderBy('created_at', 'desc')
            ->limit($show)
            ->get();

        file_put_contents(STORAGE . '/temp/recentboard.dat', json_encode($lastItems, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    $items = json_decode(file_get_contents(STORAGE . '/temp/recentboard.dat'));

    if ($items) {
        foreach ($items as $item) {
            echo '<i class="far fa-circle fa-lg text-muted"></i> <a href="/items/' . $item->id . '">' . $item->title . '</a><br>';
        }
    }
}


/**
 * Возвращает количество предложений и проблем
 *
 * @return string количество предложений и проблем
 */
function statsOffers()
{
    if (@filemtime(STORAGE . '/temp/offers.dat') < time() - 10800) {

        $offers   = Offer::query()->where('type', 'offer')->count();
        $problems = Offer::query()->where('type', 'issue')->count();

        file_put_contents(STORAGE . '/temp/offers.dat', $offers . '/' . $problems, LOCK_EX);
    }

    return file_get_contents(STORAGE . '/temp/offers.dat');
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
        case 'forums':
            DB::update('update topics set count_posts = (select count(*) from posts where topics.id = posts.topic_id)');
            DB::update('update forums set count_topics = (select count(*) from topics where forums.id = topics.forum_id)');
            DB::update('update forums set count_posts = (select ifnull(sum(count_posts), 0) from topics where forums.id = topics.forum_id)');
            break;

        case 'blogs':
            DB::update('update categories set count_blogs = (select count(*) from blogs where categories.id = blogs.category_id)');
            DB::update('update blogs set count_comments = (select count(*) from comments where relate_type = "' . addslashes(Blog::class) . '" and blogs.id = comments.relate_id)');
            break;

        case 'loads':
            DB::update('update loads set count_downs = (select count(*) from downs where loads.id = downs.category_id and active = ?)', [1]);
            DB::update('update downs set count_comments = (select count(*) from comments where relate_type = "' . addslashes(Down::class) . '" and downs.id = comments.relate_id)');
            break;

        case 'news':
            DB::update('update news set count_comments = (select count(*) from comments where relate_type = "' . addslashes(News::class) . '" and news.id = comments.relate_id)');
            break;

        case 'photos':
            DB::update('update photos set count_comments = (select count(*) from comments where relate_type=  "' . addslashes(Photo::class) . '" and photos.id = comments.relate_id)');
            break;

        case 'offers':
            DB::update('update offers set count_comments = (select count(*) from comments where relate_type=  "' . addslashes(Offer::class) . '" and offers.id = comments.relate_id)');
            break;

        case 'boards':
            DB::update('update boards set count_items = (select count(*) from items where boards.id = items.board_id and items.expires_at > ' . SITETIME . ');');
            break;

        case 'votes':
            DB::update('update votes set count = (select ifnull(sum(result), 0) from voteanswer where votes.id = voteanswer.vote_id)');
            break;
    }
}

/**
 * Возвращает количество строк в файле
 *
 * @param  string $file путь к файлу
 * @return int          количество строк
 */
function counterString($file)
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
 * @param  int    $num число
 * @return string      форматированное число
 */
function formatNum($num)
{
    if ($num > 0) {
        return '<span style="color:#00aa00">+' . $num . '</span>';
    }

    if ($num < 0) {
        return '<span style="color:#ff0000">' . $num . '</span>';
    }

    return '<span>0</span>';
}

/**
 * Форматирует вывод числа
 *
 * @param  int $num
 * @return bool|string
 */
function formatShortNum($num)
{
    if (! is_numeric($num)) {
        return false;
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
 * @param  string $path   путь к изображению
 * @param  array  $params параметры изображения
 * @return array          обработанные параметры
 */
function resizeProcess($path, array $params = [])
{
    if (empty($params['alt'])) {
        $params['alt'] = basename($path);
    }

    if (empty($params['class'])) {
        $params['class'] = 'img-fluid';
    }

    if (empty($params['width'])) {
        $params['width'] = setting('previewsize');
    }

    if (! file_exists(HOME . $path) || ! is_file(HOME . $path)) {
        return [
            'path'   => '/assets/img/images/photo.jpg',
            'source' => false,
            'params' => $params,
        ];
    }

    [$width, $height] = getimagesize(HOME . $path);

    if ($width <= $params['width'] && $height <= $params['width']) {
        return [
            'path'   => $path,
            'source' => $path,
            'params' => $params,
        ];
    }

    $thumb = ltrim(str_replace('/', '_', $path), '_');

    if (! file_exists(UPLOADS . '/thumbnails/' . $thumb)) {

        $img = Image::make(HOME . $path);
        $img->resize($params['width'], $params['width'], function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        /*
        $img->fit($params['width'], $params['width'], function ($constraint) {
            $constraint->upsize();
        });*/

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
 * @param  string $path   путь к изображению
 * @param  array  $params параметры изображения
 * @return string         уменьшенное изображение
 */
function resizeImage($path, array $params = [])
{
    $image = resizeProcess($path, $params);

    $strParams = [];
    foreach ($image['params'] as $key => $param) {
        $strParams[] = $key . '="' . $param . '"';
    }

    $strParams = implode(' ', $strParams);

    return '<img src="' . $image['path'] . '" data-source="' . $image['source'] . '" ' . $strParams . '>';
}

/**
 * Удаляет директорию рекурсивно
 *
 * @param string $dir  путь к директории
 * @return void
 */
function deleteDir($dir)
{
    if (file_exists($dir)) {
        if ($files = glob($dir . '/*')) {
            foreach($files as $file) {
                is_dir($file) ? deleteDir($file) : unlink($file);
            }
        }
        rmdir($dir);
    }
}

/**
 * Удаляет файл и превью
 *
 * @param string $path путь к файлу
 * @return bool
 */
function deleteFile($path)
{
    if (file_exists($path) && is_file($path)) {
        unlink($path);
    }

    $thumb = ltrim(str_replace([HOME, '/'], ['', '_'], $path), '_');
    $thumb = UPLOADS . '/thumbnails/' . $thumb;

    if (file_exists($thumb) && is_file($thumb)) {
        unlink($thumb);
    }

    return true;
}

/**
 * Отправляет уведомление об упоминании в приват
 *
 * @param string $text     текст сообщения
 * @param string $pageUrl  путь к странице
 * @param string $pageName название страницу
 */
function sendNotify(string $text, string $pageUrl, string $pageName)
{
    /*$parseText = preg_replace('|\[quote(.*?)\](.*?)\[/quote\]|s', '', $text);*/
    preg_match_all('/(?<=^|\s)@([\w\-]+)/', $text, $matches);

    if (! empty($matches[1])) {
        $usersAnswer = array_unique(array_diff($matches[1], [getUser('login')]));

        foreach ($usersAnswer as $login) {
            $user = getUserByLogin($login);
            if ($user && $user->notify) {
                $user->sendMessage(null, 'Пользователь @' . (getUser('login') ?? setting('guestsuser')) . ' упомянул вас на странице [url=' . $pageUrl . ']' . $pageName . '[/url]' . PHP_EOL . 'Текст сообщения: ' . $text);
            }
        }
    }
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
 * Возвращает блок статистики производительности
 *
 * @return string статистика производительности
 */
function performance()
{
    if (isAdmin() && setting('performance')) {
        $queries = getQueryLog();
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
    $files = glob(STORAGE . '/temp/*.dat');
    $files = array_diff($files, [
        STORAGE . '/temp/checker.dat',
        STORAGE . '/temp/counter7.dat'
    ]);

    if ($files) {
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
    $request = Request::createFromGlobals();

    if ($request->is('/', 'login', 'register', 'recovery', 'restore', 'ban', 'closed')) {
        return false;
    }
    $query = $request->has('return') ? $request->input('return') : $request->path();
    return '?return=' . urlencode(! $url ? $query : $url);
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
        HOME . '/themes/' . setting('themes') . '/views',
        RESOURCES . '/views',
        HOME . '/themes',
    ], STORAGE . '/caches');

    $blade->compiler()->withoutDoubleEncoding();

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
    $request  = Request::createFromGlobals();
    $protocol = server('SERVER_PROTOCOL');
    $referer  = server('HTTP_REFERER') ?? null;

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
   }

    if (setting('errorlog') && in_array($code, [403, 404, 405], true)) {

        Error::query()->create([
            'code'       => $code,
            'request'    => utfSubstr(server('REQUEST_URI'), 0, 200),
            'referer'    => utfSubstr($referer, 0, 200),
            'user_id'    => getUser('id'),
            'ip'         => getIp(),
            'brow'       => getBrowser(),
            'created_at' => SITETIME,
        ]);
    }

    if ($request->ajax()) {
        header($protocol . ' 200 OK');

        exit(json_encode([
            'status' => 'error',
            'message' => $message,
        ]));
    }

    exit(view('errors/' . $code, compact('message', 'referer')));
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
    $_SESSION['input'] = json_encode($data);
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
    if (empty($_SESSION['input'])) {
        return $default;
    }

    $session = json_decode($_SESSION['input'], true);

    if ($input = array_get($session, $name)) {
        array_forget($session, $name);

        $_SESSION['input'] = json_encode($session);
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
        $text = '<div class="text-danger">' . $error . '</div>';
    }

    return $text;
}

/**
 * Отправляет уведомления на email
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
        ->setReturnPath(env('SITE_EMAIL'));

    if (env('MAIL_DRIVER') === 'smtp') {
        $transport = (new Swift_SmtpTransport())
            ->setHost(env('MAIL_HOST'))
            ->setPort(env('MAIL_PORT'))
            ->setEncryption(env('MAIL_ENCRYPTION'))
            ->setUsername(env('MAIL_USERNAME'))
            ->setPassword(env('MAIL_PASSWORD'));
    } else {
        $transport = new Swift_SendmailTransport();

        if (env('MAIL_PATH')) {
            $transport->setCommand(env('MAIL_PATH'));
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
    $cf = new CloudFlare();
    $ip = $cf->ip();

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
    return mb_substr($version === 'unknown' ? $brow : $brow . ' ' . $version, 0, 25, 'utf-8');
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
    $request = Request::createFromGlobals();
    $server  = $request->server($key, $default);

    if ($key === 'REQUEST_URI') {
        $server = urldecode($server);
    }

    if ($key === 'PHP_SELF') {
        $server = current(explode('?', server('REQUEST_URI')));
    }

    return check($server);
}

/**
 * Возвращает объект пользователя по логину
 *
 * @param  string    $login логин пользователя
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
 */
function getUserByLogin($login): ?User
{
    return User::query()->where('login', $login)->first();
}

/**
 * Возвращает объект пользователя по id
 *
 * @param  int       $id ID пользователя
 * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null
 */
function getUserById(int $id): ?User
{
    return User::query()->find($id);
}

/**
 * Возвращает данные пользователя по ключу
 *
 * @param  string $key ключ массива
 * @return \Illuminate\Database\Query\Builder|mixed
 */
function getUser($key = null)
{
    if (Registry::has('user')) {

        $user = Registry::get('user');

        if ($key) {
            return $user[$key] ?? null;
        }

        return $user;
    }

    return null;
}

/**
 * Генерирует постраничную навигация
 *
 * @param  object $page данные страниц
 * @return string       сформированный блок
 */
function pagination($page)
{
    if (empty($page->total)) {
        return null;
    }

    if (empty($page->crumbs)) {
        $page->crumbs = 2;
    }

    $url     = array_except($_GET, 'page');
    $request = $url ? '&' . http_build_query($url) : null;

    $pages   = [];
    $pg_cnt  = (int) ceil($page->total / $page->limit);
    $idx_fst = max($page->current - $page->crumbs, 1);
    $idx_lst = min($page->current + $page->crumbs, $pg_cnt);

    if ($page->current !== 1) {
        $pages[] = [
            'page'  => $page->current - 1,
            'title' => 'Предыдущая',
            'name'  => '«',
        ];
    }

    if ($page->current > $page->crumbs + 1) {
        $pages[] = [
            'page'  => 1,
            'title' => '1 страница',
            'name'  => 1,
        ];
        if ($page->current !== $page->crumbs + 2) {
            $pages[] = [
                'separator' => true,
                'name'      => ' ... ',
            ];
        }
    }

    for ($i = $idx_fst; $i <= $idx_lst; $i++) {
        if ($i === $page->current) {
            $pages[] = [
                'current' => true,
                'name'    => $i,
            ];
        } else {
            $pages[] = [
                'page'  => $i,
                'title' => $i . ' страница',
                'name'  => $i,
            ];
        }
    }

    if ($page->current < $pg_cnt - $page->crumbs) {
        if ($page->current !== $pg_cnt - $page->crumbs - 1) {
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

    if ($page->current !== $pg_cnt) {
        $pages[] = [
            'page'  => $page->current + 1,
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
 * @return object         массив подготовленных данных
 */
function paginate(int $limit, int $total)
{
    $request = Request::createFromGlobals();
    $current = int($request->input('page'));

    if ($current < 1) {
        $current = 1;
    }

    if ($total && $current * $limit >= $total) {
        $current = (int) ceil($total / $limit);
    }

    $offset = $current * $limit - $limit;

    return (object) compact('current', 'offset', 'limit', 'total');
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

    if (empty($params['alt'])) {
        $params['alt'] = basename($path);
    }

    $strParams = [];
    foreach ($params as $key => $param) {
        $strParams[] = $key . '="' . $param . '"';
    }

    $strParams = implode(' ', $strParams);

    return '<img src="data:image/' . $type . ';base64,' . base64_encode($data) . '"' . $strParams . '>';
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
    if (! $title) {
        $title = $percent . '%';
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
            RESOURCES . '/lang'
        ),
        setting('language')
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
    if (! $save && file_exists(STORAGE . '/temp/ipban.dat')) {
        $ipBan = json_decode(file_get_contents(STORAGE . '/temp/ipban.dat'));
    } else {
        $ipBan = Ban::query()->pluck('ip')->all();
        file_put_contents(STORAGE . '/temp/ipban.dat', json_encode($ipBan), LOCK_EX);
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
    if (! Registry::has('settings')) {

        if (! file_exists(STORAGE . '/temp/settings.dat')) {
            saveSettings();
        }

        $setting = json_decode(file_get_contents(STORAGE . '/temp/settings.dat'), true);

        Registry::set('settings', $setting);
    }

    if (! $key) {
        return Registry::get('settings');
    }

    return Registry::get('settings')[$key] ?? null;
}

/**
 * Устанавливает настройки сайта
 *
 * @param array $setting массив настроек
 */
function setSetting($setting)
{
    $setting = array_merge(Registry::get('settings'), $setting);
    Registry::set('settings', $setting);
}

/**
 * Кеширует настройки сайта
 */
function saveSettings() {
    $settings = Setting::query()->pluck('value', 'name')->all();
    file_put_contents(STORAGE . '/temp/settings.dat', json_encode($settings, JSON_UNESCAPED_UNICODE), LOCK_EX);
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
    $request = Request::createFromGlobals();

    if (setting('recaptcha_public') && setting('recaptcha_private')) {
        $recaptcha = new ReCaptcha(setting('recaptcha_private'));
        $response = $recaptcha->verify($request->input('g-recaptcha-response'), getIp());
        return $response->isSuccess();
    }

    return check(strtolower($request->input('protect'))) === $_SESSION['protect'];
}

/**
 * Возвращает уникальное имя
 *
 * @param string $extension
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
 * @return string
 * @throws ErrorException
 */
function getCourses()
{
    if (
        @filesize(STORAGE . '/temp/courses.dat') === 0 ||
        @filemtime(STORAGE . '/temp/courses.dat') < time() - 3600
    ) {
        $curl = new Curl();
        $curl->setConnectTimeout(3);

        if ($query = $curl->get('https://www.cbr-xml-daily.ru/daily_json.js')) {
            file_put_contents(STORAGE . '/temp/courses.dat', $query, LOCK_EX);
        } else {
           touch(STORAGE . '/temp/courses.dat', SITETIME);
        }
    }

    $courses = @json_decode(file_get_contents(STORAGE . '/temp/courses.dat'));

    return view('app/_courses', compact('courses'));
}
