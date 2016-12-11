<?php
include_once __DIR__.'/../../app/bootstrap.php';
include_once __DIR__.'/../../app/helpers.php';

function parsePHPModules() {
    ob_start();
    phpinfo(INFO_MODULES);
    $s = ob_get_contents();
    ob_end_clean();
    $s = strip_tags($s, '<h2><th><td>');
    $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s);
    $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s);
    $vTmp = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    $vModules = array();
    for ($i = 1;$i < count($vTmp);$i++) {
        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $vTmp[$i], $vMat)) {
            $vName = trim($vMat[1]);
            $vTmp2 = explode("\n", $vTmp[$i + 1]);
            foreach ($vTmp2 AS $vOne) {
                $vPat = '<info>([^<]+)<\/info>';
                $vPat3 = "/$vPat\s*$vPat\s*$vPat/";
                $vPat2 = "/$vPat\s*$vPat/";
                if (preg_match($vPat3, $vOne, $vMat)) {
                    $vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]), trim($vMat[3]));
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

$wrap->setOption('configuration', __DIR__.'/../../phinx.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

header("Content-type:text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
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

        <p>Минимальная версия PHP необходимая для работы движка PHP 5.6.4 и MySQL 5.6</p>

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

        echo '<br /><p style="font-size: 15px; font-weight: bold">Права доступа</p>';

        $storage = glob(dirname(dirname(__DIR__)).'/app/storage/*', GLOB_ONLYDIR);
        $uploads = glob(dirname(__DIR__).'/uploads/*', GLOB_ONLYDIR);

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

            echo '<i class="fa fa-check-circle"></i> '.basename(dirname($dir)).'/'.basename($dir) . ' <b> - ' . $file_status . '</b> (chmod ' . $chmod_value . ')<br />';
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

        <p style="font-size: 20px">Установка завершена</p>
        <p style="font-size: 20px">Удалите директории install и upgrade</p>
    <?php else: ?>

    <?php endif; ?>

</div>
</body>
</html>
