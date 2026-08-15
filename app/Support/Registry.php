<?php

declare(strict_types=1);

namespace App\Support;

class Registry
{
    public static array $complaintTypes = [];
    public static array $fileTypes = [];
    public static array $labelTypes = [];
    public static array $mediaTypes = [];
    public static array $ratingTypes = [];
    public static array $spamTypes = [];
    public static array $sitemapPages = [];
    public static array $onDeleteUser = [];
    public static array $onAdminDeleteUser = [];
    public static array $onSaveStatistic = [];
    public static array $onAdminLog = [];
    public static array $onProfileValidate = [];
    public static array $onProfileSave = [];
    public static array $feeds = [];
    public static array $search = [];
    public static array $apiConfig = [];

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
     * Регистрирует тип как источник спама (метка в админке берётся из labelTypes)
     */
    public static function spamType(string $morphName): void
    {
        static::$spamTypes[] = $morphName;
    }

    /**
     * Регистрирует страницу sitemap с её генератором
     */
    public static function sitemap(string $key, callable $handler): void
    {
        static::$sitemapPages[$key] = $handler;
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
     * Регистрирует колбэк на сохранение статистики посещений
     *
     * Колбэк вида fn(bool $newHost, int $hits): void
     */
    public static function onSaveStatistic(callable $handler): void
    {
        static::$onSaveStatistic[] = $handler;
    }

    /**
     * Регистрирует колбэк логирования действий администратора
     *
     * Колбэк вида fn(Request $request): void — вызывается после отправки ответа
     */
    public static function onAdminLog(callable $handler): void
    {
        static::$onAdminLog[] = $handler;
    }

    /**
     * Регистрирует колбэк валидации при сохранении профиля пользователя
     *
     * Колбэк вида fn(User $user, Request $request, Validator $validator, bool $strict): void
     * $strict = false, когда профиль сохраняет администратор — обязательность полей не проверяется
     */
    public static function onProfileValidate(callable $handler): void
    {
        static::$onProfileValidate[] = $handler;
    }

    /**
     * Регистрирует колбэк сохранения данных профиля пользователя
     *
     * Колбэк вида fn(User $user, Request $request): void — вызывается после успешной валидации
     */
    public static function onProfileSave(callable $handler): void
    {
        static::$onProfileSave[] = $handler;
    }

    /**
     * Регистрирует модель как источник ленты
     *
     * @param array $config ['with' => [], 'view' => '', 'scope' => ?Closure, 'poll' => ?Closure]
     */
    public static function feed(string $class, array $config): void
    {
        /** @var class-string $class */
        $morphName = $class::$morphName;
        static::$feeds[$morphName] = ['class' => $class] + $config;
    }

    /**
     * Регистрирует секцию модуля в /api/config
     *
     * Настройки лежат в общей таблице плоским списком, поэтому ядро не знает,
     * какие из них принадлежат модулю и какие безопасно отдавать наружу —
     * модуль объявляет это сам
     *
     * @param array<string, mixed> $settings [ключ в ответе => имя настройки либо готовое значение]
     */
    public static function apiConfig(string $section, array $settings): void
    {
        static::$apiConfig[$section] = $settings;
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
