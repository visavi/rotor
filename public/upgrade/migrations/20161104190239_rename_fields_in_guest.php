<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInGuest extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('guest');
        $table->renameColumn('guest_id', 'id');
        $table->renameColumn('guest_user', 'user');
        $table->renameColumn('guest_text', 'text');
        $table->renameColumn('guest_ip', 'ip');
        $table->renameColumn('guest_brow', 'brow');
        $table->renameColumn('guest_time', 'time');
        $table->renameColumn('guest_reply', 'reply');
        $table->renameColumn('guest_edit', 'edit');
        $table->renameColumn('guest_edit_time', 'edit_time');
    }
}
