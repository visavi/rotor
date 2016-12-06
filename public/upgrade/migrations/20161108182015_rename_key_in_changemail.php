<?php

use Phinx\Migration\AbstractMigration;

class RenameKeyInChangemail extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('changemail');
        $table->renameColumn('key', 'hash');
    }
}
