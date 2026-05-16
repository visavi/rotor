<?php

declare(strict_types=1);

namespace App\Classes;

class Restatement
{
    public static array $handlers = [];

    public static function register(string $mode, callable $callback): void
    {
        static::$handlers[$mode] = $callback;
    }
}
