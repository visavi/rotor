<?php

namespace Database\Factories;

use App\Models\Reader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<Reader>
 */
class ReaderFactory extends Factory
{
    protected $model = Reader::class;

    public function definition(): array
    {
        return [
            'relate_type' => 'test',
            'relate_id'   => 0,
            'ip'          => $this->faker->ipv4(),
        ];
    }
}
