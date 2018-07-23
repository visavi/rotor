<?php

namespace App\Classes;

class Registry
{
    static private $_instance;

    private $registry = [];

    public static function getInstance(): ?Registry
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
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
