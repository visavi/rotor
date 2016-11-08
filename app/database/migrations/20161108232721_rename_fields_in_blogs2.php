<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBlogs2 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blogs');
        $table->renameColumn('cats_id', 'category_id');
        $table->renameColumn('read', 'visits');
    }
}
