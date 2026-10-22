<?php

namespace Database\Factories;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    public function definition(): array
    {
        return [
            'type'    => $this->faker->unique()->lexify('notice_????'),
            'name'    => $this->faker->words(2, true),
            'text'    => $this->faker->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
