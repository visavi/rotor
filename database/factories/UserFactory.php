<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'login'          => $this->faker->unique()->lexify('user_??????'),
            'name'           => $this->faker->firstName(),
            'email'          => $this->faker->unique()->safeEmail(),
            'password'       => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'level'          => User::USER,
            'remember_token' => Str::random(10),
            'created_at'     => SITETIME,
        ];
    }

    public function admin(): static
    {
        return $this->state(['level' => User::ADMIN]);
    }

    public function boss(): static
    {
        return $this->state(['level' => User::BOSS]);
    }
}
