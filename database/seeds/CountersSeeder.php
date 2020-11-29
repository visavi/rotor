<?php

use App\Models\Counter;
use Phinx\Seed\AbstractSeed;

class CountersSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
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
