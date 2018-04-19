<?php

use Phinx\Migration\AbstractMigration;

class RenameReadsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('reads');
        $table->rename('readers');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('readers');
        $table->rename('reads');
    }
}
