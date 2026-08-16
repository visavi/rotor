<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

/**
 * Спецификация OpenAPI для Swagger UI
 *
 * Спека лежит в ядре целиком, а разделы приходят из модулей.
 * Поэтому перед отдачей из неё вычищается всё, для чего нет
 * зарегистрированных маршрутов — выключенный модуль в доке не появится.
 */
class ApiSpecService
{
    /**
     * Спецификация только с доступными методами
     */
    public static function spec(): array
    {
        $spec = Yaml::parseFile(public_path('openapi/openapi.yaml'));

        $spec['paths'] = self::filterPaths($spec['paths'] ?? []);

        if (isset($spec['components']['schemas'])) {
            $spec['components']['schemas'] = self::usedSchemas($spec);
        }

        return $spec;
    }

    /**
     * Пути, у которых есть маршрут с таким же методом
     */
    private static function filterPaths(array $paths): array
    {
        $routes = self::routes();
        $filtered = [];

        foreach ($paths as $path => $operations) {
            $methods = $routes[self::normalize($path)] ?? null;

            if (! $methods) {
                continue;
            }

            foreach ($operations as $method => $operation) {
                // Кроме методов в путях лежат общие ключи вроде parameters
                if (in_array(strtoupper($method), $methods, true) || ! self::isMethod($method)) {
                    $filtered[$path][$method] = $operation;
                }
            }
        }

        return $filtered;
    }

    /**
     * Маршруты API в виде [путь => методы]
     */
    private static function routes(): array
    {
        $routes = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! Str::startsWith($route->uri(), 'api/')) {
                continue;
            }

            $path = self::normalize('/' . Str::after($route->uri(), 'api/'));

            $routes[$path] = array_merge($routes[$path] ?? [], $route->methods());
        }

        return $routes;
    }

    /**
     * Имена параметров в спеке и маршрутах не обязаны совпадать
     */
    private static function normalize(string $path): string
    {
        return rtrim((string) preg_replace('#\{[^}]+}#', '{}', $path), '/');
    }

    private static function isMethod(string $key): bool
    {
        return in_array($key, ['get', 'post', 'put', 'patch', 'delete', 'head', 'options', 'trace'], true);
    }

    /**
     * Схемы, на которые ещё есть ссылки
     */
    private static function usedSchemas(array $spec): array
    {
        $schemas = $spec['components']['schemas'];
        $used = self::refs($spec['paths']);

        // Схемы ссылаются друг на друга, поэтому обходим по цепочке
        do {
            $found = false;

            foreach ($used as $name) {
                if (isset($schemas[$name])) {
                    foreach (self::refs($schemas[$name]) as $ref) {
                        if (! in_array($ref, $used, true)) {
                            $used[] = $ref;
                            $found = true;
                        }
                    }
                }
            }
        } while ($found);

        return array_intersect_key($schemas, array_flip($used));
    }

    /**
     * Имена схем из ссылок $ref
     */
    private static function refs(array $data): array
    {
        $refs = [];

        array_walk_recursive($data, static function ($value, $key) use (&$refs) {
            if ($key === '$ref' && is_string($value) && Str::startsWith($value, '#/components/schemas/')) {
                $refs[] = Str::afterLast($value, '/');
            }
        });

        return array_values(array_unique($refs));
    }
}
