<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(Request $request)
    {
        $lang = $request->input('lang', 'ru');

        Lang::setLocale($lang);

        view()->share('lang', $lang);
    }

    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $keys = [
            'APP_ENV',
            'APP_DEBUG',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'APP_URL',
            'APP_EMAIL',
            'APP_ADMIN',
        ];

        $versions = [
            'php'   => '7.3.0',
            'mysql' => '5.7.8',
            'maria' => '10.2.7',
            'pgsql' => '9.2',
        ];

        $storage = glob(storage_path('{*,*/*,*/*/*}'), GLOB_BRACE | GLOB_ONLYDIR);
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR);
        $dirs    = [public_path('assets/modules'), base_path('bootstrap/cache')];

        $dirs = array_merge($storage, $uploads, $dirs);
        $languages = array_map('basename', glob(resource_path('lang/*'), GLOB_ONLYDIR));

        return view('install/index', compact('keys', 'languages', 'versions', 'dirs'));
    }

    /**
     * Проверка статуса
     *
     * @return View
     */
    public function status(): View
    {
        if (! Schema::hasTable('migrations')) {
            Artisan::call('migrate:install');
        }

        Artisan::call('migrate:status');
        $output = Artisan::output();

        return view('install/status', compact('output'));
    }

    /**
     * Выполнение миграций
     *
     * @return View
     */
    public function migrate(): View
    {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return view('install/migrate', compact('output'));
    }

    /**
     * Заполнение БД
     *
     * @return View
     */
    public function seed(): View
    {
        Artisan::call('db:seed', ['--force' => true]);
        $output = Artisan::output();

        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        return view('install/seed', compact('output'));
    }

    /**
     * Создание администратора
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function account(Request $request, Validator $validator)
    {
        $lang      = $request->input('lang', 'ru');
        $login     = $request->input('login');
        $password  = $request->input('password');
        $password2 = $request->input('password2');
        $email     = $request->input('email');

        if ($request->isMethod('post')) {
            $validator->regex($login, '|^[a-z0-9\-]+$|i', ['login' => __('validator.login')])
                ->regex(utfSubstr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => __('users.login_begin_requirements')])
                ->email($email, ['email' => __('validator.email')])
                ->length($login, 3, 20, ['login' => __('users.login_length_requirements')])
                ->length($password, 6, 20, ['password' => __('users.password_length_requirements')])
                ->equal($password, $password2, ['password2' => __('users.passwords_different')])
                ->false(ctype_digit($login), ['login' => __('users.field_characters_requirements')])
                ->false(ctype_digit($password), ['password' => __('users.field_characters_requirements')])
                ->false(substr_count($login, '-') > 2, ['login' => __('users.login_hyphens_requirements')]);

            if ($validator->isValid()) {
                // Проверка логина на существование
                $checkLogin = User::query()->where('login', $login)->exists();
                $validator->false($checkLogin, ['login' => __('users.login_already_exists')]);

                // Проверка email на существование
                $checkMail = User::query()->where('email', $email)->exists();
                $validator->false($checkMail, ['email' => __('users.email_already_exists')]);
            }

            if ($validator->isValid()) {
                /** @var User $user */
                $user = User::query()->create([
                    'login'      => $login,
                    'password'   => password_hash($password, PASSWORD_BCRYPT),
                    'email'      => strtolower($email),
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

                News::query()->create([
                    'title'      => __('install.welcome'),
                    'text'       => $textnews,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                ]);

                // -------------- Установка -------------//
                Setting::query()
                    ->where('name', 'app_installed')
                    ->update([
                        'value' => 1,
                    ]);

                clearCache(['statNews', 'lastNews', 'statNewsDate', 'settings']);

                return redirect('/install/finish');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('install/account', compact('login', 'email'));
    }

    /**
     * Завершение установки
     *
     * @return View
     */
    public function finish(): View
    {
        return view('install/finish');
    }

    /**
     * Parse PHP modules
     *
     * @return array
     */
    private static function parsePhpModules(): array
    {
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
                foreach ($vTmp2 as $vOne) {
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
    public static function getModuleSetting(string $pModuleName, array $pSettings): string
    {
        $vModules = self::parsePhpModules();

        foreach ($pSettings as $pSetting) {
            if (isset($vModules[$pModuleName][$pSetting])) {
                return $vModules[$pModuleName][$pSetting];
            }
        }

        return __('main.undefined');
    }
}
