<?php

class App
{
    /**
     * Возвращает текущую страницу
     *
     * @param null $url
     * @return string текущая страница
     */
    public static function returnUrl($url = null)
    {
       if (Request::is('/', 'login', 'register', 'recovery', 'ban', 'closed')) {
           return false;
       }
        $query = Request::has('return') ? Request::input('return') : Request::path();
        return '?return='.urlencode(is_null($url) ? $query : $url);
    }

    /**
     * Возвращает подключенный шаблон
     *
     * @param $template
     * @param  array $params массив параметров
     * @param  boolean $return выводить или возвращать код
     * @return string сформированный код
     * @internal param string $view имя шаблона
     */
    public static function view($template, $params = [], $return = false)
    {
        $blade = new Jenssegers\Blade\Blade([APP.'/views', HOME.'/themes'], STORAGE.'/cache');

        if ($return) {
            return $blade->render($template, $params);
        } else {
            echo $blade->render($template, $params);
        }
    }

    /**
     * Сохраняет страницы с ошибками
     *
     * @param  integer $code    код ошибки
     * @param  string  $message текст ошибки
     * @return string  сформированная страница с ошибкой
     */
    public static function abort($code, $message = null)
    {
        if ($code == 403) {
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
        }

        if ($code == 404) {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        }

        if (Setting::get('errorlog') && in_array($code, [403, 404])) {

            $error = new Log();
            $error->code = $code;
            $error->request = utf_substr(App::server('REQUEST_URI'), 0, 200);
            $error->referer = utf_substr(App::server('HTTP_REFERER'), 0, 200);
            $error->user_id = App::getUserId();
            $error->ip = App::getClientIp();
            $error->brow = App::getUserAgent();
            $error->created_at = SITETIME;
            $error->save();

            Log::where('code', $code)
                ->where('created_at', '<', SITETIME - 3600 * 24 * Setting::get('maxlogdat'))
                ->delete();
        }

        if (Request::ajax()) {
            header($_SERVER['SERVER_PROTOCOL'].' 200 OK');

            exit(json_encode([
                'status' => 'error',
                'message' => $message,
            ]));
        }

        $referer = Request::header('referer') ?? null;

        exit(self::view('errors/'.$code, compact('message', 'referer')));
    }



    /**
     * Переадресовывает пользователя
     *
     * @param  string  $url адрес переадресации
     * @param  boolean $permanent постоянное перенаправление
     */
    public static function redirect($url, $permanent = false)
    {
        if (isset($_SESSION['captcha'])) {
            $_SESSION['captcha'] = null;
        }

        if ($permanent){
            header($_SERVER['SERVER_PROTOCOL'].' 301 Moved Permanently');
        }

        exit(header('Location: '.$url));
    }

    /**
     * Сохраняет flash уведомления
     *
     * @param string $status статус уведомления
     * @param mixed $message массив или текст с уведомлениями
     */
    public static function setFlash($status, $message)
    {
        $_SESSION['flash'][$status] = $message;
    }

    /**
     * Возвращает flash уведомления
     *
     * @return string сформированный блок с уведомлениями
     * @internal param array $errors массив уведомлений
     */
    public static function getFlash()
    {
        self::view('app/_flash');
    }

    /**
     * Сохраняет POST данные введенных пользователем
     *
     * @param array $data массив полей
     */
    public static function setInput(array $data)
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
    public static function getInput($name, $default = '')
    {
        return $_SESSION['input'][$name] ?? $default;
    }

    /**
     * Подсвечивает блок с полем для ввода сообщения
     *
     * @param string $field имя поля
     * @return string CSS класс ошибки
     */
    public static function hasError($field)
    {
        return isset($_SESSION['flash']['danger'][$field]) ? ' has-error' : '';
    }

    /**
     * Возвращает блок с текстом ошибки
     *
     * @param  string $field имя поля
     * @return string        блоки ошибки
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
     *
     * @param  string  $email адрес email
     * @return boolean результат проверки
     */
    public static function isMail($email)
    {
        return preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email);
    }

    /**
     * Отправка уведомления на email
     *
     * @param  mixed   $to      Получатель
     * @param  string  $subject Тема письма
     * @param  string  $body    Текст сообщения
     * @param  array   $params  Дополнительные параметры
     * @return boolean          Результат отправки
     */
    public static function sendMail($to, $subject, $body, $params = [])
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

        if (isset($params['subscribe'])) {
            $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.env('SITE_EMAIL').'>, <'.Setting::get('home').'/unsubscribe?key='.$params['subscribe'].'>');

            $body = str_replace('<!-- unsubscribe -->', '<br /><br /><small>Если вы не хотите получать эти email, пожалуйста, <a href="'.Setting::get('home').'/unsubscribe?key='.$params['subscribe'].'">откажитесь от подписки</a></small>', $body);
            $message->setBody($body, 'text/html');
        }

        if (env('MAIL_DRIVER') == 'smtp') {
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
     * Возвращает форматированную дату
     *
     * @param string $format отформатированная дата
     * @param mixed  $date временная метки или дата
     * @return string отформатированная дата
     */
    public static function date($format, $date = null)
    {
        $date = (is_null($date)) ? SITETIME : strtotime($date);

        $eng = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $rus = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
        return str_replace($eng, $rus, date($format, $date));
    }

    /**
     * Возвращает расширение файла
     *
     * @param  string $filename имя файла
     * @return string расширение
     */
    public static function getExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Возвращает размер файла
     *
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
     *
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
     *
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
     *
     * @param  string  $text  Необработанный текст
     * @param  boolean $parse Обрабатывать или вырезать код
     * @return string         Обработанный текст
     */
    public static function bbCode($text, $parse = true)
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
     * Определяет браузер
     *
     * @param string|null $userAgent
     * @return string браузер и версия браузера
     */
    public static function getUserAgent($userAgent = null)
    {
        $browser = new Browser();
        if ($userAgent) {
            $browser->setUserAgent($userAgent);
        }

        $brow = $browser->getBrowser();
        $version = implode('.', array_slice(explode('.', $browser->getVersion()), 0, 2));
        return mb_substr($version == 'unknown' ? $brow : $brow.' '.$version, 0, 25, 'utf-8');
    }

    /**
     * Определяет IP пользователя
     *
     * @return string IP пользователя
     */
    public static function getClientIp()
    {
        $ip = Request::ip();
        return $ip == '::1' ? '127.0.0.1' : $ip;
    }

    /**
     * Возвращает серверные переменные
     *
     * @param string|null $key     ключ массива
     * @param string|null $default значение по умолчанию
     * @return mixed               данные
     */
    public static function server($key = null, $default = null)
    {
        $server = Request::server($key, $default);
        if ($key == 'REQUEST_URI') $server = urldecode($server);
        if ($key == 'PHP_SELF') $server = current(explode('?', static::server('REQUEST_URI')));

        return check($server);
    }

    /**
     * Возвращает логин пользователя
     *
     * @return string
     */
    public static function getUsername()
    {
        return self::user('login') ? self::user('login') : Setting::get('guestsuser');
    }

    /**
     * Возвращает ID пользователя
     *
     * @return int
     */
    public static function getUserId()
    {
        return isset($_SESSION['id']) ? intval($_SESSION['id']) : 0;
    }

    /**
     * Возвращает данные пользователя по ключу
     *
     * @param  string $key ключ массива
     * @return string      данные
     */
    public static function user($key = null)
    {
        if (Registry::has('user')) {
            if (empty($key)) {
                return Registry::get('user');
            } else {
                return Registry::get('user')[$key] ?? null;
            }
        }

        return null;
    }

    /**
     * Авторизует пользователя
     *
     * @param  string  $login    Логин
     * @param  string  $password Пароль пользователя
     * @param  boolean $remember Запомнить пароль
     * @return boolean           Результат авторизации
     */
    public static function login($login, $password, $remember = true)
    {
        $domain = check_string(Setting::get('home'));

        if (!empty($login) && !empty($password)) {

            $user = User::whereRaw('LOWER(login) = ?', [$login])
                ->first();

            /* Миграция старых паролей */
            if (preg_match('/^[a-f0-9]{32}$/', $user['password']))
            {
                if (md5(md5($password)) == $user['password']) {
                    $user['password'] = password_hash($password, PASSWORD_BCRYPT);

                    $user = User::where('login', $user['login'])->first();
                    $user->password = $user['password'];
                    $user->save();
                }
            }

            if ($user && password_verify($password, $user['password'])) {

                if ($remember) {
                    setcookie('login', $user['login'], SITETIME + 3600 * 24 * 365, '/', $domain);
                    setcookie('password', md5($user['password'].env('APP_KEY')), SITETIME + 3600 * 24 * 365, '/', $domain, null, true);
                }

                $_SESSION['id'] = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY').$user->password);

                // Сохранение привязки к соц. сетям
                if (!empty($_SESSION['social'])) {

                    $social = new Social();
                    $social->user = $user->login;
                    $social->network = $_SESSION['social']->network;
                    $social->uid = $_SESSION['social']->uid;
                    $social->save();
                }

                $authorization = Login::where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::create([
                        'user_id' => $user->id,
                        'ip' => self::getClientIp(),
                        'brow' => self::getUserAgent(),
                        'created_at' => SITETIME,
                        'type' => 1,
                    ]);

                    Capsule::delete('
                        DELETE FROM login WHERE created_at < (
                            SELECT MIN(created_at) FROM (
                                SELECT created_at FROM guest ORDER BY created_at DESC LIMIT 50
                            ) AS del
                        );'
                    );
                }

                $user->update([
                    'visits' => Capsule::raw('visits + 1'),
                    'timelastlogin' => SITETIME
                ]);

                return $user;
            }
        }

        return false;
    }

    /**
     * Авторизует через социальные сети
     *
     * @param string $token идентификатор Ulogin
     */
    public static function socialLogin($token)
    {
        $domain = check_string(Setting::get('home'));

        $curl = new Curl\Curl();
        $network = $curl->get('http://ulogin.ru/token.php', [
            'token' => $token,
            'host' => $_SERVER['HTTP_HOST']
        ]);

        if ($network && empty($network->error)) {
            $_SESSION['social'] = $network;

            $social = ORM::for_table('socials')->
                where(['network' => $network->network, 'uid' => $network->uid])->
                find_one();

            if ($social && $user = user($social['user'])) {

                setcookie('login', $user['login'], SITETIME + 3600 * 24 * 365, '/', $domain);
                setcookie('password', md5($user['password'].env('APP_KEY')), SITETIME + 3600 * 24 * 365, '/', $domain, null, true);

                $_SESSION['id'] = $user['id'];
                $_SESSION['password'] = md5(env('APP_KEY').$user['password']);

                self::setFlash('success', 'Добро пожаловать, '.$user['login'].'!');
                self::redirect('/');
            }
        }
    }

    /**
     * Генерирует постраничную навигация
     *
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
     * Обрабатывает постраничную навигацию
     *
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
     *
     * @return void
     */
    public static function install()
    {
        $storage = glob(dirname(__DIR__).'/storage/*', GLOB_ONLYDIR);
        $uploads = glob(dirname(dirname(__DIR__)).'/public/uploads/*', GLOB_ONLYDIR);

        $dirs = array_merge($storage, $uploads);

        foreach ($dirs as $dir) {
            $old = umask(0);
            chmod ($dir, 0777);
            umask($old);
        }
    }

    /**
     * Возвращает сформированный код base64 картинки
     *
     * @param string  $path   путь к картинке
     * @param array   $params параметры
     * @return string         сформированный код
     */
    public static function imageBase64($path, array $params = [])
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        if (! isset($params['class'])) {
            $params['class'] = 'img-responsive';
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
     * @param int  $percent
     * @param bool $title
     */
    public static function progressBar($percent, $title = false)
    {
        if (! $title){
            $title = $percent.'%';
        }
        echo '<div class="progress">
            <div class="progress-bar progress-bar-warning" style="width:'.$percent.'%;"></div>
            <span class="progress-completed">'.$title.'</span>
        </div>';
    }

    /**
     * Инициализирует языковую локализацию
     *
     * @param  string $locale
     * @param  string $fallback
     * @return \Illuminate\Translation\Translator
     */
    public static function translator($locale = 'ru', $fallback = 'en')
    {
        $translator = new \Illuminate\Translation\Translator(
            new \Illuminate\Translation\FileLoader(
                new \Illuminate\Filesystem\Filesystem(),
                APP.'/lang'
            ),
            $locale
        );
        $translator->setFallback($fallback);

        return $translator;
    }

    /**
     * Выводит список забаненных ip
     *
     * @param  boolean $save нужно ли сбросить кеш
     * @return array         массив IP
     */
    public static function ipBan($save = false)
    {
        if (! $save && file_exists(STORAGE.'/temp/ipban.dat')) {
            $ipBan = unserialize(file_get_contents(STORAGE.'/temp/ipban.dat'));
        } else {
            $ipBan = Ban::pluck('ip')->all();
            file_put_contents(STORAGE."/temp/ipban.dat", serialize($ipBan), LOCK_EX);
        }

        return $ipBan;
    }
}
