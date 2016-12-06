<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCats extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('cats');
        $table->renameColumn('cats_id', 'id');
        $table->renameColumn('cats_order', 'order');
        $table->renameColumn('cats_parent', 'parent');
        $table->renameColumn('cats_name', 'name');
        $table->renameColumn('cats_count', 'count');
    }
}
