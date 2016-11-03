<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInAdmlog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('admlog');
        $table->renameColumn('admlog_id', 'id');
        $table->renameColumn('admlog_user', 'user');
        $table->renameColumn('admlog_request', 'request');
        $table->renameColumn('admlog_referer', 'referer');
        $table->renameColumn('admlog_ip', 'ip');
        $table->renameColumn('admlog_brow', 'brow');
        $table->renameColumn('admlog_time', 'time');
    }
}
