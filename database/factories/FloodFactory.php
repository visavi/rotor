<?php

namespace Database\Factories;

use App\Models\Flood;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Flood>
 */
class FloodFactory extends Factory
{
    protected $model = Flood::class;

    public function definition(): array
    {
        return [
            'uid'  => $this->faker->md5(),
            'page' => '/',
        ];
    }
}
