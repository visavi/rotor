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
            ['topoint' => 0, 'point' => 249, 'name' => __('seeds.statuses.novice')],
            ['topoint' => 250, 'point' => 499, 'name' => __('seeds.statuses.local')],
            ['topoint' => 500, 'point' => 999, 'name' => __('seeds.statuses.advanced')],
            ['topoint' => 1000, 'point' => 1999, 'name' => __('seeds.statuses.experienced')],
            ['topoint' => 2000, 'point' => 3999, 'name' => __('seeds.statuses.specialist'), 'color' => '#FF8800'],
            ['topoint' => 4000, 'point' => 5999, 'name' => __('seeds.statuses.expert'), 'color' => '#DC143C'],
            ['topoint' => 6000, 'point' => 7999, 'name' => __('seeds.statuses.master'), 'color' => '#0080FF'],
            ['topoint' => 8000, 'point' => 9999, 'name' => __('seeds.statuses.professional'), 'color' => '#000000'],
            ['topoint' => 10000, 'point' => 14999, 'name' => __('seeds.statuses.guru'), 'color' => '#32608A'],
            ['topoint' => 15000, 'point' => 100000, 'name' => __('seeds.statuses.legend'), 'color' => '#ff0000'],
        ];

        $table->insert($data)->save();
    }
}
