<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInLotusers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $exists = $this->hasTable('lotusers');
        if ($exists) {
            $table = $this->table('lotusers');
            $table->renameColumn('lot_id', 'id');
            $table->renameColumn('lot_user', 'user');
            $table->renameColumn('lot_num', 'num');
            $table->renameColumn('lot_time', 'time');
        }
    }
}
