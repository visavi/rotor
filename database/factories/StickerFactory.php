<?php

namespace Database\Factories;

use App\Models\Sticker;
use App\Models\StickersCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Sticker>
 */
class StickerFactory extends Factory
{
    protected $model = Sticker::class;

    public function definition(): array
    {
        return [
            'category_id' => StickersCategory::factory(),
            'name'        => '/uploads/stickers/' . $this->faker->unique()->lexify('????????') . '.gif',
            'code'        => ':' . $this->faker->unique()->lexify('????????'),
        ];
    }
}
