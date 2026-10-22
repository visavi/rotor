<?php

namespace Database\Factories;

use App\Models\Online;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Online>
 */
class OnlineFactory extends Factory
{
    protected $model = Online::class;

    public function definition(): array
    {
        return [
            'uid'  => $this->faker->unique()->md5(),
            'ip'   => $this->faker->ipv4(),
            'brow' => 'test',
        ];
    }
}
