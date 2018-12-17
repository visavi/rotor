<?php

use Phinx\Seed\AbstractSeed;

class StatusSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        $this->execute('TRUNCATE status');

        $table = $this->table('status');

        $data = [
            ['topoint' => 0, 'point' => 249, 'name' => 'Новичок'],
            ['topoint' => 250, 'point' => 499, 'name' => 'Местный'],
            ['topoint' => 500, 'point' => 999, 'name' => 'Продвинутый'],
            ['topoint' => 1000, 'point' => 1999, 'name' => 'Бывалый'],
            ['topoint' => 2000, 'point' => 3999, 'name' => 'Специалист', 'color' => '#FF8800'],
            ['topoint' => 4000, 'point' => 5999, 'name' => 'Знаток', 'color' => '#DC143C'],
            ['topoint' => 6000, 'point' => 7999, 'name' => 'Мастер', 'color' => '#0080FF'],
            ['topoint' => 8000, 'point' => 9999, 'name' => 'Профессионал', 'color' => '#000000'],
            ['topoint' => 10000, 'point' => 14999, 'name' => 'Гуру', 'color' => '#32608A'],
            ['topoint' => 15000, 'point' => 100000, 'name' => 'Легенда', 'color' => '#ff0000'],
        ];

        $table->insert($data)->save();
    }
}
