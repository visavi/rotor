<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

/**
 * @property int                 $id
 * @property string              $url
 * @property string              $name
 * @property bool                $active
 * @property array               $cached_data
 * @property \Carbon\Carbon|null $cached_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 */
class ModuleRegistry extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active'      => 'bool',
            'cached_data' => 'array',
            'cached_at'   => 'datetime',
        ];
    }

    public function fetch(bool $force = false): array
    {
        $ttl = 3600;

        if (! $force && $this->cached_data && $this->cached_at?->gt(now()->subSeconds($ttl))) {
            return $this->cached_data;
        }

        try {
            $response = Http::timeout(10)->get($this->url);

            if (! $response->ok()) {
                return $this->cached_data ?? [];
            }

            $data = $response->json();

            if (! is_array($data)) {
                return $this->cached_data ?? [];
            }

            $this->update([
                'name'        => $data['name'] ?? $this->name,
                'cached_data' => $data,
                'cached_at'   => now(),
            ]);

            return $data;
        } catch (\Exception) {
            return $this->cached_data ?? [];
        }
    }

    public static function getAvailableModules(bool $force = false): array
    {
        $registries = self::query()->where('active', true)->get();
        $modules = [];

        foreach ($registries as $registry) {
            $data = $registry->fetch($force);

            foreach ($data['modules'] ?? [] as $module) {
                if (! isset($module['module'])) {
                    continue;
                }

                $name = $module['module'];
                $registryLabel = $registry->name ?: $registry->url;

                $best = self::bestCompatibleVersion($module['versions'] ?? []);
                $version = $best ?? $module['versions'][0] ?? [];
                $modules[$name] = array_merge(
                    array_diff_key($module, ['versions' => true]),
                    $version,
                    ['registry' => $registryLabel],
                );
            }
        }

        return $modules;
    }

    private static function bestCompatibleVersion(array $versions): ?array
    {
        $compatible = array_filter(
            $versions,
            static fn (array $v) => empty($v['requires']) || version_compare(ROTOR_VERSION, $v['requires'], '>='),
        );

        if (empty($compatible)) {
            return null;
        }

        usort($compatible, static fn ($a, $b) => version_compare($b['version'], $a['version']));

        return $compatible[0];
    }
}
