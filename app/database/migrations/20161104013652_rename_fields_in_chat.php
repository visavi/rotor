<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInChat extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('chat');
        $table->renameColumn('chat_id', 'id');
        $table->renameColumn('chat_user', 'user');
        $table->renameColumn('chat_text', 'text');
        $table->renameColumn('chat_ip', 'ip');
        $table->renameColumn('chat_brow', 'brow');
        $table->renameColumn('chat_time', 'time');
        $table->renameColumn('chat_edit', 'edit');
        $table->renameColumn('chat_edit_time', 'edit_time');
    }
}
