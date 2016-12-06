<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInInbox extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('inbox');
        $table->renameColumn('inbox_id', 'id');
        $table->renameColumn('inbox_user', 'user');
        $table->renameColumn('inbox_author', 'author');
        $table->renameColumn('inbox_text', 'text');
        $table->renameColumn('inbox_time', 'time');
    }
}
