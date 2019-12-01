<?php

use App\Commands\AppConfigure;
use App\Commands\AppState;
use App\Commands\CacheClear;
use App\Commands\ConfigClear;
use App\Commands\KeyGenerate;
use App\Commands\RouteClear;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Lang;

ob_start();
define('DIR', dirname(__DIR__, 2));
include_once DIR . '/app/bootstrap.php';

$request = request();

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('Rotor by Vantuz - http://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', APP . '/migration.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

$lang = check($request->input('lang', 'ru'));
Lang::setLocale($lang);

$languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

$keys = [
    'APP_ENV',
    'APP_NEW',
    'APP_DEBUG',
    'DB_DRIVER',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_ENGINE',
    'DB_CHARSET',
    'DB_COLLATION',
    'SITE_ADMIN',
    'SITE_EMAIL',
    'SITE_URL',
];

$errors = [];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        <?= config('APP_NEW') ? __('install.install') : __('install.upgrade') ?> Rotor
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="/themes/default/css/style.css">
</head>
<body>

<div class="cs" id="up">
    <a href="/"><img src="/assets/img/images/logo.png" alt=""></a>
</div>
<div class="site">
    <?php if (! $request->has('act')): ?>
        <form method="get">
            <label for="language">Выберите язык - Select language:</label>
            <div class="form-inline mb-3">
                <select class="form-control" name="lang" id="language">
                    <?php foreach ($languages as $language): ?>
                    <?php $selected = ($language === $lang) ? ' selected' : ''; ?>
                    <option value="<?= $language ?>"<?= $selected ?>><?= $language ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary"><?= __('main.select') ?></button>
            </div>
        </form>

        <h1>Шаг 1 - проверка требований</h1>

        <p style="color:#ff0000">
            Если в процессе установки движка произойдет какая-либо ошибка, чтобы узнать причину ошибки включите вывод ошибок, измените значение APP_DEBUG на true
        </p>

        <p style="color:#ff0000">
            Если вы обновляетесь с предыдущей версии Rotor, вам необходимо провести обновление базы данных, для этого значение APP_NEW должно быть false, после этого обновите текущую страницу</p>

        <p>Для установки движка, вам необходимо прописать данные от БД в файл .env</p>

        <?php foreach ($keys as $key): ?>
            <b><?= $key ?></b> - <?= trim(var_export(config($key), true), "'") ?><br>
        <?php endforeach; ?>
        <br>
        <p>Не забудьте изменить значение APP_KEY, эти данные необходимы для шифрования cookies и паролей в сессиях</p>

        <p>Минимальная версия PHP необходимая для работы движка PHP 7.2.0 и MySQL 5.5.3</p>

        <p style="font-size: 15px; font-weight: bold">Проверка требований</p>

        <?php $errors['critical']['php'] = PHP_VERSION_ID >= 70200 ?>
        <i class="fas fa-check-circle <?= $errors['critical']['php'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        Версия PHP 7.2.0: <b><?= parseVersion(PHP_VERSION) ?></b><br>

        <?php $errors['critical']['pdo_mysql'] = extension_loaded('pdo_mysql') ?>
        <i class="fas fa-check-circle <?= $errors['critical']['pdo_mysql'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = strtok(getModuleSetting('pdo_mysql', ['Client API version', 'PDO Driver for MySQL, client library version']), '-'); ?>
        Расширение PDO-MySQL: <b><?= $version ?></b><br>

        <?php $errors['simple']['bcmath'] = extension_loaded('bcmath') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['bcmath'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        Расширение BCMath<br>

        <?php $errors['simple']['ctype'] = extension_loaded('ctype') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['ctype'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        Расширение Ctype<br>

        <?php $errors['simple']['json'] = extension_loaded('json') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['json'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        Расширение Json<br>

        <?php $errors['simple']['tokenizer'] = extension_loaded('tokenizer') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['tokenizer'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        Расширение Tokenizer<br>

        <?php $errors['simple']['mbstring'] = extension_loaded('mbstring') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['mbstring'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = getModuleSetting('mbstring', ['oniguruma version', 'Multibyte regex (oniguruma) version']); ?>
        Расширение MbString: <b><?= $version ?></b><br>

        <?php $errors['simple']['openssl'] = extension_loaded('openssl') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['openssl'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = getModuleSetting('openssl', ['OpenSSL Library Version', 'OpenSSL Header Version']); ?>
        Расширение OpenSSL: <b><?= $version ?></b><br>

        <?php $errors['simple']['xml'] = extension_loaded('xml') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['xml'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = getModuleSetting('xml', ['libxml2 Version']); ?>
        Расширение XML: <b><?= $version ?></b><br>

        <?php $errors['simple']['gd'] = extension_loaded('gd') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['gd'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = getModuleSetting('gd', ['GD headers Version', 'GD library Version']); ?>
        Библиотека GD: <b><?= $version ?></b><br>

        <?php $errors['simple']['curl'] = extension_loaded('curl') ?>
        <i class="fas fa-check-circle <?= $errors['simple']['curl'] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
        <?php $version = getModuleSetting('curl', ['Curl Information', 'cURL Information']); ?>
        Библиотека Curl: <b><?= $version ?></b><br>

        Для обработки видео желательно установить библиотеку FFmpeg<br><br>

        <p style="font-size: 15px; font-weight: bold">Права доступа</p>

        <?php
        runCommand(new AppConfigure());

        $storage = glob(STORAGE . '/*', GLOB_ONLYDIR);
        $uploads = glob(UPLOADS . '/*', GLOB_ONLYDIR);
        $modules = [HOME . '/assets/modules'];

        $dirs = array_merge($storage, $uploads, $modules);
        ?>

        <?php foreach ($dirs as $dir): ?>
            <?php $chmod = decoct(fileperms($dir)) % 1000; ?>
            <?php $errors['chmod'][$dir] = is_writable($dir); ?>

            <i class="fas fa-check-circle <?= $errors['chmod'][$dir] ? 'text-success' : 'fa-times-circle text-danger' ?>"></i>
            <?= str_replace(DIR, '', $dir) ?>: <b><?= $chmod ?></b><br>
        <?php endforeach; ?>

        <br>Дополнительно можете выставить права на директории и файлы с шаблонами внутри resources/views - это необходимо для редактирования шаблонов сайта<br><br>

        Если какой-то пункт выделен красным, необходимо зайти по FTP и выставить CHMOD разрешающую запись<br>
        Некоторые настройки являются рекомендуемыми для полной совместимости, однако скрипт способен работать даже если рекомендуемые настройки не совпадают с текущими.<br><br>

        <?php if (! in_array(false, $errors['critical'], true) && ! in_array(false, $errors['chmod'], true)): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> Вы можете продолжить установку движка!
            </div>

            <?php if (! in_array(false, $errors['simple'], true)): ?>
                Все модули и библиотеки присутствуют, настройки корректны, необходимые файлы и папки доступны для записи<br><br>
            <?php else: ?>
                <div class="alert alert-warning">У вас имеются предупреждения!<br>
                    Данные предупреждения не являются критическими, но тем не менее для полноценной, стабильной и безопасной работы движка желательно их устранить<br>
                    Вы можете продолжить установку скрипта, но нет никаких гарантий, что движок будет работать стабильно
                </div>
            <?php endif; ?>

            <a style="font-size: 18px" href="?act=status&amp;lang=<?= $lang ?>">Проверить статус</a>
            (Выполняется <?= config('APP_NEW') ? __('install.install') : __('install.upgrade') ?>)
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="fa fa-times-circle"></i> Имеются критические ошибки!<br>
                Вы не сможете приступить к установке, пока не устраните критические ошибки
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (config('APP_NEW')): ?>
        <?php if ($request->input('act') === 'status'): ?>
            <h1>Шаг 2 - проверка статуса (установка)</h1>

            <?= nl2br($wrap->getStatus()) ?>

            <p><a style="font-size: 18px" href="?act=migrate&amp;lang=<?= $lang ?>">Выполнить миграции</a></p>

        <?php elseif ($request->input('act') === 'migrate'): ?>
            <h1>Шаг 3 - выполнение миграций (установка)</h1>

            <?= nl2br($wrap->getMigrate()) ?>

            <p><a style="font-size: 18px" href="?act=seed&amp;lang=<?= $lang ?>">Заполнить БД</a></p>

        <?php elseif ($request->input('act') === 'seed'): ?>

            <h1>Шаг 4 - заполнение БД (установка)</h1>

            <?= nl2br($wrap->getSeed()) ?>

            <p><a style="font-size: 18px" href="?act=account&amp;lang=<?= $lang ?>">Создать администратора</a></p>
        <?php elseif ($request->input('act') === 'account'): ?>

            <h1>Шаг 5 - создание администратора (установка)</h1>

            Прежде чем перейти к администрированию вашего сайта, необходимо создать аккаунт администратора.<br>
            Перед тем как нажимать кнопку Создать, убедитесь, что на предыдущей странице нет уведомлений об ошибках, иначе процесс не сможет быть завершен удачно.<br>
            После окончания инсталляции необходимо удалить директорию <b>install</b> со всем содержимым, пароль и остальные данные вы сможете поменять в своем профиле<br><br>

            <?php
                $login     = check($request->input('login'));
                $password  = check($request->input('password'));
                $password2 = check($request->input('password2'));
                $email     = strtolower(check($request->input('email')));
            ?>

            <?php if ($request->isMethod('post')): ?>

                <?php
                $length = strlen($login);
                if ($length <= 20 && $length >= 3) {
                if (preg_match('|^[a-z0-9\-]+$|i', $login)) {
                if ($password === $password2) {
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

                    redirect('?act=finish&lang=' . $lang);

                } else {echo '<p style="color: #ff0000">Ошибка! Указанный вами адрес email уже используется в системе!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Пользователь с данным логином уже зарегистрирован!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Неправильный адрес email, необходим формат name@site.domen</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Веденные пароли отличаются друг от друга</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Недопустимые символы в логине. Разрешены только знаки латинского алфавита и цифры!</p>';}
                } else {echo '<p style="color: #ff0000">Ошибка! Слишком длинный или короткий логин (От 3 до 20 символов)</p>';}

                ?>
            <?php endif; ?>

            <div class="form">
               <form method="post" action="?act=account&amp;lang=<?= $lang ?>">
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


        <?php elseif ($request->input('act') === 'finish'): ?>

            <h1>Установка завершена</h1>

            <p>
                Поздравляем Вас, Rotor был успешно установлен на Ваш сервер. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br><br>
                Аккаунт администратора создан<br><br>
                <a href="/">Перейти на главную страницу сайта</a><br>
            </p>
            <p style="font-size: 20px">Удалите директорию install</p>

            <?php
            runCommand(new AppState());
            runCommand(new KeyGenerate());
            runCommand(new CacheClear());
            runCommand(new RouteClear());
            runCommand(new ConfigClear());
            ?>
        <?php endif; ?>

    <?php else: ?>

        <?php if ($request->input('act') === 'status'): ?>

            <h1>Шаг 2 - проверка статуса (обновление)</h1>
            <?= nl2br($wrap->getStatus()) ?>
            <a style="font-size: 18px" href="?act=migrate&amp;lang=<?= $lang ?>">Перейти к обновлению</a>

        <?php elseif ($request->input('act') === 'rollback'): ?>
            <?= nl2br($wrap->getRollback()) ?>

        <?php elseif ($request->input('act') === 'migrate'): ?>

            <h1>Обновление завершено</h1>

            <?= nl2br($wrap->getMigrate()) ?>

            <p>
                Поздравляем Вас, Rotor был успешно обновлен. Вы можете перейти на главную страницу вашего сайта и посмотреть возможности скрипта<br><br>
                <a href="/">Перейти на главную страницу сайта</a><br>
            </p>

            <p style="font-size: 20px">Удалите директорию install</p>
            <?php
            runCommand(new CacheClear());
            runCommand(new RouteClear());
            runCommand(new ConfigClear());
            ?>
        <?php endif; ?>
    <?php endif; ?>
 </div>
</body>
</html>
<?php

/**
 * Parse PHP modules
 *
 * @return array
 */
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
    $iMax = count($vTmp);

    for ($i = 1; $i < $iMax; $i++) {
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

/**
 * Get PHP module setting
 *
 * @param string $pModuleName
 * @param array  $pSettings
 *
 * @return string
 */
function getModuleSetting(string $pModuleName, array $pSettings)
{
    $vModules = parsePHPModules();

    foreach ($pSettings as $pSetting) {
        if (isset($vModules[$pModuleName][$pSetting])) {
            return $vModules[$pModuleName][$pSetting];
        }
    }

    return 'Не определено';
}
