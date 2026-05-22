<?php

declare(strict_types=1);

namespace App\Classes;

class Registry
{
    public static array $complaintTypes = [];
    public static array $fileTypes = [];
    public static array $mediaTypes = [];
    public static array $ratingTypes = [];
    public static array $spamTypes = [];
    public static array $sitemapPages = [];
    public static array $pollResolvers = [];
    public static array $deleteUserCallbacks = [];
    public static array $adminDeleteHandlers = [];
    public static array $feedTypes = [];
    public static array $feedViewMap = [];
    public static array $searchTypes = [];
    public static array $searchViewMap = [];
    public static array $searchMorphWith = [];
    public static array $searchClasses = [];

    public static function complaint(string $type, callable $handler): void
    {
        static::$complaintTypes[$type] = $handler;
    }

    public static function fileType(string $morphName): void
    {
        static::$fileTypes[] = $morphName;
    }

    public static function mediaType(string $morphName): void
    {
        static::$mediaTypes[] = $morphName;
    }

    public static function ratingType(string $morphName): void
    {
        static::$ratingTypes[] = $morphName;
    }

    public static function spam(string $morphName, string $label): void
    {
        static::$spamTypes[$morphName] = $label;
    }

    public static function sitemap(string $key, callable $handler): void
    {
        static::$sitemapPages[$key] = $handler;
    }

    public static function pollResolver(string $class, callable $handler): void
    {
        static::$pollResolvers[$class] = $handler;
    }

    public static function onDeleteUser(callable $handler): void
    {
        static::$deleteUserCallbacks[] = $handler;
    }

    public static function onAdminDeleteUser(callable $handler): void
    {
        static::$adminDeleteHandlers[] = $handler;
    }

    public static function feed(string $class, array $withs, string $view): void
    {
        /** @var class-string $class */
        $morphName = $class::$morphName;
        static::$feedTypes[$morphName] = ['class' => $class, 'withs' => $withs];
        static::$feedViewMap[$morphName] = $view;
    }

    public static function search(string $class, string $label, string $view, array $with = []): void
    {
        /** @var class-string $class */
        $morphName = $class::$morphName;
        static::$searchTypes[$morphName] = $label;
        static::$searchViewMap[$morphName] = $view;
        static::$searchClasses[] = $class;
        if ($with) {
            static::$searchMorphWith[$class] = $with;
        }
    }
}
