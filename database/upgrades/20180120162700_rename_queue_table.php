<?php

use Phinx\Migration\AbstractMigration;

class RenameQueueTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('queue');
        $table->rename('mailings');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('mailings');
        $table->rename('queue');
    }
}
