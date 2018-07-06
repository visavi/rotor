<?php

use Phinx\Migration\AbstractMigration;

class RemoveHourFromCounters24 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters24');
        $table->removeColumn('hour')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE counters24');

        $table = $this->table('counters24');
        $table->addColumn('hour', 'integer')
            ->addIndex('hour', ['unique' => true])
            ->save();
    }
}
