<?php

namespace Database\Factories;

use App\Models\Ban;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Ban>
 */
class BanFactory extends Factory
{
    protected $model = Ban::class;

    public function definition(): array
    {
        return [
            'ip' => $this->faker->unique()->ipv4(),
        ];
    }
}
