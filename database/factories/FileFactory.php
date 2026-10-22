<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Запись с обязательными полями: остальное — значения по умолчанию из миграции
 *
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'relate_type' => 'test',
            'relate_id'   => 0,
            'path'        => '/uploads/files/' . $this->faker->uuid() . '.txt',
            'name'        => $this->faker->word() . '.txt',
            'size'        => 1024,
            'user_id'     => User::factory(),
        ];
    }
}
