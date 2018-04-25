<?php

use Phinx\Seed\AbstractSeed;

class CountersSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run()
    {
        $this->execute("REPLACE INTO `counters` (`id`, `hours`, `days`, `allhosts`, `allhits`, `dayhosts`, `dayhits`, `hosts24`, `hits24`) VALUES (1, '0', '0', '0', '0', '0', '0', '0', '0');");
    }
}
