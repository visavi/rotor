<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Sticker;
use Illuminate\Support\Facades\Cache;

/**
 * Резолвит стикеры в HTML на лету.
 *
 * Стикеры в БД хранятся как <img class="sticker" src="..." alt="code">.
 * Поскольку админ может удалить или переименовать файл стикера, src в
 * сохранённом HTML может протухнуть. Этот класс берёт актуальный src
 * из кэша по alt-коду; если стикер удалён — подставляет текстовый код.
 */
class StickerResolver
{
    public static function resolve(?string $html): ?string
    {
        if ($html === null || ! str_contains($html, 'class="sticker"')) {
            return $html;
        }

        $map = self::getMap();

        return preg_replace_callback(
            '/<img\b([^>]*)>/i',
            static function (array $match) use ($map): string {
                $attrs = $match[1];

                if (! preg_match('/class="sticker"/i', $attrs)) {
                    return $match[0];
                }

                if (! preg_match('/alt="([^"]+)"/i', $attrs, $altMatch)) {
                    return $match[0];
                }

                $code = $altMatch[1];

                if (! isset($map[$code])) {
                    return htmlspecialchars($code, ENT_QUOTES);
                }

                return '<img class="sticker" src="' . $map[$code] . '" alt="' . $code . '">';
            },
            $html
        );
    }

    /**
     * @return array<string, string> code → src (путь к файлу)
     */
    private static function getMap(): array
    {
        return Cache::rememberForever('stickers_map', static function (): array {
            return Sticker::query()
                ->pluck('name', 'code')
                ->toArray();
        });
    }
}
