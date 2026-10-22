<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'relate_type' => 'test',
            'relate_id'   => 0,
            'text'        => $this->faker->sentence(),
            'ip'          => '127.0.0.1',
            'brow'        => 'test',
        ];
    }
}
