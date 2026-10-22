<?php

namespace Database\Factories;

use App\Models\Dialogue;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Dialogue>
 */
class DialogueFactory extends Factory
{
    protected $model = Dialogue::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'user_id'    => User::factory(),
            'author_id'  => User::factory(),
            'type'       => Dialogue::IN,
        ];
    }
}
