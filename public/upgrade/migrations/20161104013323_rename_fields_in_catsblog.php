<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCatsblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('catsblog');
        $table->renameColumn('cats_id', 'id');
        $table->renameColumn('cats_order', 'order');
        $table->renameColumn('cats_name', 'name');
        $table->renameColumn('cats_count', 'count');
    }
}
