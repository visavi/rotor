<?php

class App
{
    /**
     * Возвращает данные роутов
     * @param $key
     * @return object данные роутов
     */
    public static function router($key)
    {
        if (Registry::has('router')) {
            return Registry::get('router')[$key];
        }
    }

    /**
     * Возвращает текущую страницу
     * @param null $url
     * @return string текущая страница
     */
    public static function returnUrl($url = null)
    {
       if (Request::is('/', 'login', 'register', 'lostpassword', 'ban', 'closed')) {
           return false;
       }
        $query = Request::has('return') ? Request::input('return') : Request::path();
        return '?return='.urlencode(is_null($url) ? $query : $url);
    }

    /**
     * Возвращает подключенный шаблон
     * @param $template
     * @param  array $params массив параметров
     * @param  boolean $return выводить или возвращать код
     * @return string сформированный код
     * @internal param string $view имя шаблона
     */
    public static function view($template, $params = [], $return = false)
    {
        $log    = static::user('login');
        $config = self::setting();

        $params +=compact('config', 'log');

        $blade = new Philo\Blade\Blade([APP.'/views', HOME.'/themes'], STORAGE.'/cache');

        if ($return) {
            return $blade->view()->make($template, $params)->render();
        } else {
            echo $blade->view()->make($template, $params)->render();
        }
    }

    /**
     * Сохраняет страницы с ошибками
     * @param  integer $code    код ошибки
     * @param  string  $message текст ошибки
     * @return string  сформированная страница с ошибкой
     */
    public static function abort($code, $message = '')
    {
        if ($code == 403) {
            header($_SERVER["SERVER_PROTOCOL"].' 403 Forbidden');

            if (App::setting('errorlog')) {
                DB::run()->query("INSERT INTO `error` (`num`, `request`, `referer`, `username`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?);", [403, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getUsername(), App::getClientIp(), App::getUserAgent(), SITETIME]);

                DB::run()->query("DELETE FROM `error` WHERE `num`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `error` WHERE `num`=? ORDER BY `time` DESC LIMIT " . App::setting('maxlogdat') . ") AS del);", [403, 403]);
            }
        }

        if ($code == 404) {
            header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');

            if (App::setting('errorlog')) {
                DB::run()->query("INSERT INTO `error` (`num`, `request`, `referer`, `username`, `ip`, `brow`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?);", [404, App::server('REQUEST_URI'), App::server('HTTP_REFERER'), App::getUsername(), App::getClientIp(), App::getUserAgent(), SITETIME]);

                DB::run()->query("DELETE FROM `error` WHERE `num`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `error` WHERE `num`=? ORDER BY `time` DESC LIMIT " . App::setting('maxlogdat') . ") AS del);", [404, 404]);
            }
        }

        exit(self::view('errors.'.$code, compact('message')));
    }



    /**
     * Переадресовывает пользователя
     * @param  string  $url адрес переадресации
     * @param  boolean $permanent постоянное перенаправление
     */
    public static function redirect($url, $permanent = false)
    {
        if ($permanent){
            header('HTTP/1.1 301 Moved Permanently');
        }
        if (isset($_SESSION['captcha'])) $_SESSION['captcha'] = null;

        exit(header('Location: '.$url));
    }

    /**
     * Сохраняет flash уведомления
     * @param string $status статус уведомления
     * @param array $message массив с уведомлениями
     */
    public static function setFlash($status, $message)
    {
        $_SESSION['flash'][$status] = $message;
    }

    /**
     * Возвращает flash уведомления
     * @return string сформированный блок с уведомлениями
     * @internal param array $errors массив уведомлений
     */
    public static function getFlash()
    {
        self::view('app._flash');
    }

    /**
     * Сохраняет POST данные введенных пользователем
     * @param array $data массив полей
     */
    public static function setInput($data)
    {
        $_SESSION['input'] = $data;
    }

    /**
     * Возвращает значение из POST данных
     * @param string $name имя поля
     * @param string $default
     * @return string сохраненный текст
     */
    public static function getInput($name, $default = '')
    {
        return isset($_SESSION['input'][$name]) ? $_SESSION['input'][$name] : $default;
    }

    /**
     * Подсвечивает блок с полем для ввода сообщения
     * @param string $field имя поля
     * @return string CSS класс ошибки
     */
    public static function hasError($field)
    {
        return isset($_SESSION['flash']['danger'][$field]) ? ' has-error' : '';
    }

    /**
     * Возвращает блок с текстом ошибки
     * @param  string $field имя поля
     * @return string блоки ошибки
     */
    public static function textError($field)
    {
        if (isset($_SESSION['flash']['danger'][$field])) {
            $error = $_SESSION['flash']['danger'][$field];
            return '<div class="text-danger">'.$error.'</div>';
        }
    }

    /**
     * Проверяет является ли email валидным
     * @param  string  $email адрес email
     * @return boolean результат проверки
     */
    public static function isMail($email)
    {
        return preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email);
    }

    /**
     * Отправка уведомления на email
     * @param  mixed   $to      Получатель
     * @param  string  $subject Тема письма
     * @param  string  $body    Текст сообщения
     * @param  array   $headers Дополнительные параметры
     * @return boolean  Результат отправки
     */
/*    public static function sendMail($to, $subject, $body, $headers = [])
    {
        if (empty($headers['from'])) $headers['from'] = [env('SITE_EMAIL') => env('SITE_ADMIN')];

        $message = Swift_Message::newInstance()
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body, 'text/html')
            ->setFrom($headers['from'])
            ->setReturnPath(env('SITE_EMAIL'));

        if (env('MAIL_DRIVER') == 'smtp') {
            $transport = Swift_SmtpTransport::newInstance(env('MAIL_HOST'), env('MAIL_PORT'), 'ssl')
                ->setUsername(env('MAIL_USERNAME'))
                ->setPassword(env('MAIL_PASSWORD'));
        } else {
            $transport = new Swift_MailTransport();
        }

        $mailer = new Swift_Mailer($transport);
        return $mailer->send($message);
    }*/

    /**
     * Возвращает форматированную дату
     * @param string $format отформатированная дата
     * @param mixed  $date временная метки или дата
     * @return string отформатированная дата
     */
    public static function date($format, $date = null)
    {
        $date = (is_null($date)) ? time() : strtotime($date);

        $eng = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $rus = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        return str_replace($eng, $rus, date($format, $date));
    }

    /**
     * Возвращает расширение файла
     * @param  string $filename имя файла
     * @return string расширение
     */
    public static function getExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Возвращает размер файла
     * @param  string  $filename путь к файлу
     * @param  integer $decimals кол. чисел после запятой
     * @return string            форматированный вывод размера
     */
    public static function filesize($filename, $decimals = 1)
    {
        if (!file_exists($filename)) return 0;

        $bytes = filesize($filename);
        $size = ['B','kB','MB','GB','TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        $unit = isset($size[$factor]) ? $size[$factor] : '';
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).$unit;
    }

    /**
     * Склоняет числа
     * @param  integer $num  число
     * @param  array   $forms массив склоняемых слов (один, два, много)
     * @return string  форматированная строка
     */
    public static function plural($num, array $forms)
    {
        if ($num % 100 > 10 &&  $num % 100 < 15) return $num.' '.$forms[2];
        if ($num % 10 == 1) return $num.' '.$forms[0];
        if ($num % 10 > 1 && $num %10 < 5) return $num.' '.$forms[1];
        return $num.' '.$forms[2];
    }

    /**
     * Валидирует даты
     * @param  string $date   дата
     * @param  string $format формат даты
     * @return boolean        результат валидации
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Обрабатывает BB-код
     * @param  string  $text  Необработанный текст
     * @param  boolean $parse Обрабатывать или вырезать код
     * @return string         Обработанный текст
     */
    public static function bbCode($text, $parse = true)
    {
        $bbcode = new BBCodeParser(self::setting());

        if ( ! $parse) return $bbcode->clear($text);

        $text = $bbcode->parse($text);
        $text = $bbcode->parseSmiles($text);

        return $text;
    }

    /**
     * Определяет браузер
     * @param null $userAgent
     * @return string браузер и версия браузера
     */
    public static function getUserAgent($userAgent = null)
    {
        $browser = new Browser();
        if ($userAgent) $browser->setUserAgent($userAgent);

        $brow = $browser->getBrowser();
        $version = implode('.', array_slice(explode('.', $browser->getVersion()), 0, 2));
        return $version == 'unknown' ? $brow : $brow.' '.$version;
    }

    /**
     * Определяет IP пользователя
     * @return string IP пользователя
     */
    public static function getClientIp()
    {
        $ip = Request::ip();
        return $ip == '::1' ? '127.0.0.1' : $ip;
    }

    public static function server($key = null, $default = null)
    {
        $server = Request::server($key, $default);
        if ($key == 'REQUEST_URI') $server = urldecode($server);
        if ($key == 'PHP_SELF') $server = current(explode('?', static::server('REQUEST_URI')));

        return check($server);
    }

    public static function getUsername()
    {
        return isset($_SESSION['login']) ? check($_SESSION['login']) : self::setting('guestsuser');
    }

    /**
     * Возвращает данные пользователя по ключу
     * @param  string $key ключ массива
     * @return string      данные
     */
    public static function user($key = null)
    {
        if (Registry::has('user')) {
            if (empty($key)) return Registry::get('user');

            return isset(Registry::get('user')[$key]) ? Registry::get('user')[$key] : '';
        }
    }

    /**
     * Возвращает настройки сайта по ключу
     * @param  string $key ключ массива
     * @return string      данные
     */
    public static function setting($key = null)
    {
        if (Registry::has('config')) {
            if (empty($key)) return Registry::get('config');

            return isset(Registry::get('config')[$key]) ? Registry::get('config')[$key] : '';
        }
    }

    /**
     * Авторизует пользователя
     * @param  string  $login    Логин или никнэйм
     * @param  string  $password Пароль пользователя
     * @param  boolean $remember Запомнить пароль
     * @return boolean           Результат авторизации
     */
    public static function login($login, $password, $remember = true)
    {
        $domain = check_string(self::setting('home'));

        if (!empty($login) && !empty($password)) {

            $user = DB::run()->queryFetch("SELECT `login`, `password` FROM `users` WHERE LOWER(`login`)=? OR LOWER(`nickname`)=? LIMIT 1;", [$login, $login]);

            /* Миграция старых паролей */
            if (preg_match('/^[a-f0-9]{32}$/', $user['password']))
            {
                if (md5(md5($password)) == $user['password']) {
                    $user['password'] = password_hash($password, PASSWORD_BCRYPT);
                    DBM::run()->update('users', [
                        'password' => $user['password'],
                    ], ['login' => $login]);
                }
            }

            if ($user && password_verify($password, $user['password'])) {

                if ($remember) {
                    setcookie('login', $user['login'], time() + 3600 * 24 * 365, '/', $domain);
                    setcookie('password', md5($user['password'].env('APP_KEY')), time() + 3600 * 24 * 365, '/', $domain, null, true);
                }

                $_SESSION['ip'] = self::getClientIp();
                $_SESSION['login'] = $user['login'];
                $_SESSION['password'] = md5(env('APP_KEY').$user['password']);

                // Сохранение привязки к соц. сетям
                if (!empty($_SESSION['social'])) {
                    DBM::run()->insert('socials', [
                        'user'    => $user['login'],
                        'network' => $_SESSION['social']->network,
                        'uid'     => $_SESSION['social']->uid,
                    ]);
                }

                DB::run()->query("UPDATE `users` SET `visits`=`visits`+1, `timelastlogin`=? WHERE `login`=?", [SITETIME, $user['login']]);

                $authorization = DB::run()->querySingle("SELECT `id` FROM `login` WHERE `user`=? AND `time`>? LIMIT 1;", [$user['login'], SITETIME - 30]);

                if (empty($authorization)) {
                    DB::run()->query("INSERT INTO `login` (`user`, `ip`, `brow`, `time`, `type`) VALUES (?, ?, ?, ?, ?);", [$user['login'], self::getClientIp(), self::getUserAgent(), SITETIME, 1]);
                    DB::run()->query("DELETE FROM `login` WHERE `user`=? AND `time` < (SELECT MIN(`time`) FROM (SELECT `time` FROM `login` WHERE `user`=? ORDER BY `time` DESC LIMIT 50) AS del);", [$user['login'], $user['login']]);
                }

                return $user;
            }
        }

        return false;
    }

    /**
     * Авторизует через социальные сети
     * @param string $token идентификатор Ulogin
     */
    public static function socialLogin($token)
    {
        $domain = check_string(self::setting('home'));

        $curl = new Curl\Curl();
        $network = $curl->get('http://ulogin.ru/token.php', [
            'token' => $token,
            'host' => $_SERVER['HTTP_HOST']
        ]);

        if ($network && empty($network->error)) {
            $_SESSION['social'] = $network;

            $social = DBM::run()->selectFirst('socials', ['network' => $network->network, 'uid' => $network->uid]);

            if ($social && $user = user($social['user'])) {

                setcookie('login', $user['login'], time() + 3600 * 24 * 365, '/', $domain);
                setcookie('password', md5($user['password'].env('APP_KEY')), time() + 3600 * 24 * 365, '/', $domain, null, true);

                $_SESSION['login'] = $user['login'];
                $_SESSION['password'] = md5(env('APP_KEY').$user['password']);
                $_SESSION['ip'] = App::getClientIp();

                self::setFlash('success', 'Добро пожаловать, '.$user['login'].'!');
                self::redirect('/');
            }
        }
    }

    /**
     * Генерирует постраничную навигация
     * @param  array  $page массив данных
     * @return string       сформированный блок
     */
    public static function pagination($page)
    {
        if ($page['total'] > 0) {

            if (empty($page['crumbs'])) $page['crumbs'] = 3;

            $url = array_except($_GET, 'page');
            $request = $url ? '&'.http_build_query($url) : null;

            $pages = [];
            $pg_cnt = ceil($page['total'] / $page['limit']);
            $idx_fst = max($page['current'] - $page['crumbs'], 1);
            $idx_lst = min($page['current'] + $page['crumbs'], $pg_cnt);

            if ($page['current'] != 1) {
                $pages[] = [
                    'page' => $page['current'] - 1,
                    'title' => 'Предыдущая',
                    'name' => '«',
                ];
            }

            if ($page['current'] > $page['crumbs'] + 1) {
                $pages[] = [
                    'page' => 1,
                    'title' => '1 страница',
                    'name' => 1,
                ];
                if ($page['current'] != $page['crumbs'] + 2) {
                    $pages[] = [
                        'separator' => true,
                        'name' => ' ... ',
                    ];
                }
            }

            for ($i = $idx_fst; $i <= $idx_lst; $i++) {
                if ($i == $page['current']) {
                    $pages[] = [
                        'current' => true,
                        'name' => $i,
                    ];
                } else {
                    $pages[] = [
                        'page' => $i,
                        'title' => $i.' страница',
                        'name' => $i,
                    ];
                }
            }

            if ($page['current'] < $pg_cnt - $page['crumbs']) {
                if ($page['current'] != $pg_cnt - $page['crumbs'] - 1) {
                    $pages[] = [
                        'separator' => true,
                        'name' => ' ... ',
                    ];
                }
                $pages[] = [
                    'page' => $pg_cnt,
                    'title' => $pg_cnt . ' страница',
                    'name' => $pg_cnt,
                ];
            }

            if ($page['current'] != $pg_cnt) {
                $pages[] = [
                    'page' => $page['current'] + 1,
                    'title' => 'Следующая',
                    'name' => '»',
                ];
            }

            self::view('app._pagination', compact('pages', 'request'));
        }
    }

    /**
     * Генерирует постраничную навигация для форума
     * @param  array  $topic массив данных
     * @return string       сформированный блок
     */
    public static function forumPagination($topic) {

        if ($topic['posts']) {

            $pages = [];
            $link = '/topic/'.$topic['id'];

            $pg_cnt = ceil($topic['posts'] / App::setting('forumpost'));

            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $pg_cnt) {
                    $pages[] = [
                        'page' => $i,
                        'title' => $i.' страница',
                        'name' => $i,
                    ];
                }
            }

            if (5 < $pg_cnt) {

                if (6 < $pg_cnt) {
                    $pages[] = array(
                        'separator' => true,
                        'name' => ' ... ',
                    );
                }

                $pages[] = array(
                    'page' => $pg_cnt,
                    'title' => $pg_cnt.' страница',
                    'name' => $pg_cnt,
                );
            }

            self::view('forum._pagination', compact('pages', 'link'));
        }
    }

    /**
     * Обрабатывает постраничную навигацию
     * @param  integer $limit элементов на страницу
     * @param  integer $total всего элементов
     * @return array          массив подготовленных данных
     */
    public static function paginate($limit, $total)
    {
        $current = Request::input('page');
        if ($current < 1) $current = 1;

        if ($total && $current * $limit >= $total) {
            $current = ceil($total / $limit);
        }

        $offset = intval(($current * $limit) - $limit);

        return compact('current', 'offset', 'limit', 'total');
    }

    /**
     * Устанавливает права доступа на папки
     */
    public static function install()
    {
        $storage = glob(dirname(__DIR__)."/storage/*", GLOB_ONLYDIR);
        $uploads = glob(dirname(dirname(__DIR__))."/public/upload/*", GLOB_ONLYDIR);

        $dirs = array_merge($storage, $uploads);

        foreach ($dirs as $dir) {
            $old = umask(0);
            chmod ($dir, 0777);
            umask($old);
        }
    }
}
