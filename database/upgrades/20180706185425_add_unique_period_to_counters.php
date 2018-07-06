<?php

use Phinx\Migration\AbstractMigration;

class AddUniquePeriodToCounters extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters24');
        $table->addIndex('period', ['unique' => true])
            ->save();

        $table = $this->table('counters31');
        $table->addIndex('period', ['unique' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters24');
        $table->removeIndexByName('period')
            ->save();

        $table = $this->table('counters31');
        $table->removeIndexByName('period')
            ->save();
    }
}
