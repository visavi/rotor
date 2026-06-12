<?php

declare(strict_types=1);

namespace App\Classes;

class Registry
{
    public static array $complaintTypes = [];
    public static array $fileTypes = [];
    public static array $labelTypes = [];
    public static array $mediaTypes = [];
    public static array $ratingTypes = [];
    public static array $spamTypes = [];
    public static array $sitemapPages = [];
    /** @var array<class-string, callable> */
    public static array $pollResolvers = [];
    public static array $onDeleteUser = [];
    public static array $onAdminDeleteUser = [];
    public static array $feeds = [];
    public static array $search = [];

    /**
     * Регистрирует обработчик жалобы на контент типа $type
     */
    public static function complaint(string $type, callable $handler): void
    {
        static::$complaintTypes[$type] = $handler;
    }

    /**
     * Помечает тип как поддерживающий загрузку файлов (не медиа)
     */
    public static function fileType(string $morphName): void
    {
        static::$fileTypes[] = $morphName;
    }

    /**
     * Помечает тип как поддерживающий загрузку медиафайлов (фото/видео)
     */
    public static function mediaType(string $morphName): void
    {
        static::$mediaTypes[] = $morphName;
    }

    /**
     * Помечает тип как поддерживающий рейтинг
     */
    public static function ratingType(string $morphName): void
    {
        static::$ratingTypes[] = $morphName;
    }

    /**
     * Регистрирует отображаемое название для morph-типа
     */
    public static function label(string $morphName, string $label): void
    {
        static::$labelTypes[$morphName] = $label;
    }

    /**
     * Регистрирует тип как источник спама с меткой для админки
     */
    public static function spamType(string $morphName, string $label): void
    {
        static::$spamTypes[$morphName] = $label;
    }

    /**
     * Регистрирует страницу sitemap с её генератором
     */
    public static function sitemap(string $key, callable $handler): void
    {
        static::$sitemapPages[$key] = $handler;
    }

    /**
     * Регистрирует резолвер голосований для модели
     */
    public static function pollResolver(string $class, callable $handler): void
    {
        static::$pollResolvers[$class] = $handler;
    }

    /**
     * Регистрирует колбэк на удаление пользователя
     */
    public static function onDeleteUser(callable $handler): void
    {
        static::$onDeleteUser[] = $handler;
    }

    /**
     * Регистрирует колбэк на удаление пользователя администратором
     */
    public static function onAdminDeleteUser(callable $handler): void
    {
        static::$onAdminDeleteUser[] = $handler;
    }

    /**
     * Регистрирует модель как источник ленты
     *
     * @param array $config ['with' => [], 'view' => '', 'scope' => ?Closure]
     */
    public static function feed(string $class, array $config): void
    {
        /** @var class-string $class */
        $morphName = $class::$morphName;
        static::$feeds[$morphName] = ['class' => $class] + $config;
    }

    /**
     * Регистрирует модель в полнотекстовом поиске: метка, шаблон и eager-загрузки
     */
    public static function search(string $class, string $view, array $with = []): void
    {
        /** @var class-string $class */
        $morphName = $class::$morphName;
        static::$search[$morphName] = ['class' => $class, 'view' => $view, 'with' => $with];
    }
}
