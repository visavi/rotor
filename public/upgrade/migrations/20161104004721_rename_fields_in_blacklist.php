<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBlacklist extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blacklist');
        if ($table->exists()) {
            $table->renameColumn('black_id', 'id');
            $table->renameColumn('black_type', 'type');
            $table->renameColumn('black_value', 'value');
            $table->renameColumn('black_user', 'user');
            $table->renameColumn('black_time', 'time');
        }
    }
}
