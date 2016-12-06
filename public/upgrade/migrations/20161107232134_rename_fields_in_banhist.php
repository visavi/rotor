<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBanhist extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('banhist');
        $table->renameColumn('ban_id', 'id');
        $table->renameColumn('ban_user', 'user');
        $table->renameColumn('ban_send', 'send');
        $table->renameColumn('ban_type', 'type');
        $table->renameColumn('ban_reason', 'reason');
        $table->renameColumn('ban_term', 'term');
        $table->renameColumn('ban_time', 'time');
    }
}
