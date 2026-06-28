<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Setting;
use App\Models\User;
use App\Services\MigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstallController extends Controller
{
    /**
     * Конструктор
     */
    public function __construct(
        Request $request,
        private readonly MigrationService $migrations
    ) {
        $lang = $request->input('lang', 'ru');

        Lang::setLocale($lang);

        view()->share('lang', $lang);
    }

    /**
     * Главная страница
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
            'php'   => '8.3.0',
            'mysql' => '5.7.8',
            'maria' => '10.2.7',
            'pgsql' => '9.2',
        ];

        $storage = glob(storage_path('{*,*/*,*/*/*}'), GLOB_BRACE | GLOB_ONLYDIR);
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR);
        $dirs = [public_path('assets/modules'), base_path('bootstrap/cache'), base_path('modules')];

        $dirs = array_merge($storage, $uploads, $dirs);
        $languages = getAvailableLanguages();

        $isUpdate = $this->isUpdate();
        $database = $this->databaseChecks($versions);

        return view('install/index', compact('keys', 'languages', 'versions', 'dirs', 'isUpdate', 'database'));
    }

    /**
     * Проверка подключения к БД и её версии (driver-aware, с защитой от падения)
     */
    private function databaseChecks(array $versions): array
    {
        $driver = (string) config('database.default');
        $pdoExt = $driver === 'pgsql' ? 'pdo_pgsql' : 'pdo_mysql';

        $database = [
            'driver'     => $driver,
            'pdoExt'     => $pdoExt,
            'pdoLoaded'  => extension_loaded($pdoExt),
            'connected'  => false,
            'error'      => null,
            'client'     => '',
            'mysqlnd'    => true,
            'version'    => '',
            'versionOk'  => false,
            'minVersion' => match ($driver) {
                'mariadb' => $versions['maria'],
                'pgsql'   => $versions['pgsql'],
                default   => $versions['mysql'],
            },
        ];

        try {
            $pdo = DB::connection()->getPdo();
            $database['connected'] = true;

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $database['client'] = (string) ($pdo->getAttribute(\PDO::ATTR_CLIENT_VERSION) ?: '');
                $database['mysqlnd'] = stripos($database['client'], 'mysqlnd') !== false;
            }

            $raw = DB::selectOne('SELECT VERSION() as version')->version ?? '';
            preg_match('/(\d+\.\d+(?:\.\d+)?)/', (string) $raw, $match);

            $database['version'] = (string) $raw;
            $database['versionOk'] = isset($match[1]) && version_compare($match[1], $database['minVersion'], '>=');
        } catch (\Throwable $e) {
            $database['error'] = $e->getMessage();
        }

        return $database;
    }

    /**
     * Проверка статуса и выполнение миграций
     */
    public function status(): View
    {
        if (! Schema::hasTable('migrations')) {
            Artisan::call('migrate:install');
        }

        $isUpdate = $this->isUpdate();
        $pendingMigrations = $this->migrations->getPendingMigrations($this->paths());

        return view('install/status', compact('isUpdate', 'pendingMigrations'));
    }

    /**
     * Выполняет одну следующую миграцию
     */
    public function migrateNext(): JsonResponse
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        $pending = $this->migrations->getPendingMigrations($this->paths());

        if (empty($pending)) {
            if (! $this->isUpdate()) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');

            return response()->json(['done' => true, 'migration' => null, 'output' => '']);
        }

        $name = $pending[0];
        $file = $this->migrations->findFile($name);

        if (! $file) {
            return response()->json(['error' => "Файл миграции не найден: {$name}"], 500);
        }

        $remaining = count($pending) - 1;

        try {
            $output = $this->migrations->runOne($file);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => "Ошибка миграции {$name}: " . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'done'      => $remaining === 0,
            'migration' => $name,
            'output'    => $output,
            'remaining' => $remaining,
        ]);
    }

    private function paths(): array
    {
        $paths = [database_path('migrations')];

        if ($this->isUpdate()) {
            $paths[] = database_path('upgrades');
        }

        return $paths;
    }

    /**
     * Заполнение БД
     */
    public function seed(): View
    {
        if (setting('app_installed')) {
            abort(403);
        }

        Artisan::call('db:seed', ['--force' => true]);
        $output = Artisan::output();

        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        return view('install/seed', compact('output'));
    }

    /**
     * Создание администратора
     */
    public function account(Request $request, Validator $validator): View|RedirectResponse
    {
        if (setting('app_installed')) {
            abort(403);
        }

        $lang = $request->input('lang', 'ru');
        $login = (string) $request->input('login');
        $password = $request->input('password');
        $password2 = $request->input('password2');
        $email = strtolower((string) $request->input('email'));

        if ($request->isMethod('post')) {
            $validator->regex($login, '|^[a-z0-9\-]+$|i', ['login' => __('validator.login')])
                ->regex(Str::substr($login, 0, 1), '|^[a-z0-9]+$|i', ['login' => __('users.login_begin_requirements')])
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
                $user = User::query()->create([
                    'login'    => $login,
                    'password' => Hash::make($password),
                    'email'    => $email,
                    'level'    => User::BOSS,
                    'gender'   => User::MALE,
                    'themes'   => 'default',
                    'point'    => 500,
                    'money'    => 100000,
                    'status'   => 'Boss',
                    'language' => $lang,
                ]);

                // ------------- Авторизация -----------//
                Auth::login($user, true);

                // -------------- Приват ---------------//
                $text = __('install.text_message', ['login' => $login]);
                $user->sendMessage(null, $text);

                return redirect('/install/finish');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('install/account', compact('login', 'email'));
    }

    /**
     * Завершение установки
     */
    public function finish(): View
    {
        if ($this->isUpdate()) {
            abort(403);
        }

        // Помечаем все апгрейды как выполненные — свежая схема уже содержит все изменения
        $batch = DB::table('migrations')->max('batch') + 1;
        foreach (glob(database_path('upgrades/*.php')) as $file) {
            DB::table('migrations')->insertOrIgnore([
                'migration' => pathinfo($file, PATHINFO_FILENAME),
                'batch'     => $batch,
            ]);
        }

        Setting::query()
            ->where('name', 'app_installed')
            ->update(['value' => 1]);

        clearCache('settings');

        return view('install/finish');
    }

    private function isUpdate(): bool
    {
        return (bool) setting('app_installed');
    }
}
