<?php

use Phinx\Migration\AbstractMigration;

class RenameParentInForums extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('forums');
        $table->renameColumn('parent', 'parent_id');
    }
}
