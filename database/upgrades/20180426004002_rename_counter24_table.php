<?php

use Phinx\Migration\AbstractMigration;

class RenameCounter24Table extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter24');
        $table->rename('counters24');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters24');
        $table->rename('counter24');
    }
}
