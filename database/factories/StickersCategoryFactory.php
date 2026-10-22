<?php

namespace Database\Factories;

use App\Models\StickersCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<StickersCategory>
 */
class StickersCategoryFactory extends Factory
{
    protected $model = StickersCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
        ];
    }
}
