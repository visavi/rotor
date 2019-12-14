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

    /**
     * Set value
     *
     * @param mixed $key
     * @param mixed $value
     */
    public static function set($key, $value): void
    {
        self::getInstance()->registry[$key] = $value;
    }

    /**
     * Get value
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::getInstance()->registry[$key] ?? $default;
    }

    /**
     * Check exists
     *
     * @param mixed $name
     *
     * @return bool
     */
    public static function has($name): bool
    {
        if (! isset(self::getInstance()->registry[$name])) {
            return false;
        }

        return true;
    }

    /**
     * Remove
     *
     * @param mixed $name
     */
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
