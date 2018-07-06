<?php

use Phinx\Migration\AbstractMigration;

class AddPeriodToCounters extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function change()
    {
        $table = $this->table('counters');
        $table->addColumn('period', 'datetime', ['after' => 'id', 'null' => true])
            ->update();
    }
}
