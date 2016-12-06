<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInLogin extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('login');
        $table->renameColumn('login_id', 'id');
        $table->renameColumn('login_user', 'user');
        $table->renameColumn('login_ip', 'ip');
        $table->renameColumn('login_brow', 'brow');
        $table->renameColumn('login_time', 'time');
        $table->renameColumn('login_type', 'type');
    }
}
