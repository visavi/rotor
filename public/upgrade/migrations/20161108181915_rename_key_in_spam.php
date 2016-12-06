<?php

use Phinx\Migration\AbstractMigration;

class RenameKeyInSpam extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('spam');
        $table->renameColumn('key', 'relate');
    }
}
