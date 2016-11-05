<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInAntimat extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('antimat');
        $table->renameColumn('mat_id', 'id');
        $table->renameColumn('mat_string', 'string');
    }
}
