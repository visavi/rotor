<?php

declare(strict_types=1);

namespace App\Classes;

class Registry
{
    private static $instance;

    private $registry = [];

    public static function getInstance(): ?Registry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function set($key, $object): void
    {
        self::getInstance()->registry[$key] = $object;
    }

    public static function get($key)
    {
        return self::getInstance()->registry[$key];
    }

    public static function has($name): bool
    {
        if (! isset(self::getInstance()->registry[$name])) {
            return false;
        }

        return true;
    }

    public static function remove($name): void
    {
        if (self::has($name)) {
            unset(self::getInstance()->registry[$name]);
        }
    }

    private function __construct() {
    }

    private function __clone() {
    }
}
