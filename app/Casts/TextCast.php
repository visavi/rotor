<?php

namespace App\Casts;

use App\Support\Registry;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Пользовательский текст без разметки — заголовки, подписи
 *
 * Пустое поле формы приходит null (ConvertEmptyStringsToNull), а колонки
 * NOT NULL — поэтому null сохраняется пустой строкой. Колонкам, где null
 * значим, каст подключается с параметром: TextCast::class . ':nullable'
 *
 * Фильтры модулей (Registry::textFilter) применяются при чтении: в базе
 * остаётся оригинал, и убранное из фильтра слово возвращает старые тексты
 */
class TextCast implements CastsAttributes
{
    private bool $nullable;

    public function __construct(?string $mode = null)
    {
        $this->nullable = $mode === 'nullable';
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->output((string) $value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return $this->nullable ? null : '';
        }

        return $this->input((string) $value);
    }

    /**
     * Готовит сохранённое значение к выводу
     */
    protected function output(string $value): string
    {
        return Registry::filterText($value);
    }

    /**
     * Готовит значение к сохранению
     */
    protected function input(string $value): string
    {
        return $value;
    }
}
