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
     * Языки, для которых есть каталог переводов
     *
     * @return array<int, string>
     */
    public static function available(): array
    {
        static $languages;

        return $languages ??= array_values(array_filter(
            scandir(resource_path('lang')) ?: [],
            static fn (string $dir) => $dir[0] !== '.' && is_dir(resource_path('lang/' . $dir)),
        ));
    }
}
