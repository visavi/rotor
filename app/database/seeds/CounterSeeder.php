<?php

use Phinx\Seed\AbstractSeed;

class CounterSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run()
    {
        $this->execute("REPLACE INTO `counter` (`id`, `hours`, `days`, `allhosts`, `allhits`, `dayhosts`, `dayhits`, `hosts24`, `hits24`) VALUES (1, '0', '0', '0', '0', '0', '0', '0', '0');");
    }
}
