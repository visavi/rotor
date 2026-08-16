<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;

/**
 * Спецификация OpenAPI для Swagger UI
 *
 * Ядро описывает только свои методы, разделы приходят из модулей:
 * каждый несёт openapi.json со своими путями и схемами. Выключенный
 * модуль в доке не появится — его файла на диске нет.
 */
class ApiSpecService
{
    /**
     * Спецификация ядра вместе с разделами включённых модулей
     */
    public static function spec(): array
    {
        $spec = self::read(public_path('openapi/openapi.json'));

        foreach (Module::getEnabledModules() as $module => $data) {
            if (! ($data['files']['openapi'] ?? false)) {
                continue;
            }

            $part = self::read(base_path('modules/' . $module . '/openapi.json'));

            $spec['paths'] = array_merge($spec['paths'] ?? [], $part['paths'] ?? []);

            $spec['components']['schemas'] = array_merge(
                $spec['components']['schemas'] ?? [],
                $part['components']['schemas'] ?? [],
            );
        }

        return $spec;
    }

    /**
     * Битый или пропавший файл модуля не должен ронять всю доку
     */
    private static function read(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }
}
