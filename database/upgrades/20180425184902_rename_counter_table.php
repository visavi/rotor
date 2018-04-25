<?php

use Phinx\Migration\AbstractMigration;

class RenameCounterTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter');
        $table->rename('counters');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters');
        $table->rename('counter');
    }
}
