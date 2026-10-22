<?php

namespace Database\Factories;

use App\Models\BlackList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<BlackList>
 */
class BlackListFactory extends Factory
{
    protected $model = BlackList::class;

    public function definition(): array
    {
        return [
            'type'    => 'login',
            'value'   => $this->faker->unique()->userName(),
            'user_id' => User::factory(),
        ];
    }
}
