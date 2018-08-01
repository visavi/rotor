<?php

use Phinx\Migration\AbstractMigration;

class ChangePeriodInCounters extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counters');
        $table->changeColumn('period', 'datetime')
            ->save();

        $table = $this->table('counters24');
        $table->changeColumn('period', 'datetime')
            ->save();

        $table = $this->table('counters31');
        $table->changeColumn('period', 'datetime')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters');
        $table->changeColumn('period', 'datetime', ['null' => true])
            ->save();

        $table = $this->table('counters24');
        $table->changeColumn('period', 'datetime', ['null' => true])
            ->save();

        $table = $this->table('counters31');
        $table->changeColumn('period', 'datetime', ['null' => true])
            ->save();
    }
}
