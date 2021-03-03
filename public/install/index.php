<?php

use App\Commands\AppPermission;
use App\Commands\CacheClear;
use App\Commands\ConfigClear;
use App\Commands\KeyGenerate;
use App\Commands\RouteClear;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Lang;

ob_start();
define('DIR', dirname(__DIR__, 2));

try {
    include_once DIR . '/app/bootstrap.php';
} catch (Exception $e) {
    exit($e->getMessage());
}

$request      = request();
$phpVersion   = '7.3.0';
$mysqlVersion = '5.7.8';
$postgresVersion = '9.2';

$app  = new Phinx\Console\PhinxApplication();
$wrap = new Phinx\Wrapper\TextWrapper($app);

$app->setName('Rotor by Vantuz - https://visavi.net');
$app->setVersion(VERSION);

$wrap->setOption('configuration', APP . '/migration.php');
$wrap->setOption('parser', 'php');
$wrap->setOption('environment', 'default');

$lang = check($request->input('lang', 'ru'));

Lang::setLocale($lang);

$errors    = [];
$languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

$keys = [
    'APP_ENV',
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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>
        <?= setting('app_installed') ? __('install.update') : __('install.install') ?> Rotor
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="image_src" href="/assets/img/images/icon.png">
    <link rel="stylesheet" type="text/css" href="/themes/default/dist/app.css">
</head>

<body class="bg-light">
<div class="container border bg-white px-5">
    <div class="py-5 text-center">
        <a href="/"><img class="d-block mx-auto mb-3" src="/assets/img/images/logo_big.png" alt=""></a>
        <h2>Mobile CMS</h2>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
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

        <h1><?= __('install.step1') ?></h1>

        <div class="alert alert-info">
            <?= __('install.debug') ?>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><?= __('install.env') ?></h5>
                        <p class="card-text">
                            <?php foreach ($keys as $key): ?>
                                <?= $key ?> - <?= trim(var_export(config($key), true), "'") ?><br>
                            <?php endforeach; ?>
                        </p>
                        <span class="text-danger font-italic"><?= __('install.app_key') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3"><?= __('install.requirements', ['php' => $phpVersion, 'mysql' => $mysqlVersion, 'pgsql' => $postgresVersion]) ?></div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><?= __('install.check_requirements') ?></h5>
                        <p class="card-text">
                            <?php $errors['critical']['php'] = version_compare(PHP_VERSION, $phpVersion) >= 0 ?>
                            <span class="<?= $errors['critical']['php'] ? 'text-success' : 'text-danger' ?>">PHP: <?= parseVersion(PHP_VERSION) ?></span><br>

                            <?php $errors['critical']['pdo_mysql'] = extension_loaded('pdo_mysql') ?>
                            <?php $version = strtok(getModuleSetting('pdo_mysql', ['Client API version', 'PDO Driver for MySQL, client library version']), '-'); ?>
                            <span class="<?= $errors['critical']['pdo_mysql'] ? 'text-success' : 'text-danger' ?>">PDO-MySQL: <?= $version ?></span><br>

                            <?php $errors['simple']['bcmath'] = extension_loaded('bcmath') ?>
                            <span class="<?= $errors['simple']['bcmath'] ? 'text-success' : 'text-danger' ?>">BCMath</span><br>

                            <?php $errors['simple']['ctype'] = extension_loaded('ctype') ?>
                            <span class="<?= $errors['simple']['ctype'] ? 'text-success' : 'text-danger' ?>">Ctype</span><br>

                            <?php $errors['simple']['json'] = extension_loaded('json') ?>
                            <span class="<?= $errors['simple']['json'] ? 'text-success' : 'text-danger' ?>">Json</span><br>

                            <?php $errors['simple']['tokenizer'] = extension_loaded('tokenizer') ?>
                            <span class="<?= $errors['simple']['tokenizer'] ? 'text-success' : 'text-danger' ?>">Tokenizer</span><br>

                            <?php $errors['simple']['fileinfo'] = extension_loaded('fileinfo') ?>
                            <span class="<?= $errors['simple']['fileinfo'] ? 'text-success' : 'text-danger' ?>">Fileinfo</span><br>

                            <?php $errors['simple']['mbstring'] = extension_loaded('mbstring') ?>
                            <?php $version = getModuleSetting('mbstring', ['oniguruma version', 'Multibyte regex (oniguruma) version']); ?>
                            <span class="<?= $errors['simple']['mbstring'] ? 'text-success' : 'text-danger' ?>">MbString: <?= $version ?></span><br>

                            <?php $errors['simple']['openssl'] = extension_loaded('openssl') ?>
                            <?php $version = getModuleSetting('openssl', ['OpenSSL Library Version', 'OpenSSL Header Version']); ?>
                            <span class="<?= $errors['simple']['openssl'] ? 'text-success' : 'text-danger' ?>">OpenSSL: <?= $version ?></span><br>

                            <?php $errors['simple']['xml'] = extension_loaded('xml') ?>
                            <?php $version = getModuleSetting('xml', ['libxml2 Version']); ?>
                            <span class="<?= $errors['simple']['xml'] ? 'text-success' : 'text-danger' ?>">XML: <?= $version ?></span><br>

                            <?php $errors['simple']['gd'] = extension_loaded('gd') ?>
                            <?php $version = getModuleSetting('gd', ['GD headers Version', 'GD library Version']); ?>
                            <span class="<?= $errors['simple']['gd'] ? 'text-success' : 'text-danger' ?>">GD: <?= $version ?></span><br>

                            <?php $errors['simple']['curl'] = extension_loaded('curl') ?>
                            <?php $version = getModuleSetting('curl', ['Curl Information', 'cURL Information']); ?>
                            <span class="<?= $errors['simple']['curl'] ? 'text-success' : 'text-danger' ?>">Curl: <?= $version ?></span><br>
                            <span class="font-italic my-3">
                                <?= __('install.ffmpeg') ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><?= __('install.chmod_rights') ?></h5>
                        <p class="card-text">
                            <?php
                            runCommand(new AppPermission());

                            $storage = glob(STORAGE . '/*', GLOB_ONLYDIR);
                            $uploads = glob(UPLOADS . '/*', GLOB_ONLYDIR);
                            $modules = [HOME . '/assets/modules'];

                            $dirs = array_merge($storage, $uploads, $modules);
                            ?>

                            <?php foreach ($dirs as $dir): ?>
                                <?php $chmod = decoct(fileperms($dir)) % 1000; ?>
                                <?php $errors['chmod'][$dir] = is_writable($dir); ?>

                                <span class="<?= $errors['chmod'][$dir] ? 'text-success' : 'text-danger' ?>"><?= str_replace(DIR, '', $dir) ?>: <?= $chmod ?></span><br>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?= __('install.chmod_views') ?><br><br>

        <?= __('install.chmod') ?><br>
        <?= __('install.errors') ?><br><br>

        <?php if (! in_array(false, $errors['critical'], true) && ! in_array(false, $errors['chmod'], true)): ?>
            <div class="alert alert-success">
                <?= __('install.continue') ?>
            </div>

            <?php if (! in_array(false, $errors['simple'], true)): ?>
                <?= __('install.requirements_pass') ?><br><br>
            <?php else: ?>
                <div class="alert alert-warning"><?= __('install.requirements_warning') ?><br>
                    <?= __('install.requirements_not_pass') ?><br>
                    <?= __('install.continue_restrict') ?>
                </div>
            <?php endif; ?>

            <a class="btn btn-primary" style="font-size: 18px" href="?act=status&amp;lang=<?= $lang ?>"><?= __('install.check_status') ?></a>
            <span class="text-info font-weight-bold"><?= setting('app_installed') ? __('install.update') : __('install.install') ?></span>
        <?php else: ?>
            <div class="alert alert-danger">
                <?= __('install.requirements_failed') ?><br>
                <?= __('install.resolve_errors') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Обновление движка -->
    <?php if (setting('app_installed')): ?>
        <!-- Проверка статуса -->
        <?php if ($request->input('act') === 'status'): ?>
            <h1><?= __('install.step2_update') ?></h1>
            <?= nl2br($wrap->getStatus()) ?>
            <a class="btn btn-primary" href="?act=migrate&amp;lang=<?= $lang ?>"><?= __('install.migrations') ?></a>

        <!-- Откат миграций -->
        <?php elseif ($request->input('act') === 'rollback'): ?>
            <?= nl2br($wrap->getRollback()) ?>

        <!-- Применение миграций -->
        <?php elseif ($request->input('act') === 'migrate'): ?>
            <h1><?= __('install.update_completed') ?></h1>
            <?= nl2br($wrap->getMigrate()) ?>

            <div>
                <div class="alert alert-success">
                    <?= __('install.success_update') ?>
                </div>

                <a class="btn btn-primary" href="/"><?= __('install.main_page') ?></a><br>
            </div>
            <?php
            runCommand(new CacheClear());
            runCommand(new RouteClear());
            runCommand(new ConfigClear());
            ?>
        <?php endif; ?>

    <!-- Установка движка -->
    <?php else: ?>
        <!-- Проверка статуса -->
        <?php if ($request->input('act') === 'status'): ?>
            <h1><?= __('install.step2_install') ?></h1>

            <?= nl2br($wrap->getStatus()) ?>

            <div>
                <a class="btn btn-primary" href="?act=migrate&amp;lang=<?= $lang ?>"><?= __('install.migrations') ?></a>
            </div>

        <!-- Применение миграций -->
        <?php elseif ($request->input('act') === 'migrate'): ?>
            <h1><?= __('install.step3_install') ?></h1>

            <?= nl2br($wrap->getMigrate()) ?>

            <div>
                <a class="btn btn-primary" href="?act=seed&amp;lang=<?= $lang ?>"><?= __('install.seeds') ?></a>
            </div>

        <!-- Заполнение БД -->
        <?php elseif ($request->input('act') === 'seed'): ?>
            <h1><?= __('install.step4_install') ?>)</h1>

            <?= nl2br($wrap->getSeed()) ?>

            <div>
                <a class="btn btn-primary" href="?act=account&amp;lang=<?= $lang ?>"><?= __('install.create_admin') ?></a>
            </div>

            <?php
            runCommand(new KeyGenerate());
            runCommand(new CacheClear());
            runCommand(new RouteClear());
            runCommand(new ConfigClear());
            ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Создание админа -->
    <?php if ($request->input('act') === 'account'): ?>
        <h1><?= __('install.step5_install') ?></h1>

        <?= __('install.create_admin_info') ?><br>
        <?= __('install.create_admin_errors') ?><br>
        <?= __('install.delete_install') ?><br><br>

        <?php
        $login     = $request->input('login');
        $password  = $request->input('password');
        $password2 = $request->input('password2');
        $email     = strtolower($request->input('email'));
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

            /** @var User $user */
            $user = User::query()->create([
                'login'      => $login,
                'password'   => password_hash($password, PASSWORD_BCRYPT),
                'email'      => $email,
                'level'      => 'boss',
                'gender'     => 'male',
                'themes'     => 'default',
                'point'      => 500,
                'money'      => 100000,
                'status'     => 'Boss',
                'language'   => $lang,
                'created_at' => SITETIME,
            ]);

            // ------------- Авторизация -----------//
            User::auth($login, $password);

            // -------------- Приват ---------------//
            $text = __('install.text_message', ['login' => $login]);
            $user->sendMessage(null, $text);

            // -------------- Новость ---------------//
            $textnews = __('install.text_news');

            $news = News::query()->create([
                'title'      => __('install.welcome'),
                'text'       => $textnews,
                'user_id'    => $user->id,
                'created_at' => SITETIME,
            ]);

            redirect('?act=finish&lang=' . $lang);

            } else {echo '<div class="alert alert-danger">' . __('users.email_already_exists') . '</div>';}
            } else {echo '<div class="alert alert-danger">' . __('users.login_already_exists') . '</div>';}
            } else {echo '<div class="alert alert-danger">' . __('validator.email') . '</div>';}
            } else {echo '<div class="alert alert-danger">' . __('users.passwords_different') . '</div>';}
            } else {echo '<div class="alert alert-danger">' . __('validator.login') . '</div>';}
            } else {echo '<div class="alert alert-danger">' . __('users.login_requirements') . '</div>';}
            ?>
        <?php endif; ?>

        <div class="section-form mb-3 shadow">
            <form method="post" action="?act=account&amp;lang=<?= $lang ?>">
                <div class="form-group">
                    <label for="login"><?= __('users.login') ?> (max20):</label>
                    <input type="text" class="form-control" name="login" id="login" maxlength="20" value="<?= $login ?>">
                    <span class="text-muted font-italic"><?= __('users.login_requirements') ?></span>
                </div>
                <div class="form-group">
                    <label for="password"><?= __('users.password') ?> (max20):</label>
                    <input class="form-control" name="password" id="password" type="password" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="password2"><?= __('users.confirm_password') ?>:</label>
                    <input class="form-control" name="password2" id="password2" type="password" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="email"><?= __('users.email') ?>:</label>
                    <input class="form-control" name="email" id="email" maxlength="50" value="<?= $email ?>">
                </div>

                <button class="btn btn-primary"><?= __('main.create') ?></button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Завершение установки -->
    <?php if ($request->input('act') === 'finish'): ?>
        <h1><?= __('install.install_completed') ?></h1>
        <div>
            <div class="alert alert-success">
                <?= __('install.success_install') ?>
            </div>

            <a class="btn btn-primary" href="/"><?= __('install.main_page') ?></a><br>
        </div>
    <?php endif; ?>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; 2005-<?= date('Y') ?> VISAVI.NET</p>
    </footer>
</div>
</div>
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
    $s = ob_get_clean();
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

    return __('main.undefined');
}
