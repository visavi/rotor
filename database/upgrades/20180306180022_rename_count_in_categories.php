<?php

use Phinx\Migration\AbstractMigration;

class RenameCountInCategories extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('categories');
        $table->renameColumn('count', 'count_blogs');
    }
}
