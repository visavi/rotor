<?php

namespace Database\Factories;

use App\Models\Search;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Search>
 */
class SearchFactory extends Factory
{
    protected $model = Search::class;

    public function definition(): array
    {
        return [
            'text'        => $this->faker->sentence(),
            'relate_type' => 'test',
            'relate_id'   => 0,
        ];
    }
}
