<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Выбор языка интерфейса
 */
class Locale
{
    /**
     * Каталоги переводов модулей-языков
     *
     * @var array<string, string>
     */
    private static array $paths = [];

    /**
     * Кэш списка языков на запрос
     *
     * @var array<int, string>|null
     */
    private static ?array $languages = null;

    /**
     * Ставит язык, откатываясь на язык сайта, если каталога переводов нет
     */
    public static function apply(?string $language): void
    {
        if (! $language || ! in_array($language, self::available(), true)) {
            $language = setting('language', config('app.locale'));
        }

        App::setLocale($language);
    }

    /**
     * Язык из Accept-Language, ближайший к имеющимся переводам
     *
     * Язык сайта идёт первым в кандидатах: без заголовка или без совпадений вернётся он
     */
    public static function fromRequest(Request $request): string
    {
        $languages = array_values(array_unique([
            (string) setting('language', config('app.locale')),
            ...self::available(),
        ]));

        return (string) $request->getPreferredLanguage($languages);
    }

    /**
     * Подключает каталог переводов модуля-языка
     *
     * Файлы не копируются в ядро: загрузчик ищет {path}/{locale}/{group}.php,
     * {path}/{locale}.json и переводы чужих модулей в {path}/vendor/{namespace}/{locale}
     */
    public static function addPath(string $path): void
    {
        self::$paths[$path] = $path;
        self::$languages = null;

        app('translation.loader')->addPath($path);
    }

    /**
     * Каталоги переводов: ядро и модули-языки
     *
     * @return array<int, string>
     */
    public static function paths(): array
    {
        return [resource_path('lang'), ...array_values(self::$paths)];
    }

    /**
     * Каталог переводов языка: в ядре или в модуле-языке
     */
    public static function path(string $language): ?string
    {
        foreach (self::paths() as $path) {
            if (is_dir($path . '/' . $language)) {
                return $path . '/' . $language;
            }
        }

        return null;
    }

    /**
     * Языки, для которых есть каталог переводов
     *
     * @return array<int, string>
     */
    public static function available(): array
    {
        if (self::$languages !== null) {
            return self::$languages;
        }

        $languages = [];
        foreach (self::paths() as $path) {
            foreach (glob($path . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
                $languages[] = basename($dir);
            }
        }

        // vendor — переводы для чужих модулей, не язык
        $languages = array_diff(array_unique($languages), ['vendor']);
        sort($languages);

        return self::$languages = $languages;
    }
}
