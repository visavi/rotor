<?php

namespace App\Casts;

use App\Services\StickerResolver;
use App\Support\HtmlSanitizer;
use App\Support\Registry;

/**
 * HTML из редактора: чистится при записи, фильтруется при выводе
 *
 * Правила для null и фильтров — как у TextCast. Фильтры применяются только
 * к тексту между тегами: слово из списка не должно ломать классы, ссылки
 * и атрибуты стикеров
 */
class HtmlCast extends TextCast
{
    protected function output(string $value): string
    {
        if (Registry::$textFilters) {
            $parts = preg_split('/(<[^>]*>)/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

            if ($parts !== false) {
                // Чётные элементы — текст, нечётные — теги
                foreach ($parts as $index => $part) {
                    if ($index % 2 === 0) {
                        $parts[$index] = Registry::filterText($part);
                    }
                }

                $value = implode('', $parts);
            }
        }

        return (string) StickerResolver::resolve($value);
    }

    protected function input(string $value): string
    {
        return HtmlSanitizer::sanitize($value);
    }
}
