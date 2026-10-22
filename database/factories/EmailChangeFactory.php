<?php

namespace Database\Factories;

use App\Models\EmailChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<EmailChange>
 */
class EmailChangeFactory extends Factory
{
    protected $model = EmailChange::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email'   => $this->faker->unique()->safeEmail(),
            'token'   => $this->faker->sha256(),
        ];
    }
}
