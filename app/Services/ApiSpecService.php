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
        $requestFields = [];

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

            if (is_array($part['x-request-fields'] ?? null)) {
                $requestFields[] = $part['x-request-fields'];
            }

            if (is_array($part['x-config-sections'] ?? null)) {
                $spec = self::mergeConfigSections($spec, $part['x-config-sections']);
            }
        }

        // После всех адресов: модуль может дописать поле и в адрес другого модуля
        foreach ($requestFields as $fields) {
            $spec = self::mergeRequestFields($spec, $fields);
        }

        return $spec;
    }

    /**
     * Разделы модуля в ответе /config
     *
     * Модуль отдаёт их секцией api в module.php и описывает сам — ключом
     * "x-config-sections": {"forum": {"type": "object", "properties": {...}}} в openapi.json.
     * Так выключенный модуль не висит в документации ядра
     */
    public static function mergeConfigSections(array $spec, array $sections): array
    {
        $schema = $spec['paths']['/config']['get']['responses']['200']['content']['application/json']['schema'] ?? null;

        if (! is_array($schema)) {
            return $spec;
        }

        $schema['properties'] = array_merge($schema['properties'] ?? [], array_filter($sections, 'is_array'));

        $spec['paths']['/config']['get']['responses']['200']['content']['application/json']['schema'] = $schema;

        return $spec;
    }

    /**
     * Поля модуля в теле чужого запроса
     *
     * Модуль проверяет их хуками вроде Registry::onRegisterValidate, а адрес принадлежит
     * ядру или другому модулю — целиком его не переписать. В openapi.json модуля:
     * "x-request-fields": {"/register": {"post": {"properties": {...}, "required": [...]}}}
     *
     * Несуществующий адрес или запрос без тела пропускается, а не создаётся пустым
     */
    public static function mergeRequestFields(array $spec, array $fields): array
    {
        foreach ($fields as $path => $methods) {
            if (! is_array($methods)) {
                continue;
            }

            foreach ($methods as $method => $extra) {
                $schema = $spec['paths'][$path][$method]['requestBody']['content']['application/json']['schema'] ?? null;

                if (! is_array($schema) || ! is_array($extra)) {
                    continue;
                }

                $schema['properties'] = array_merge($schema['properties'] ?? [], (array) ($extra['properties'] ?? []));
                // Списки объединяются без повторов: array_replace_recursive затёр бы их по номерам
                $required = array_values(array_unique([...$schema['required'] ?? [], ...(array) ($extra['required'] ?? [])]));

                // Пустой required OpenAPI 3.0 не допускает
                if ($required) {
                    $schema['required'] = $required;
                }

                $spec['paths'][$path][$method]['requestBody']['content']['application/json']['schema'] = $schema;
            }
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
