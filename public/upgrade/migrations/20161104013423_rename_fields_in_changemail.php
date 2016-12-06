<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInChangemail extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('changemail');
        $table->renameColumn('change_id', 'id');
        $table->renameColumn('change_user', 'user');
        $table->renameColumn('change_mail', 'mail');
        $table->renameColumn('change_key', 'key');
        $table->renameColumn('change_time', 'time');
    }
}
