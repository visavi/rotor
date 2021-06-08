<?php

namespace Database\Seeders;

use App\Models\Counter;
use Illuminate\Database\Seeder;

class CounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Counter::query()->updateOrCreate([
            'id' => 1
        ], [
            'period'   => date('Y-m-d H:00:00'),
            'allhosts' => 0,
            'allhits'  => 0,
            'dayhosts' => 0,
            'dayhits'  => 0,
            'hosts24'  => 0,
            'hits24'   => 0,
        ]);
    }
}
