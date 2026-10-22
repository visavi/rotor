<?php

namespace Database\Factories;

use App\Models\Error;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Error>
 */
class ErrorFactory extends Factory
{
    protected $model = Error::class;

    public function definition(): array
    {
        return [
            'code' => 404,
            'ip'   => $this->faker->ipv4(),
            'brow' => 'test',
        ];
    }
}
