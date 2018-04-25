<?php

use Phinx\Migration\AbstractMigration;

class RenameNotebookTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('notebook');
        $table->rename('notebooks');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('notebooks');
        $table->rename('notebook');
    }
}
