<?php

declare(strict_types=1);

namespace App\Classes;

use Stringable;

class Hook
{
    private static array $hooks = [];

    /**
     * Возвращает все хуки
     */
    public static function getHooks(): array
    {
        return self::$hooks;
    }

    /**
     * Добавляет хук
     *
     * Принимает строку/Stringable или callable вида fn(mixed $args = null): string|Stringable|null.
     * Чем выше priority, тем раньше хук выводится.
     */
    public static function add(string $hookName, string|Stringable|callable $value, int $priority = 0): void
    {
        if (! isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = [];
        }

        self::$hooks[$hookName][] = [
            'value'    => $value,
            'priority' => $priority,
        ];

        usort(self::$hooks[$hookName], static fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    /**
     * Проверяет, зарегистрирован ли хук
     */
    public static function has(string $hookName): bool
    {
        return ! empty(self::$hooks[$hookName]);
    }

    /**
     * Вызывает хук
     */
    public static function call(string $hookName, mixed $args = null): string
    {
        $result = '<!--@' . $hookName . '-->';

        foreach (self::$hooks[$hookName] ?? [] as $hook) {
            $value = $hook['value'];
            $fragment = (is_string($value) || $value instanceof Stringable) ? $value : $value($args);

            if ($fragment === null || $fragment === '') {
                continue;
            }

            $result .= (string) $fragment . PHP_EOL;
        }

        return $result;
    }
}
