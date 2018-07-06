<?php

use Phinx\Migration\AbstractMigration;

class RemoveHoursAndDaysFromCounters extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters');
        $table->removeColumn('hours')
            ->removeColumn('days')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters');
        $table->addColumn('hours', 'integer')
            ->addColumn('days', 'integer')
            ->save();
    }
}
