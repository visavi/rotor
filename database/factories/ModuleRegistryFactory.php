<?php

namespace Database\Factories;

use App\Models\ModuleRegistry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<ModuleRegistry>
 */
class ModuleRegistryFactory extends Factory
{
    protected $model = ModuleRegistry::class;

    public function definition(): array
    {
        return [
            'url' => 'https://' . $this->faker->unique()->domainName(),
        ];
    }
}
