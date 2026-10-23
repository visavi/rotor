<?php

declare(strict_types=1);

namespace App\Traits;

trait SortableTrait
{
    /**
     * Get sortable fields
     */
    protected static function sortableFields(): array
    {
        return ['date' => ['field' => 'created_at', 'label' => __('main.date')]];
    }

    /**
     * Get sorting
     *
     * Значения приходят из запроса как есть: ?sort[]= — массив, он падал на типе параметра.
     * Третьим идёт приведённый ключ: его, а не значение из запроса, отдавать в шаблон
     *
     * @return array{0: array, 1: array{0: string, 1: 'asc'|'desc'}, 2: string}
     */
    public static function getSorting(mixed $sort, mixed $order): array
    {
        $options = static::sortableFields();

        // Незнакомый ключ — сортировка по умолчанию: date, а у моделей без него первая
        $default = isset($options['date']) ? 'date' : array_key_first($options);
        $sort = is_string($sort) && isset($options[$sort]) ? $sort : (string) $default;
        $order = in_array($order, ['asc', 'desc'], true) ? $order : 'desc';

        $options[$sort]['badge'] = 'success';
        $options[$sort]['inverse'] = $order === 'asc' ? 'desc' : 'asc';
        $options[$sort]['icon'] = $order === 'asc' ? ' ↑' : ' ↓';

        // С таблицей: в запросах с join (голос пользователя, capped) created_at и rating есть и у polls
        $orderBy = [static::query()->qualifyColumn($options[$sort]['field']), $order];

        return [$options, $orderBy, $sort];
    }
}
