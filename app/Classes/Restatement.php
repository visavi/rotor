<?php

declare(strict_types=1);

namespace App\Classes;

class Restatement
{
    public static array $handlers = [];

    public static function boot(): void {}

    public static function register(string $mode, callable $callback): void
    {
        static::$handlers[$mode] = $callback;
    }

    public static function run(string|array $mode): void
    {
        foreach ((array) $mode as $m) {
            if (isset(static::$handlers[$m])) {
                (static::$handlers[$m])();
            }
        }
    }
}
