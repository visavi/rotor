<?php
ob_start();

$level = 0;
$folder_level = '';
while (!file_exists($folder_level.'app') && $level < 5) {
    $folder_level .= '../';
    ++$level;
}
unset($level);

define('DIR', rtrim($folder_level, '/'));

include_once DIR.'/app/bootstrap.php';
include_once DIR.'/app/helpers.php';

function parsePHPModules() {
    ob_start();
    phpinfo(INFO_MODULES);
    $s = ob_get_contents();
    ob_end_clean();
    $s = strip_tags($s, '<h2><th><td>');
    $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s);
    $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s);
    $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    $vModules = [];
    for ($i = 1;$i < count($vTmp);$i++) {
        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $vTmp[$i], $vMat)) {
            $vName = trim($vMat[1]);
            $vTmp2 = explode("\n", $vTmp[$i + 1]);
            foreach ($vTmp2 AS $vOne) {
                $vPat = '<info>([^<]+)<\/info>';
                $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                $vPat2 = "/$vPat\s*$vPat/";
                if (preg_match($vPat3, $vOne, $vMat)) {
                    $vModules[$vName][trim($vMat[1])] = [trim($vMat[2]), trim($vMat[3])];
                } elseif (preg_match($vPat2, $vOne, $vMat)) {
                    $vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
                }
            }
        }
    }
    return $vModules;
}
// ------------------------------------------------------------------//
function getModuleSetting($pModuleName, $pSettings) {
    $vModules = parsePHPModules();
    if (is_array($pSettings)) {
        foreach ($pSettings as $pSetting) {
            if (isset($vModules[$pModuleName][$pSetting])) {
                return $vModules[$pModuleName][$pSetting];
            }
        }
    } else {
        if (isset($vModules[$pModuleName][$pSettings])) {
            return $vModules[$pModuleName][$pSettings];
        }
    }
    return 'Не определено';
}

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('RotorCMS by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', DIR.'/phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>
        Обновление RotorCMS
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="image_src" href="/assets/img/images/icon.png" />
    <link rel="stylesheet" href="/themes/default/css/style.css" type="text/css" />
</head>
<body>

<div class="cs" id="up">
    <a href="/"><img src="/assets/img/images/logo.png" /></a>
</div>
<div class="site">

    <?php if (empty($_GET['act'])): ?>

        <h1>Шаг 1 - проверка требований</h1>
        <p>Для установки вам необходимо прописать данные от БД в файл .env</p>

        <?php
        $keys = [
            'APP_ENV',
            'DB_DRIVER',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'SITE_ADMIN',
            'SITE_EMAIL',
        ];

        foreach ($keys as $key) {
            echo $key.' - '.env($key).'<br />';
        }
        ?>
        <p>Не забудьте изменить значение APP_KEY, эти данные необходимы для шифрования cookies и паролей в сессиях</p>

        <p>Минимальная версия PHP необходимая для работы движка PHP 5.6.4 и MySQL 5.5.3</p>

        <p style="font-size: 15px; font-weight: bold">Проверка требований</p>
        <?php
        $error_setting = 0;

        if (version_compare(PHP_VERSION, '5.6.4') > 0) {
            echo '<i class="fa fa-plus-circle"></i> Версия PHP 5.6.4 и выше: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . strtok(phpversion(), '-') . ')<br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Версия PHP 5.6.4 и выше: <b><span style="color:#ff0000">Ошибка</span></b>  (Версия ' . strtok(phpversion(), '-') . ')<br />';
        $error_critical = 1;
        }

        if (extension_loaded('pdo_mysql')) {

            $version = strtok(getModuleSetting('pdo_mysql', ['Client API version', 'PDO Driver for MySQL, client library version']), '-');
            echo '<i class="fa fa-plus-circle"></i> Расширение PDO-MySQL ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение PDO-MySQL: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
            $error_critical = 1;
        }

        if (extension_loaded('openssl')) {
            $version = getModuleSetting('openssl', ['OpenSSL Library Version', 'OpenSSL Header Version']);
            echo '<i class="fa fa-plus-circle"></i> Расширение OpenSSL ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение OpenSSL: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
            $error_critical = 1;
        }

        if (extension_loaded('tokenizer')) {
            echo '<i class="fa fa-plus-circle"></i> Расширение Tokenizer: <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение Tokenizer: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
            $error_critical = 1;
        }

        if (extension_loaded('mbstring')) {
            $version = getModuleSetting('mbstring', ['oniguruma version', 'Multibyte regex (oniguruma) version']);
            echo '<i class="fa fa-plus-circle"></i> Расширение Mbstring ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение Mbstring: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
            $error_critical = 1;
        }

        if (extension_loaded('xml')) {
            $version = getModuleSetting('xml', 'libxml2 Version');
            echo '<i class="fa fa-plus-circle"></i> Расширение XML ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение XML: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br />';
            $error_critical = 1;
        }

        if (extension_loaded('gd')) {
            $version = getModuleSetting('gd', ['GD headers Version', 'GD library Version']);
            echo '<i class="fa fa-plus-circle"></i> Библиотека GD ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Библиотека GD: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br />';
            $error_setting++;
        }

        if (extension_loaded('curl')) {
            $version = getModuleSetting('curl', 'cURL Information');
            echo '<i class="fa fa-plus-circle"></i> Библиотека Curl ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br />';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Библиотека Curl: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br />';
            $error_setting++;
        }

        echo 'Для обработка видео желательно установить библиотеку FFmpeg<br />';

        echo '<br /><p style="font-size: 15px; font-weight: bold">Права доступа</p>';

        $storage = glob(DIR.'/app/storage/*', GLOB_ONLYDIR);
        $uploads = glob(DIR.'/uploads/*', GLOB_ONLYDIR);

        $dirs = array_merge($storage, $uploads);

        $chmod_errors = 0;

        foreach ($dirs as $dir) {
            if (is_writable($dir)) {
                $file_status = '<span style="color:#00cc00">ОК</span>';
            } else {
                $old = umask(0);
                @chmod ($dir, 0777);
                umask($old);
                if (is_writable($dir)) {
                    $file_status = '<span style="color:#00cc00">ОК</span>';
                } else {
                    $file_status = '<span style="color:#ff0000">Запрещено</span>';
                    $chmod_errors = 1;
                }
            }
            $chmod_value = @decoct(@fileperms($dir)) % 1000;

            echo '<i class="fa fa-check-circle"></i> '.str_replace('../', '', $dir).' <b> - ' . $file_status . '</b> (chmod ' . $chmod_value . ')<br />';
        }

        echo '<br />Дополнительно можете выставить права на директории и файы с шаблонами внутри app/views<br /><br />';

        echo 'Если какой-то пункт выделен красным, необходимо зайти по FTP и выставить CHMOD разрешающую запись<br />';
        echo 'Некоторые настройки являются рекомендуемыми для полной совместимости, однако скрипт способен работать даже если рекомендуемые настройки не совпадают с текущими.<br /><br />';

        if (empty($error_critical)  && empty($chmod_errors)) {
            echo '<i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Вы можете продолжить установку движка!</span></b><br /><br />';

            if (empty($error_setting)) {
                echo 'Все модули и библиотеки присутствуют, настройки корректны, необходимые файлы и папки доступны для записи<br /><br />';
            } else {
                echo '<b><span style="color:#ffa500">У вас имеются предупреждения!</span></b> (Всего: ' . $error_setting . ')<br />';
                echo 'Данные предупреждения не являются критическими, но тем не менее для полноценной, стабильной и безопасной работы движка желательно их устранить<br />';
                echo 'Вы можете продолжить установку скрипта, но нет никаких гарантий, что движок будет работать стабильно<br /><br />';
            }

            echo '<span style="color:#ff0000">Внимание, база данных должна быть создана в кодировке utf8mb4_unicode_ci</span><br /><br />';

            echo '<p><a style="font-size: 18px" href="?act=status">Проверить статус</a></p><br />';
        } else {
            echo '<b><span style="color:#ff0000">Имеются критические ошибки!</span></b><br />';
            echo 'Вы не сможете приступить к установке, пока не устраните все ошибки<br /><br />';
        }

        ?>


    <?php elseif($_GET['act'] == 'status'): ?>
        <h1>Шаг 2 - проверка статуса</h1>
        <pre>
            <span class="inner-pre" style="font-size: 11px">
                <?= $wrap->getStatus(); ?>
            </span>
        </pre>
        <p><a style="font-size: 18px" href="?act=migrate">Выполнить миграции</a></p>

    <?php elseif($_GET['act'] == 'migrate'): ?>
        <h1>Шаг 3 - выполнение миграций</h1>
        <pre>
            <span class="inner-pre" style="font-size: 11px">
                <?= $wrap->getMigrate(); ?>
            </span>
        </pre>

        <p><a style="font-size: 18px" href="?act=seed">Заполнить БД</a></p>

    <?php elseif($_GET['act'] == 'seed'): ?>

        <h1>Шаг 4 - заполнение БД</h1>

        <pre>
            <span class="inner-pre" style="font-size: 11px">
                <?= $wrap->getSeed(); ?>
            </span>
        </pre>

        <p><a style="font-size: 18px" href="?act=account">Создать администратора</a></p>
    <?php elseif($_GET['act'] == 'account'): ?>

        <h1>Шаг 5 - создание администратора</h1>

        Прежде чем перейти к администрированию вашего сайта, необходимо создать аккаунт администратора.<br />
        Перед тем как нажимать кнопку Создать, убедитесь, что на предыдущей странице нет уведомлений об ошибках, иначе процесс не сможет быть завершен удачно.<br />
        После окончания инсталляции необходимо удалить директории <b>install</b> и <b>upgrade</b> со всем содержимым навсегда, пароль и остальные данные вы сможете поменять в своем профиле<br /><br />

        <?php
            $servername = isset($_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_HOST']) : htmlspecialchars($_SERVER['SERVER_NAME']);
            $servername = 'http://'.$servername;

        $login = isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '';
        $password = isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '';
        $password2 = isset($_POST['password2']) ? ($_POST['password2']) : '';
        $email = isset($_POST['email']) ? strtolower(htmlspecialchars($_POST['email'])) : '';
        $site = isset($_POST['site']) ? utf_lower(htmlspecialchars($_POST['site'])) : $servername;
        ?>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <?php
            if (strlen($login) <= 20 && strlen($login) >= 3) {
            if (preg_match('|^[a-z0-9\-]+$|i', $login)) {
            if ($password == $password2) {
            if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email)) {
            if (preg_match('#^https?://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)?+$#u', $site)) {

            // Проверка логина или ника на существование
            $reglogin = DB::run()->querySingle("SELECT `id` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($login)]);
            if (!$reglogin) {

            // Проверка email на существование
            $regmail = DB::run()->querySingle("SELECT `id` FROM `users` WHERE `email`=? LIMIT 1;", [$email]);
            if (!$regmail) {

                $registration = new User();
                $registration->login = $login;
                $registration->password = password_hash($password, PASSWORD_BCRYPT);
                $registration->email = $email;
                $registration->joined = SITETIME;
                $registration->level = 101;
                $registration->gender = 1;
                $registration->themes = 0;
                $registration->point = 500;
                $registration->money = 1000000;
                $registration->info = 'Администратор сайта';
                $registration->status = 'Администратор';
                $registration->save();

                Setting::where('name', 'login')->update(['value' => $login]);
                Setting::where('name', 'email')->update(['value' => $email]);
                Setting::where('name', 'site')->update(['value' => $site]);

                save_setting();

                // -------------- Приват ---------------//
                $textpriv = 'Привет, ' . $login . '! Поздравляем с успешной установкой нашего движка RotorCMS.'.PHP_EOL.'Новые версии, апгрейды, а также множество других дополнений вы найдете на нашем сайте [url=http://visavi.net]VISAVI.NET[/url]';

                $inbox = Inbox::create([
                    'user'   => $login,
                    'author' => 'Vantuz',
                    'text'   => $textpriv,
                    'time'   => SITETIME,
                ]);

                // -------------- Новость ---------------//
                $textnews = 'Добро пожаловать на демонстрационную страницу движка RotorCMS'.PHP_EOL.'RotorCMS - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта. RotorCMS является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности'.PHP_EOL.'Главной особенностью RotorCMS является низкая нагрузка на системные ресурсы, даже при очень большой аудитории сайта нагрузка не сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.'.PHP_EOL.'Движок RotorCMS вы можете скачать на официальном сайте [url=http://visavi.net]VISAVI.NET[/url]';

                $news = News::create([
                    'title'  => 'Добро пожаловать!',
                    'text'  => $textnews,
                    'author' => $login,
                    'time' => SITETIME,
                ]);

                redirect('?act=finish');


            } else {echo '<p style="color: #ff0000">Ошибка! Указанный вами адрес e-mail уже используется в системе!</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Пользователь с данным логином или ником уже зарегистрирован!</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Неправильный адрес сайта, необходим формата http://my_site.domen</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Неправильный адрес email, необходим формат name@site.domen</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Веденные пароли отличаются друг от друга</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Недопустимые символы в логине. Разрешены только знаки латинского алфавита и цифры!</p>';}
            } else {echo '<p style="color: #ff0000">Ошибка! Слишком длинный или короткий логин (От 3 до 20 символов)</p>';}

            ?>
        <?php endif; ?>

        <div class="form">
           <form method="post">
                Логин (max20):<br />
                <input class="form-control" name="login" maxlength="20" value="<?= $login ?>" /><br />
                Пароль(max20):<br />
                <input class="form-control" name="password" type="password" maxlength="50" /><br />
                Повторите пароль:<br />
                <input class="form-control" name="password2" type="password" maxlength="50" /><br />
                Адрес e-mail:<br />
                <input class="form-control" name="email" maxlength="100" value="<?= $email ?>" /><br />
                Адрес сайта:<br />
                <input name="site" value="<?= $site ?>" maxlength="100" /><br /><br />
               <button type="submit" class="btn btn-primary">Создать</button>
            </form>
        </div><br />

        Внимание, в поле логин разрешены только знаки латинского алфавита, цифры и знак дефис<br />
        Все поля обязательны для заполнения<br />
        E-mail будет нужен для восстановления пароля, пишите только свои данные<br />
        Не нажимайте кнопку дважды, подождите до тех пор, пока процесс не завершится<br />
        В поле ввода адреса сайта необходимо ввести адрес в который у вас распакован движок, если это поддомен или папка, то необходимо указать ее, к примеру http://wap.visavi.net<br /><br />


    <?php else: ?>

        <h1>Установка завершена</h1>

        <p>
            Поздравляем Вас, RotorCMS был успешно установлен на Ваш сервер. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br /><br />
            Аккаунт администратора создан<br /><br />
            <a href="/">Перейти на главную страницу сайта</a><br />
        </p>
        <p style="font-size: 20px">Удалите директории install и upgrade</p>


    <?php endif; ?>

</div>
</body>
</html>
