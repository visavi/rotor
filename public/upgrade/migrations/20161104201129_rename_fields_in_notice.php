<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInNotice extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('notice');
        $table->renameColumn('notice_id', 'id');
        $table->renameColumn('notice_name', 'name');
        $table->renameColumn('notice_text', 'text');
        $table->renameColumn('notice_user', 'user');
        $table->renameColumn('notice_time', 'time');
        $table->renameColumn('notice_protect', 'protect');
    }
}
