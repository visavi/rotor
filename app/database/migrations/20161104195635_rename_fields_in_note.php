<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInNote extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('note');
        $table->renameColumn('note_id', 'id');
        $table->renameColumn('note_user', 'user');
        $table->renameColumn('note_text', 'text');
        $table->renameColumn('note_edit', 'edit');
        $table->renameColumn('note_time', 'time');
    }
}
