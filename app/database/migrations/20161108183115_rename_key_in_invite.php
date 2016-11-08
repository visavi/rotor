<?php

use Phinx\Migration\AbstractMigration;

class RenameKeyInInvite extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('invite');
        $table->renameColumn('key', 'hash');
    }
}
