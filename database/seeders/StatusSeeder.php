<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['topoint' => 0, 'point' => 249, 'name' => __('seeds.statuses.novice'), 'color' => null],
            ['topoint' => 250, 'point' => 499, 'name' => __('seeds.statuses.local'), 'color' => null],
            ['topoint' => 500, 'point' => 999, 'name' => __('seeds.statuses.advanced'), 'color' => null],
            ['topoint' => 1000, 'point' => 1999, 'name' => __('seeds.statuses.experienced'), 'color' => null],
            ['topoint' => 2000, 'point' => 3999, 'name' => __('seeds.statuses.specialist'), 'color' => '#FF8800'],
            ['topoint' => 4000, 'point' => 5999, 'name' => __('seeds.statuses.expert'), 'color' => '#DC143C'],
            ['topoint' => 6000, 'point' => 7999, 'name' => __('seeds.statuses.master'), 'color' => '#0080FF'],
            ['topoint' => 8000, 'point' => 9999, 'name' => __('seeds.statuses.professional'), 'color' => '#000000'],
            ['topoint' => 10000, 'point' => 14999, 'name' => __('seeds.statuses.guru'), 'color' => '#32608A'],
            ['topoint' => 15000, 'point' => 100000, 'name' => __('seeds.statuses.legend'), 'color' => '#ff0000'],
        ];

        Status::query()->truncate();
        Status::query()->insert($data);
    }
}
