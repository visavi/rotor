<?php

use App\Classes\Request;
use App\Models\News;
use App\Models\User;

ob_start();

$level = 0;
$folder_level = '';
while (!file_exists($folder_level.'app') && $level < 5) {
    $folder_level .= '../';
    ++$level;
}
unset($level);

define('DIR', rtrim($folder_level, '/'));

include_once DIR . '/app/bootstrap.php';
include_once DIR . '/app/helpers.php';

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
    for ($i = 1; $i < count($vTmp); $i++) {
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

$app->setName('Rotor by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', DIR.'/app/migration.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        <?= env('APP_NEW') ? 'Установка' : 'Обновление' ?> Rotor
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="stylesheet" href="/themes/default/css/style.css">
</head>
<body>

<div class="cs" id="up">
    <a href="/"><img src="/assets/img/images/logo.png"></a>
</div>
<div class="site">

    <?php if (! Request::has('act')): ?>

        <h1>Шаг 1 - проверка требований</h1>

        <p style="color:#ff0000">
            Если в процессе установки движка произойдет какая-либо ошибка, чтобы узнать причину ошибки включите вывод ошибок, измените значение APP_DEBUG на true
        </p>

        <p style="color:#ff0000">
            Если вы обновляетесь с предыдущей версии Rotor вам необходимо провести обновление базы данных, для этого измените значение APP_NEW на false и обновите текущую страницу</p>

        <p>Для установки вам необходимо прописать данные от БД в файл .env</p>

        <?php
        $keys = [
            'APP_ENV',
            'APP_NEW',
            'APP_DEBUG',
            'DB_DRIVER',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_CHARSET',
            'DB_COLLATION',
            'SITE_ADMIN',
            'SITE_EMAIL',
            'SITE_URL',
        ];

        foreach ($keys as $key) {
            echo $key.' - '.env($key).'<br>';
        }
        ?>
        <p>Не забудьте изменить значение APP_KEY, эти данные необходимы для шифрования cookies и паролей в сессиях</p>

        <p>Минимальная версия PHP необходимая для работы движка PHP 7.1.3 и MySQL 5.5.3</p>

        <p style="font-size: 15px; font-weight: bold">Проверка требований</p>
        <?php
        $errorSettings = 0;

        if (version_compare(PHP_VERSION, '7.1.3') > 0) {
            echo '<i class="fa fa-plus-circle"></i> Версия PHP 7.1.3 и выше: <b><span style="color:#00cc00">ОК</span></b> (Версия ' . parseVersion(PHP_VERSION) . ')<br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Версия PHP 7.1.3 и выше: <b><span style="color:#ff0000">Ошибка</span></b>  (Версия ' . parseVersion(PHP_VERSION) . ')<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('pdo_mysql')) {

            $version = strtok(getModuleSetting('pdo_mysql', ['Client API version', 'PDO Driver for MySQL, client library version']), '-');
            echo '<i class="fa fa-plus-circle"></i> Расширение PDO-MySQL ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение PDO-MySQL: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('openssl')) {
            $version = getModuleSetting('openssl', ['OpenSSL Library Version', 'OpenSSL Header Version']);
            echo '<i class="fa fa-plus-circle"></i> Расширение OpenSSL ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение OpenSSL: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('tokenizer')) {
            echo '<i class="fa fa-plus-circle"></i> Расширение Tokenizer: <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение Tokenizer: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('mbstring')) {
            $version = getModuleSetting('mbstring', ['oniguruma version', 'Multibyte regex (oniguruma) version']);
            echo '<i class="fa fa-plus-circle"></i> Расширение Mbstring ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение Mbstring: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('xml')) {
            $version = getModuleSetting('xml', 'libxml2 Version');
            echo '<i class="fa fa-plus-circle"></i> Расширение XML ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Расширение XML: <b><span style="color:#ff0000">Ошибка</span></b> (Расширение не загружено)<br>';
            $errorCritical = 1;
        }

        if (extension_loaded('gd')) {
            $version = getModuleSetting('gd', ['GD headers Version', 'GD library Version']);
            echo '<i class="fa fa-plus-circle"></i> Библиотека GD ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Библиотека GD: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br>';
            $errorSettings++;
        }

        if (extension_loaded('curl')) {
            $version = getModuleSetting('curl', 'cURL Information');
            echo '<i class="fa fa-plus-circle"></i> Библиотека Curl ('.$version.'): <b><span style="color:#00cc00">ОК</span></b><br>';
        } else {
            echo '<i class="fa fa-minus-circle"></i> Библиотека Curl: <b><span style="color:#ffa500">Предупреждение</span></b> (Библиотека не загружена)<br>';
            $errorSettings++;
        }

        echo 'Для обработка видео желательно установить библиотеку FFmpeg<br>';

        echo '<br><p style="font-size: 15px; font-weight: bold">Права доступа</p>';

        $uploadDir = file_exists(DIR . '/public/uploads') ? DIR . '/public/uploads' : DIR . '/uploads';

        $storage = glob(DIR . '/storage/*', GLOB_ONLYDIR);
        $uploads = glob($uploadDir . '/*', GLOB_ONLYDIR);

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

            echo '<i class="fa fa-check-circle"></i> '.str_replace('../', '', $dir).' <b> - ' . $file_status . '</b> (chmod ' . $chmod_value . ')<br>';
        }
?>
        <br>Дополнительно можете выставить права на директории и файлы с шаблонами внутри resources/views<br><br>

        Если какой-то пункт выделен красным, необходимо зайти по FTP и выставить CHMOD разрешающую запись<br>
        Некоторые настройки являются рекомендуемыми для полной совместимости, однако скрипт способен работать даже если рекомендуемые настройки не совпадают с текущими.<br><br>

        <?php if (empty($errorCritical) && empty($chmod_errors)): ?>
            <i class="fa fa-check-circle"></i> <b><span style="color:#00cc00">Вы можете продолжить установку движка!</span></b><br><br>

            <?php if (empty($errorSettings)): ?>
                Все модули и библиотеки присутствуют, настройки корректны, необходимые файлы и папки доступны для записи<br><br>
            <?php else: ?>
                <b><span style="color:#ffa500">У вас имеются предупреждения!</span></b> (Всего: <?= $errorSettings ?>)<br>
                Данные предупреждения не являются критическими, но тем не менее для полноценной, стабильной и безопасной работы движка желательно их устранить<br>
                Вы можете продолжить установку скрипта, но нет никаких гарантий, что движок будет работать стабильно<br><br>
            <?php endif; ?>

            <span style="color:#ff0000">
                Внимание, кодировка таблиц настраивается в файле .env,<br>
                Требуемая кодировка БД utf8mb4_unicode_ci<br>
                По умолчанию БД будет создана в кодировке <?= env('DB_COLLATION') ?>
            </span>
            <br><br>

            <p><a style="font-size: 18px" href="?act=status">Проверить статус</a> (Выполняется <?= env('APP_NEW') ? 'установка' : 'обновление' ?>)</p><br>
        <?php else: ?>
            <b><span style="color:#ff0000">Имеются критические ошибки!</span></b><br>
            Вы не сможете приступить к установке, пока не устраните все ошибки<br><br>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (env('APP_NEW')): ?>
        <?php if (Request::input('act') === 'status'): ?>
            <h1>Шаг 2 - проверка статуса (установка)</h1>

            <?= nl2br($wrap->getStatus()); ?>

            <p><a style="font-size: 18px" href="?act=migrate">Выполнить миграции</a></p>

        <?php elseif(Request::input('act') === 'migrate'): ?>
            <h1>Шаг 3 - выполнение миграций (установка)</h1>

            <?= nl2br($wrap->getMigrate()); ?>

            <p><a style="font-size: 18px" href="?act=seed">Заполнить БД</a></p>

        <?php elseif(Request::input('act') === 'seed'): ?>

            <h1>Шаг 4 - заполнение БД (установка)</h1>

            <?= nl2br($wrap->getSeed()); ?>

            <p><a style="font-size: 18px" href="?act=account">Создать администратора</a></p>
        <?php elseif(Request::input('act') === 'account'): ?>

            <h1>Шаг 5 - создание администратора (установка)</h1>

            Прежде чем перейти к администрированию вашего сайта, необходимо создать аккаунт администратора.<br>
            Перед тем как нажимать кнопку Создать, убедитесь, что на предыдущей странице нет уведомлений об ошибках, иначе процесс не сможет быть завершен удачно.<br>
            После окончания инсталляции необходимо удалить директории <b>install</b> и <b>upgrade</b> со всем содержимым навсегда, пароль и остальные данные вы сможете поменять в своем профиле<br><br>

            <?php
                $login     = check(Request::input('login'));
                $password  = check(Request::input('password'));
                $password2 = check(Request::input('password2'));
                $email     = strtolower(check(Request::input('email')));
            ?>

            <?php if (Request::isMethod('post')): ?>

                <?php
                if (strlen($login) <= 20 && strlen($login) >= 3) {
                if (preg_match('|^[a-z0-9\-]+$|i', $login)) {
                if ($password == $password2) {
                if (preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $email)) {

                // Проверка логина на существование
                $checkLogin = User::query()->where('login', $login)->count();
                if (! $checkLogin) {

                // Проверка email на существование
                $checkMail = User::query()->where('email', $email)->count();
                if (! $checkMail) {

                    $user = User::query()->create([
                        'login'      => $login,
                        'password'   => password_hash($password, PASSWORD_BCRYPT),
                        'email'      => $email,
                        'level'      => 'boss',
                        'gender'     => 'male',
                        'themes'     => 0,
                        'point'      => 500,
                        'money'      => 100000,
                        'info'       => 'Администратор сайта',
                        'status'     => 'Босс',
                        'created_at' => SITETIME,
                    ]);

                    // -------------- Приват ---------------//
                    $text = 'Привет, ' . $login . '! Поздравляем с успешной установкой нашего движка Rotor.'.PHP_EOL.'Новые версии, апгрейды, а также множество других дополнений вы найдете на нашем сайте [url=http://visavi.net]VISAVI.NET[/url]';

                    $user->sendMessage(null, $text);

                    // -------------- Новость ---------------//
                    $textnews = 'Добро пожаловать на демонстрационную страницу движка Rotor'.PHP_EOL.'Rotor - функционально законченная система управления контентом с открытым кодом написанная на PHP. Она использует базу данных MySQL для хранения содержимого вашего сайта. Rotor является гибкой, мощной и интуитивно понятной системой с минимальными требованиями к хостингу, высоким уровнем защиты и является превосходным выбором для построения сайта любой степени сложности'.PHP_EOL.'Главной особенностью Rotor является низкая нагрузка на системные ресурсы, даже при очень большой аудитории сайта нагрузка не сервер будет минимальной, и вы не будете испытывать каких-либо проблем с отображением информации.'.PHP_EOL.'Движок Rotor вы можете скачать на официальном сайте [url=http://visavi.net]VISAVI.NET[/url]';

                    $news = News::query()->create([
                        'title'      => 'Добро пожаловать!',
                        'text'       => $textnews,
                        'user_id'    => $user->id,
                        'created_at' => SITETIME,
                    ]);

                    redirect('?act=finish');

                } else {echo '<p style="color: #ff0000">Ошибка! Указанный вами адрес email уже используется в системе!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Пользователь с данным логином уже зарегистрирован!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Неправильный адрес email, необходим формат name@site.domen</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Веденные пароли отличаются друг от друга</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Недопустимые символы в логине. Разрешены только знаки латинского алфавита и цифры!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Слишком длинный или короткий логин (От 3 до 20 символов)</p>';}

                ?>
            <?php endif; ?>

            <div class="form">
               <form method="post">
                    Логин (max20):<br>
                    <input class="form-control" name="login" maxlength="20" value="<?= $login ?>"><br>
                    Пароль(max20):<br>
                    <input class="form-control" name="password" type="password" maxlength="50"><br>
                    Повторите пароль:<br>
                    <input class="form-control" name="password2" type="password" maxlength="50"><br>
                    Адрес email:<br>
                    <input class="form-control" name="email" maxlength="100" value="<?= $email ?>"><br>
                   <button class="btn btn-primary">Создать</button>
                </form>
            </div><br>

            Внимание, в поле логин разрешены только знаки латинского алфавита, цифры и знак дефис<br>
            Все поля обязательны для заполнения<br>
            Email будет нужен для восстановления пароля, пишите только свои данные<br>
            Не нажимайте кнопку дважды, подождите до тех пор, пока процесс не завершится<br>
            В поле ввода адреса сайта необходимо ввести адрес в который у вас распакован движок, если это поддомен или папка, то необходимо указать ее, к примеру http://wap.visavi.net<br><br>


        <?php elseif (Request::input('act') === 'finish'): ?>

            <h1>Установка завершена</h1>

            <p>
                Поздравляем Вас, Rotor был успешно установлен на Ваш сервер. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br><br>
                Аккаунт администратора создан<br><br>
                <a href="/">Перейти на главную страницу сайта</a><br>
            </p>
            <p style="font-size: 20px">Удалите директории install и upgrade</p>
        <?php endif; ?>

    <?php else: ?>

        <?php if (Request::input('act') === 'status'): ?>

            <h1>Шаг 2 - проверка статуса (обновление)</h1>
            <?= nl2br($wrap->getStatus()); ?>
            <a style="font-size: 18px" href="?act=migrate">Перейти к обновлению</a>

        <?php elseif(Request::input('act') === 'rollback'): ?>
            <?= nl2br($wrap->getRollback()); ?>

        <?php elseif (Request::input('act') === 'migrate'): ?>

            <h1>Обновление завершено</h1>

            <?= nl2br($wrap->getMigrate()); ?>

            <p>
                Поздравляем Вас, Rotor был успешно обновлен. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br><br>
                <a href="/">Перейти на главную страницу сайта</a><br>
            </p>

            <p style="font-size: 20px">Удалите директории install и upgrade</p>
        <?php endif; ?>
    <?php endif; ?>
 </div>
</body>
</html>
