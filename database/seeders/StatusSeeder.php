<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['topoint' => 0, 'point' => 99, 'name' => __('seeds.statuses.novice'), 'color' => null],
            ['topoint' => 100, 'point' => 249, 'name' => __('seeds.statuses.local'), 'color' => null],
            ['topoint' => 250, 'point' => 499, 'name' => __('seeds.statuses.advanced'), 'color' => null],
            ['topoint' => 500, 'point' => 999, 'name' => __('seeds.statuses.experienced'), 'color' => null],
            ['topoint' => 1000, 'point' => 1499, 'name' => __('seeds.statuses.specialist'), 'color' => '#FF8800'],
            ['topoint' => 1500, 'point' => 1999, 'name' => __('seeds.statuses.expert'), 'color' => '#DC143C'],
            ['topoint' => 2000, 'point' => 2999, 'name' => __('seeds.statuses.master'), 'color' => '#0080FF'],
            ['topoint' => 3000, 'point' => 3999, 'name' => __('seeds.statuses.professional'), 'color' => '#000000'],
            ['topoint' => 4000, 'point' => 4999, 'name' => __('seeds.statuses.guru'), 'color' => '#32608A'],
            ['topoint' => 5000, 'point' => 100000, 'name' => __('seeds.statuses.legend'), 'color' => '#ff0000'],
        ];

        Status::query()->truncate();
        Status::query()->insert($data);
    }
}
