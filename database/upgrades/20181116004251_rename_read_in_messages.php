<?php

use Phinx\Migration\AbstractMigration;

class RenameReadInMessages extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('messages');
        $table->renameColumn('read', 'reading');
    }
}
