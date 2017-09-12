<?php

use Phinx\Migration\AbstractMigration;

class RenameParentInCats extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('cats');
        $table->renameColumn('parent', 'parent_id');
    }
}
