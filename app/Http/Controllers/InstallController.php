<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\UserRules;
use App\Models\Setting;
use App\Models\User;
use App\Services\MigrationService;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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

        // App, а не Lang: второй переключает только переводчик, и <html lang>
        // с языком, поставленным middleware по Accept-Language, расходился с текстами
        App::setLocale($lang);

        view()->share('lang', $lang);
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        // Сброс всех кэшей на входе в установку: после сброса таблиц stale-кэш
        // (settings, modules и пр.) уводит движок в неверное состояние —
        // напр. держит модуль «активным», а его таблицы ещё не мигрированы.
        // config:clear убирает кэш конфига, чтобы config() отражал
        // свежеотредактированный .env, а не старые креды БД из кэша
        Artisan::call('config:clear');
        clearCache();

        // Читаем через config(), а не env(): env() возвращает null при
        // закэшированном конфиге, config() отдаёт значения в любом случае
        $driver = (string) config('database.default');
        $keys = [
            'APP_ENV'       => config('app.env'),
            'APP_DEBUG'     => config('app.debug'),
            'DB_CONNECTION' => $driver,
            'DB_HOST'       => config("database.connections.$driver.host"),
            'DB_PORT'       => config("database.connections.$driver.port"),
            'DB_DATABASE'   => config("database.connections.$driver.database"),
            'DB_USERNAME'   => config("database.connections.$driver.username"),
            'APP_URL'       => config('app.url'),
            'APP_EMAIL'     => config('app.email'),
            'APP_ADMIN'     => config('app.admin'),
        ];

        $versions = [
            'php'   => '8.3.0',
            'mysql' => '5.7.8',
            'maria' => '10.2.7',
            'pgsql' => '9.2',
        ];

        $storage = glob(storage_path('{*,*/*,*/*/*}'), GLOB_BRACE | GLOB_ONLYDIR) ?: [];
        $uploads = glob(public_path('uploads/*'), GLOB_ONLYDIR) ?: [];
        $dirs = [public_path('uploads'), public_path('assets/modules'), base_path('bootstrap/cache'), base_path('modules')];

        $dirs = array_merge($storage, $uploads, $dirs);
        $languages = Locale::available();

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

            refreshCaches();

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

        refreshCaches();

        return view('install/seed', compact('output'));
    }

    /**
     * Создание администратора
     */
    public function account(Request $request): View|RedirectResponse
    {
        if (setting('app_installed')) {
            abort(403);
        }

        $lang = $request->input('lang', 'ru');
        $login = $request->string('login')->value();
        $password = $request->string('password')->value();
        $email = $request->string('email')->lower()->value();

        if ($request->isMethod('post')) {
            // Не FormRequest: установщик удаляется после установки, класс запроса остался бы сиротой.
            // Правила — те же, что у регистрации
            $validator = Validator::make($request->all(), [
                'login'     => UserRules::login(),
                'password'  => UserRules::password(),
                'password2' => ['required', 'same:password'],
                'email'     => UserRules::email(true),
            ], [
                ...UserRules::messages(),
                ...UserRules::passwordMessages('password'),
                'password2' => __('users.passwords_different'),
            ]);

            if ($validator->passes()) {
                $user = User::query()->create([
                    'login'     => $login,
                    'password'  => Hash::make($password),
                    'email'     => $email,
                    'level'     => User::BOSS,
                    'gender'    => User::MALE,
                    'themes'    => 'default',
                    'point'     => 500,
                    'money'     => 100000,
                    'language'  => $lang,
                    'subscribe' => Str::random(32),
                ]);

                // ------------- Авторизация -----------//
                Auth::login($user, true);

                // -------------- Приват ---------------//
                $text = __('install.text_message', ['login' => $login]);
                $user->sendMessage(null, $text);

                return redirect('/install/finish');
            }

            return redirect('/install/account')
                ->withInput()
                ->withErrors($validator);
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
        foreach (glob(database_path('upgrades/*.php')) ?: [] as $file) {
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
        // Читаем из БД напрямую, минуя кэш настроек: после сброса таблиц кэш
        // может держать app_installed=1 и уводить установщик в режим обновления
        if (! Schema::hasTable('settings')) {
            return false;
        }

        return (bool) Setting::query()
            ->where('name', 'app_installed')
            ->value('value');
    }
}
