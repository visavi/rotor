<?php

namespace App\Classes;

class HookManager
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
     * Регистрирует функцию для хука
     */
    public static function addHook(string $hookName, callable $callback, int $priority = 0): void
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
    public static function callHook(string $hookName, mixed $value = null)
    {
        if (isset(self::$hooks[$hookName])) {
            foreach (self::$hooks[$hookName] as $hook) {
                $value = call_user_func($hook['callback'], $value);
            }
        }

        return $value;
    }
}
