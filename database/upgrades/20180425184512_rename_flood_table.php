<?php

use Phinx\Migration\AbstractMigration;

class RenameFloodTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('flood');
        $table->rename('floods');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('floods');
        $table->rename('flood');
    }
}
