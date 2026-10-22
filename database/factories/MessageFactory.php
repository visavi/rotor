<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'author_id' => User::factory(),
            'text'      => $this->faker->sentence(),
        ];
    }
}
