<?php

namespace App\Classes;

class Hook
{
    private static array $hooks = [];

    /**
     * Возвращает все хуки
     */
    public static function getHooks()
    {
        return self::$hooks;
    }

    /**
     * Добавляет функцию для хука
     */
    public static function add(string $hookName, callable $callback, int $priority = 0): void
    {
        if (! isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = [];
        }

        self::$hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        usort(self::$hooks[$hookName], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }

    /**
     * Вызывает хук
     */
    public static function call(string $hookName, mixed $value = null)
    {
        if (isset(self::$hooks[$hookName])) {
            foreach (self::$hooks[$hookName] as $hook) {
                $value = call_user_func($hook['callback'], $value);
            }
        }

        return $value;
    }
}
