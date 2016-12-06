<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInError extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('error');
        $table->renameColumn('error_id', 'id');
        $table->renameColumn('error_num', 'num');
        $table->renameColumn('error_request', 'request');
        $table->renameColumn('error_referer', 'referer');
        $table->renameColumn('error_username', 'username');
        $table->renameColumn('error_ip', 'ip');
        $table->renameColumn('error_brow', 'brow');
        $table->renameColumn('error_time', 'time');
    }
}
