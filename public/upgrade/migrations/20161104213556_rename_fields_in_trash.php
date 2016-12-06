<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInTrash extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('trash');
        $table->renameColumn('trash_id', 'id');
        $table->renameColumn('trash_user', 'user');
        $table->renameColumn('trash_author', 'author');
        $table->renameColumn('trash_text', 'text');
        $table->renameColumn('trash_time', 'time');
        $table->renameColumn('trash_del', 'del');
    }
}
