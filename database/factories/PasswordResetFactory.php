<?php

namespace Database\Factories;

use App\Models\PasswordReset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<PasswordReset>
 */
class PasswordResetFactory extends Factory
{
    protected $model = PasswordReset::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->safeEmail(),
            'token' => $this->faker->sha256(),
        ];
    }
}
