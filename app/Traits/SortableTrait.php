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
     */
    public static function getSorting(?string $sort, ?string $order): array
    {
        $options = static::sortableFields();

        // Незнакомый ключ — сортировка по умолчанию: date, а у моделей без него первая
        $default = isset($options['date']) ? 'date' : array_key_first($options);
        $sort = isset($options[$sort]) ? $sort : $default;
        $order = in_array($order, ['asc', 'desc']) ? $order : 'desc';

        $options[$sort]['badge'] = 'success';
        $options[$sort]['inverse'] = $order === 'asc' ? 'desc' : 'asc';
        $options[$sort]['icon'] = $order === 'asc' ? ' ↑' : ' ↓';

        $orderBy = [$options[$sort]['field'], $order];

        return [$options, $orderBy];
    }
}
