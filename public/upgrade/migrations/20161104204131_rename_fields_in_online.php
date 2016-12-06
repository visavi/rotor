<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInOnline extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('online');
        $table->renameColumn('online_id', 'id');
        $table->renameColumn('online_ip', 'ip');
        $table->renameColumn('online_brow', 'brow');
        $table->renameColumn('online_time', 'time');
        $table->renameColumn('online_user', 'user');
    }
}
