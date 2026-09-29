<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Support\Str;

/**
 * Название места — город, страна: свободный ввод без справочника
 *
 * Лишние пробелы убираются, первая буква заглавная — « москва» и «Москва»
 * сливаются в одно место, и подсказки не двоятся. Остальной регистр не трогаем,
 * иначе «Нью-Йорк» стал бы «Нью-йорк». Сравнение в MySQL и так без учёта регистра
 */
class PlaceCast extends TextCast
{
    protected function input(string $value): string
    {
        return Str::ucfirst(preg_replace('/\s+/u', ' ', trim($value)));
    }
}
