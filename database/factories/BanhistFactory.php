<?php

namespace Database\Factories;

use App\Models\Banhist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Banhist>
 */
class BanhistFactory extends Factory
{
    protected $model = Banhist::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'send_user_id' => User::factory(),
            'type'         => Banhist::BAN,
        ];
    }
}
