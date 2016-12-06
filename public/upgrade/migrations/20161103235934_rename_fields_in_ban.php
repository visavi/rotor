<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBan extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ban');
        $table->renameColumn('ban_id', 'id');
        $table->renameColumn('ban_ip', 'ip');
        $table->renameColumn('ban_user', 'user');
        $table->renameColumn('ban_time', 'time');
    }
}
