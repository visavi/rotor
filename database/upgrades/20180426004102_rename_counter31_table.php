<?php

use Phinx\Migration\AbstractMigration;

class RenameCounter31Table extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('counter31');
        $table->rename('counters31');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('counters31');
        $table->rename('counter31');
    }
}
