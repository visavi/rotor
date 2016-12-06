<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInIgnore extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ignore');
        $table->renameColumn('ignore_id', 'id');
        $table->renameColumn('ignore_user', 'user');
        $table->renameColumn('ignore_name', 'name');
        $table->renameColumn('ignore_text', 'text');
        $table->renameColumn('ignore_time', 'time');
    }
}
