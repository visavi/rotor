<?php

use Phinx\Seed\AbstractSeed;

class CountersSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run()
    {
        $this->execute("REPLACE INTO `counters` (`id`, `period`, `allhosts`, `allhits`, `dayhosts`, `dayhits`, `hosts24`, `hits24`) VALUES (1, '" . date('Y-m-d H:00:00') . "', '0', '0', '0', '0', '0', '0');");
    }
}
