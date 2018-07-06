<?php

use Phinx\Migration\AbstractMigration;

class RemoveDaysFromCounters31 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters31');
        $table->removeColumn('days')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('TRUNCATE counters31');

        $table = $this->table('counters31');
        $table->addColumn('days', 'integer')
            ->addIndex('days', ['unique' => true])
            ->save();
    }
}
