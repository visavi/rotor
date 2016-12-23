<?php

use Phinx\Seed\AbstractSeed;

class StatusSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run()
    {
        $this->execute('TRUNCATE status');

        $table = $this->table('status');

        $data = [
            ['id' => 1, 'topoint' => 0, 'point' => 249, 'name' => 'Новичок'],
            ['id' => 2, 'topoint' => 250, 'point' => 499, 'name' => 'Местный'],
            ['id' => 3, 'topoint' => 500, 'point' => 999, 'name' => 'Продвинутый'],
            ['id' => 4, 'topoint' => 1000, 'point' => 1999, 'name' => 'Бывалый'],
            ['id' => 5, 'topoint' => 2000, 'point' => 3999, 'name' => 'Специалист', 'color' => '#FF8800'],
            ['id' => 6, 'topoint' => 4000, 'point' => 5999, 'name' => 'Знаток', 'color' => '#DC143C'],
            ['id' => 7, 'topoint' => 6000, 'point' => 7999, 'name' => 'Мастер', 'color' => '#0080FF'],
            ['id' => 8, 'topoint' => 8000, 'point' => 9999, 'name' => 'Профессионал', 'color' => '#000000'],
            ['id' => 9, 'topoint' => 10000, 'point' => 14999, 'name' => 'Гуру', 'color' => '#32608A'],
            ['id' => 10, 'topoint' => 15000, 'point' => 100000, 'name' => 'Легенда', 'color' => '#ff0000'],
        ];

        $table->insert($data)->save();
    }
}
