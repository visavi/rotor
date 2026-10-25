<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckToken;
use App\Http\Middleware\CheckUser;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionParameter;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Правила, которые проще проверить, чем помнить: нарушение валит тест с объяснением
 */
class ArchitectureTest extends TestCase
{
    public function testTestsDoNotTruncate(): void
    {
        $this->assertCodeMissing(
            '/->truncate\(/',
            ['tests', 'modules/*/Tests'],
            'truncate() — это DDL: неявный COMMIT переживает откат RefreshDatabase, записи протекают в соседние тесты. Используйте delete()',
        );
    }

    public function testNoPartialUpsert(): void
    {
        $this->assertCodeMissing(
            '/->upsert\(/',
            ['app', 'modules'],
            'upsert() — это INSERT ... ON DUPLICATE KEY: строгий MySQL требует все NOT NULL-поля даже для существующих строк. Массовое обновление — update() с CASE',
        );
    }

    public function testNoSitetime(): void
    {
        $this->assertCodeMissing(
            '/\bSITETIME\b/',
            ['app', 'modules', 'resources/views'],
            'SITETIME — мост для старых модулей до 15.0. Даты — datetime-колонки и now()',
            ['app/helpers.php'],
        );
    }

    public function testNoGetUser(): void
    {
        // В PHP устаревший вызов ловит phpstan, шаблоны он не разбирает
        $this->assertCodeMissing(
            '/(?<![\w>:$])(?<!function )getUser\s*\(/',
            ['app', 'modules', 'resources/views'],
            'getUser() удаляется в 15.0 и прячет null от phpstan. Пользователь — auth()->user(), id — auth()->id(), в экшене контроллера — #[CurrentUser] User $user',
            ['app/helpers.php'],
        );
    }

    public function testServicesAreInjected(): void
    {
        // Свои шаблоны в resources/custom не проверяются: документация учит брать там сервис через new
        $this->assertCodeMissing(
            '/\bapp\(\s*\\\\?[\w\\\\]*Service::class|\bnew\s+\\\\?[\w\\\\]*Service\b/',
            ['app/Http/Controllers', 'app/Http/Middleware', 'app/Traits', 'modules/*/Http/Controllers', 'modules/*/Http/Middleware', 'resources/views', 'modules/*/resources/views'],
            'Сервис из app() или через new прячет зависимость. В экшене — параметр метода, в middleware — конструктор, в шаблон — передать из контроллера или метода модели',
        );
    }

    public function testRouteActionsExist(): void
    {
        $found = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uses = $route->getAction('uses');

            if (! is_string($uses) || ! str_contains($uses, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $uses, 2);

            if (! method_exists($class, $method)) {
                $found[] = $route->uri() . ' → ' . $uses;
            }
        }

        $this->assertSame([], $found, 'Маршрут ведёт на метод, которого нет в контроллере: любой запрос по нему отдаёт 500. Удалите маршрут или добавьте метод');
    }

    public function testCurrentUserDoesNotShadowRouteParameter(): void
    {
        $found = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $names = $route->parameterNames();

            foreach ($route->signatureParameters() as $parameter) {
                if ($parameter->getAttributes(Authenticated::class, ReflectionAttribute::IS_INSTANCEOF) && in_array($parameter->getName(), $names, true)) {
                    $found[] = $route->uri() . ' → $' . $parameter->getName();
                }
            }
        }

        $this->assertSame([], $found, 'Сегмент маршрута с тем же именем подставит модель из адреса вместо текущего пользователя. Назовите параметр иначе, например #[CurrentUser] User $me');
    }

    public function testCurrentUserRoutesRequireAuth(): void
    {
        // Алиасы из bootstrap/app.php попадают в роутер, только когда создано HTTP-ядро
        $this->app->make(HttpKernel::class);

        $found = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $current = array_filter(
                $route->signatureParameters(),
                static fn (ReflectionParameter $parameter): bool => ! $parameter->allowsNull()
                    && $parameter->getAttributes(Authenticated::class, ReflectionAttribute::IS_INSTANCEOF),
            );

            if (! $current) {
                continue;
            }

            // Классы с учётом withoutMiddleware(): алиас, класс и группа сводятся к одному виду
            $guarded = array_filter(
                Route::gatherRouteMiddleware($route),
                static fn (mixed $middleware): bool => is_string($middleware)
                    && in_array(explode(':', $middleware)[0], [CheckUser::class, CheckToken::class, CheckAdmin::class, Authenticate::class], true),
            );

            if (! $guarded) {
                $found[] = $route->uri();
            }
        }

        $this->assertSame([], $found, 'Гость дойдёт до экшена, и #[CurrentUser] получит null — TypeError и 500. Закройте маршрут check.user, check.token или check.admin, либо примите ?User');
    }

    public function testMailLinksAreAbsolute(): void
    {
        $this->assertCodeMissing(
            '/\basset\(/',
            ['resources/views/mailer', 'modules/*/resources/views/mailer'],
            'asset() при ASSET_URL=/ даёт относительный путь — в письме он не откроется. Ссылки наружу — url() или route()',
        );
    }

    public function testTranslationKeysExist(): void
    {
        // Ключ без перевода выводится как есть, а тест с __() сравнивает его сам с собой
        $found = [];

        $files = Finder::create()->files()->name('*.php')
            ->in(array_filter([base_path('app'), base_path('resources/views'), base_path('routes'), ...glob(base_path('modules/*'), GLOB_ONLYDIR) ?: []]))
            ->followLinks()
            ->notPath(['vendor', '#(^|/)lang/#', '#(^|/)Tests/#', 'Docs/resources']);

        foreach ($files as $file) {
            preg_match_all("/(?:\b__|\btrans|\btrans_choice|@lang)\(\s*'((?:[a-z_]+::)?[a-z_]+\.[\w.\-]+)'(?!\s*\.)/", $file->getContents(), $matches);

            foreach ($matches[1] as $key) {
                if (! $this->translationExists($key)) {
                    $found[] = str_replace(base_path() . '/', '', $file->getPathname()) . ' → ' . $key;
                }
            }
        }

        $this->assertSame([], array_values(array_unique($found)), 'Нет перевода: на странице выведется сам ключ. Добавьте ключ в resources/lang/ru или в resources/lang модуля');
    }

    /**
     * Ключ модуля ищется в его файлах: перевод выключенного модуля не загружен
     */
    private function translationExists(string $key): bool
    {
        if (! str_contains($key, '::')) {
            return Lang::has($key, 'ru', false);
        }

        [$namespace, $item] = explode('::', $key, 2);
        [$group, $path] = explode('.', $item, 2);

        foreach (glob(base_path('modules/*'), GLOB_ONLYDIR) ?: [] as $dir) {
            $file = $dir . '/resources/lang/ru/' . $group . '.php';

            if (Str::snake(basename($dir)) === $namespace && is_file($file)) {
                return Arr::has((array) require $file, $path);
            }
        }

        return false;
    }

    /**
     * @param list<string> $paths  пути от корня проекта, можно с * для модулей
     * @param list<string> $except файлы, где запрет не действует
     */
    private function assertCodeMissing(string $pattern, array $paths, string $reason, array $except = []): void
    {
        $dirs = array_filter(
            array_merge(...array_map(static fn (string $path): array => glob(base_path($path), GLOB_ONLYDIR) ?: [], $paths)),
        );

        if (! $dirs) {
            $this->markTestSkipped('Нет каталогов для проверки');
        }

        $found = [];

        $files = Finder::create()->files()->name('*.php')->in($dirs)->followLinks()
            ->notPath(['vendor', 'Docs/resources']);

        foreach ($files as $file) {
            $path = str_replace(base_path() . '/', '', $file->getPathname());

            if (in_array($path, $except, true)) {
                continue;
            }

            foreach (file($file->getPathname()) ?: [] as $number => $line) {
                // Комментарии объясняют запреты и сами упоминают запрещённое
                if (preg_match('~^\s*(//|\*|/\*)~', $line)) {
                    continue;
                }

                if (preg_match($pattern, $line)) {
                    $found[] = $path . ':' . ($number + 1);
                }
            }
        }

        $this->assertSame([], $found, $reason);
    }
}
