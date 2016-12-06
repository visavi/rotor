<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInOutbox extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('outbox');
        $table->renameColumn('outbox_id', 'id');
        $table->renameColumn('outbox_user', 'user');
        $table->renameColumn('outbox_author', 'author');
        $table->renameColumn('outbox_text', 'text');
        $table->renameColumn('outbox_time', 'time');
    }
}
